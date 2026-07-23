#!/usr/bin/env bash
# deploy-theme.sh — push WP theme files to temp.nwvintagemodified.com via the
# DirectAdmin API. Works today because SSH (port 22) is disabled on this
# hosting tier, so the GitHub Action's rsync step can't run.
#
# Credential lives in the macOS keychain — never in the repo, never in argv:
#   security add-generic-password -a wordpress \
#     -s "web3.bigmountainmail.com:2222" -w 'YOUR_KEY'
#
# Usage:
#   ./scripts/deploy-theme.sh                  # deploy all data/*.json
#   ./scripts/deploy-theme.sh standings.json   # deploy one file
#   ./scripts/deploy-theme.sh --all            # entire theme dir (slow)
#
# When Jon enables SSH, prefer the GitHub Action instead of this.

set -euo pipefail

DA_HOST="web3.bigmountainmail.com:2222"
DA_USER="nwvintage"
REMOTE_THEME="/domains/temp.nwvintagemodified.com/public_html/wp-content/themes/vmra"
LOCAL_THEME="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)/wp-theme/vmra"
LIVE_BASE="https://temp.nwvintagemodified.com/wp-content/themes/vmra"

KEY="$(security find-generic-password -a wordpress -s "$DA_HOST" -w 2>/dev/null || true)"
if [[ -z "$KEY" ]]; then
  echo "ERROR: no keychain entry for $DA_HOST (account: wordpress)" >&2
  exit 1
fi

upload() {
  local local_file="$1" remote_dir="$2" name
  name="$(basename "$local_file")"

  if [[ ! -f "$local_file" ]]; then
    echo "  SKIP  $name (not found)"
    return
  fi

  # Validate JSON before it goes anywhere near production
  if [[ "$name" == *.json ]]; then
    if ! python3 -m json.tool "$local_file" >/dev/null 2>&1; then
      echo "  FAIL  $name — invalid JSON, not uploading" >&2
      return 1
    fi
  fi

  local resp
  resp="$(curl -s --max-time 60 -k -u "$DA_USER:$KEY" \
    -F "action=upload" \
    -F "path=$remote_dir" \
    -F "file1=@$local_file;filename=$name" \
    "https://$DA_HOST/CMD_API_FILE_MANAGER")"

  if [[ "$resp" == *"error=0"* ]]; then
    echo "  OK    $name"
  else
    echo "  FAIL  $name → $resp" >&2
    return 1
  fi
}

echo "Deploying to $REMOTE_THEME"
echo ""

if [[ "${1:-}" == "--all" ]]; then
  find "$LOCAL_THEME" -type f \
    ! -name '.DS_Store' ! -name '._*' ! -name '*.zip' \
    | while read -r f; do
        rel="${f#$LOCAL_THEME/}"
        sub="$(dirname "$rel")"
        [[ "$sub" == "." ]] && rdir="$REMOTE_THEME" || rdir="$REMOTE_THEME/$sub"
        upload "$f" "$rdir"
      done
elif [[ $# -gt 0 ]]; then
  for name in "$@"; do
    upload "$LOCAL_THEME/data/$name" "$REMOTE_THEME/data"
  done
else
  for f in "$LOCAL_THEME"/data/*.json; do
    upload "$f" "$REMOTE_THEME/data"
  done
fi

echo ""
echo "Verifying live:"
for f in "$LOCAL_THEME"/data/*.json; do
  name="$(basename "$f")"
  live="$(curl -s --max-time 20 "$LIVE_BASE/data/$name?cb=$(date +%s)" 2>/dev/null || true)"
  if [[ -n "$live" ]] && printf '%s' "$live" | python3 -c "import json,sys; json.load(sys.stdin)" 2>/dev/null; then
    upd="$(printf '%s' "$live" | python3 -c "import json,sys; print(json.load(sys.stdin).get('updated','?'))" 2>/dev/null)"
    echo "  $name — live, updated $upd"
  else
    echo "  $name — could not verify" >&2
  fi
done
