# GitHub Actions auto-deploy — one-time setup

After this is wired up, every push to `main` that touches `wp-theme/vmra/**`
deploys to `temp.nwvintagemodified.com` automatically. No DevTools paste, no
manual click. CLI Claude (or any agent that can `git push`) ships features
end-to-end.

The workflow lives at `.github/workflows/deploy.yml` and uses rsync over SSH.
Setup takes ~10 minutes, all on Ryan's side.

---

## Step 1 — Generate the deploy SSH key

On your Mac:

```bash
ssh-keygen -t ed25519 \
  -C "github-actions-vmra-deploy" \
  -f ~/.ssh/vmra_gh_deploy \
  -N ""
```

Empty passphrase (`-N ""`) — GitHub Actions can't enter a passphrase. The
private key is sensitive but will live only on your Mac and as a GitHub Secret.

You now have two files:
- `~/.ssh/vmra_gh_deploy`     — the **PRIVATE** key (do NOT share)
- `~/.ssh/vmra_gh_deploy.pub` — the **PUBLIC** key (paste into DA)

---

## Step 2 — Confirm SSH access is enabled in DirectAdmin

Big Mountain shared hosting may default SSH to off. Check:

1. Log in to https://web3.bigmountainmail.com:2222
2. **User Panel → Advanced Features → SSH Keys**
   - If you see "SSH access is disabled for this account", contact Big Mountain
     support and ask them to enable shell access (specifically: SFTP / rsync
     over SSH, not interactive shell — they may have a specific tier for this).
   - If the page exists and is editable, you're good.

If SSH is permanently off on your tier, ping me and I'll wire up an
HTTP-API-based fallback using DA Login Keys.

---

## Step 3 — Add the public key to DirectAdmin

In **User Panel → SSH Keys → Add SSH Key**:

1. Name: `github-actions-vmra-deploy`
2. Key: paste the entire contents of `~/.ssh/vmra_gh_deploy.pub` (one line,
   starts with `ssh-ed25519`)
3. Save.

---

## Step 4 — Verify the key works from your Mac

Find your SSH host + port. In DA's User Panel home, look for "SSH Information"
or similar. Big Mountain typically uses:

- Host: `web3.bigmountainmail.com` (or its IP `161.129.90.109`)
- Port: `22` (for SSH/SFTP — not the panel's `2222`)

Test:

```bash
ssh -i ~/.ssh/vmra_gh_deploy -p 22 YOUR_DA_USER@web3.bigmountainmail.com
```

Replace `YOUR_DA_USER` with your DirectAdmin username. First connection will
ask "yes" to the host fingerprint; type `yes`. If you land at a shell prompt
or get a "shell disabled but SFTP allowed" message, you're good. Type `exit`
or Ctrl+D to leave.

If you get "Permission denied (publickey)", the key isn't installed correctly
in DA — re-paste in step 3.

---

## Step 5 — Add 4 secrets to the GitHub repo

Go to **https://github.com/Rconman99/vmra-site/settings/secrets/actions →
New repository secret** and add these one at a time:

| Name                    | Value                                            |
| ----------------------- | ------------------------------------------------ |
| `DA_SSH_HOST`           | `web3.bigmountainmail.com` (or IP)               |
| `DA_SSH_PORT`           | `22` (whatever you confirmed in step 4)          |
| `DA_SSH_USER`           | your DirectAdmin username                        |
| `DA_SSH_PRIVATE_KEY`    | full contents of `~/.ssh/vmra_gh_deploy` (the    |
|                         | PRIVATE one — `cat ~/.ssh/vmra_gh_deploy`)       |

For `DA_SSH_PRIVATE_KEY`, paste everything from `-----BEGIN OPENSSH PRIVATE KEY-----`
through `-----END OPENSSH PRIVATE KEY-----` inclusive, including the final
newline.

---

## Step 6 — First deploy

Two ways to trigger the workflow:

**Option A — push any theme change.** Edit any file under `wp-theme/vmra/`,
commit, push to main. The workflow auto-fires.

**Option B — manual run from the GitHub UI.** Go to
**https://github.com/Rconman99/vmra-site/actions/workflows/deploy.yml →
Run workflow** (top right). Pick `main`. Optionally type a reason. Click
the green button.

Watch the run logs. Expected output:

```
> Theme version on this commit: 1.5.0
> sending incremental file list
> wp-theme/vmra/functions.php
> wp-theme/vmra/style.css
> ... (or just "no changes" if everything's already in sync)
> Live version reports: 1.5.0
> Expected: 1.5.0
```

If you see the "Live version does not match" warning, the rsync uploaded
correctly but a CDN/page cache may be serving stale content. Hard-reload the
homepage; it'll catch up within a minute.

---

## Troubleshooting

**"Permission denied (publickey)"** in the rsync step:
- Key name in DA was changed/deleted, OR `DA_SSH_USER` secret is wrong, OR
  `DA_SSH_PRIVATE_KEY` got truncated when pasted (must include the BEGIN/END
  lines and final newline).

**"Connection timed out"** or **"could not resolve hostname"**:
- `DA_SSH_HOST` or `DA_SSH_PORT` wrong. Test from your Mac first using the
  same values.

**rsync runs but live site doesn't update**:
- Wrong `remote_path`. Default is
  `/domains/temp.nwvintagemodified.com/public_html/wp-content/themes/vmra/`.
  If your DA layout differs (e.g., `home/USER/domains/...`), edit the workflow
  YAML.

**"shell access disabled but SFTP works"**:
- That's fine. rsync over SFTP works without an interactive shell on most
  DA configs. If rsync fails complaining about no shell, edit the workflow
  switches to add `--rsh="ssh -p $port"` or pivot to `lftp` over SFTP — ping me.
