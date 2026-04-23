# Weekly Race Update Playbook

**Audience:** Ryan (the only person who runs this script)
**Goal:** Take a race-update email from the team and ship it to the live site in under 5 minutes.

---

## The whole flow at a glance

1. Team emails `board@vmra.club` using `RACE-UPDATE-TEMPLATE.md`
2. You copy the email body, save to a file (or paste into stdin)
3. Run the parser script in dry-run mode → review the diff
4. Re-run for real → JSON files get updated
5. `vercel deploy --prod --yes` → live in ~30 seconds
6. Done

---

## Step-by-step

### Step 1 — Save the team's email body

When the team sends you the templated update, copy the body of the email and save it to a file. Easiest way:

```bash
cd ~/Downloads/vmra-work/vmra-site
nano /tmp/update.txt
# paste the email body, then Ctrl+O to save, Ctrl+X to exit
```

Or just pipe it in via your clipboard:

```bash
pbpaste > /tmp/update.txt
```

### Step 2 — Dry-run to review the changes

```bash
python3 scripts/apply-race-update.py --dry-run --file /tmp/update.txt
```

You'll see something like:

```
PARSING
  Race:    2026-04-19 · Evergreen Speedway · Round 1
  Winner:  #30 Kyten Jones
  Fast:    #22 Steve Woods · 16.17
  Podium:  3 entries
  Points:  5 drivers updated

STANDINGS CHANGES (DRY RUN)
  #30 Kyten Jones: 539 → 574
  #22 Steve Woods: 534 → 559
  #57 Shane Strimple: 482 → 502
  #65 Randy Adams: 470 → 448

RESULTS APPEND (DRY RUN)
  Added entry for 2026-04-19 Round 1 at Evergreen Speedway

NEWS ITEM (DRY RUN)
  Headline: Kyten Jones Takes Round 1 at Evergreen Speedway
  Snippet:  Took it on a broken upper control arm — last 5 laps were one-handed…
```

**Read the diff.** Make sure the points changes look right and the headline reads OK.

### Step 3 — Apply for real

```bash
python3 scripts/apply-race-update.py --file /tmp/update.txt
```

This writes to:
- `public/data/standings.json` (driver points + position re-sort)
- `public/data/results.json` (appends this race's results)
- `public/data/news.json` (prepends a new race-recap card to the homepage)

### Step 4 — Deploy

```bash
vercel deploy --prod --yes
```

About 30 seconds later, the site is live with:
- Updated standings table at `/standings/`
- New race recap card on the homepage news section
- Ticker still works (it pulls schedule, not results)

### Step 5 — Spot-check

Open https://vmra-preview-theta.vercel.app/standings/ — confirm the new totals.
Open https://vmra-preview-theta.vercel.app/ — scroll to "From the Paddock", confirm new card.

---

## Common things to fix manually

The script does ~95% of what you need, but here are cases where you'll want to edit JSON files directly:

### Edit a news headline the script auto-generated

The auto-generated headline ("X Takes Round Y at Z") is generic. If the team gave you a great storyline, replace it:

```bash
nano public/data/news.json
# find the top item (date matches the race), edit "headline" and "snippet"
```

### Add a non-race news item (announcement, sponsor news, rule update)

The script only handles race recaps. For announcements, edit `news.json` manually:

```json
{
  "date": "2026-04-22",
  "category": "Announcement",
  "headline": "Pomp's Tire Title Renewal — 2027 Already Locked In",
  "snippet": "Three more years secured...",
  "byline": "Rick Villyard",
  "link": "/contact/"
}
```

Add it at the TOP of the `items` array. Keep the array under ~8 items (older ones can stay archived but only the top 5 show on the homepage).

### Fix a wrong points entry

Just edit `public/data/standings.json` directly. The drivers array is sorted by points after every script run, but you can edit-and-resort:

```bash
nano public/data/standings.json
# fix the points value
python3 -c "import json; d=json.load(open('public/data/standings.json')); real=[x for x in d['drivers'] if isinstance(x.get('position'),int)]; real.sort(key=lambda x: x['points'], reverse=True); [r.update({'position':i+1}) for i,r in enumerate(real)]; d['drivers']=real+[x for x in d['drivers'] if not isinstance(x.get('position'),int)]; json.dump(d, open('public/data/standings.json','w'), indent=2)"
```

### Change the schedule

`public/data/schedule.json` — edit dates/tracks/labels directly. Both the `/schedule/` page and the homepage ticker pick up changes on next deploy.

---

## What if the team's email doesn't follow the template?

Two options:

1. **Reply asking them to use the template** — over time they'll get used to it.
2. **Just edit the JSON files manually** for that update — takes a few minutes.

The script accepts partial templates — winner alone is enough to generate a news card and append a result. Points-only updates work too (no winner, no news card, just standings).

---

## Things the script will NOT touch

- `public/data/schedule.json` — only edit by hand when you publish next year's schedule
- Any HTML files — those just fetch the JSON
- The racers grid — separate dataset that you edit manually when adding photos

---

## Troubleshooting

**"Could not find a RACE: line"**
The team didn't use the right format. Re-send them `RACE-UPDATE-TEMPLATE.md`.

**Points went to 0 for a driver**
The script saw a non-numeric value and skipped. Check the team's email — they probably wrote "DNF" instead of a number. Just hand-edit `standings.json`.

**The site shows "Loading…" forever**
Likely a JSON syntax error. Test:
```bash
python3 -c "import json; json.load(open('public/data/standings.json'))"
```
If it throws an error, fix the JSON syntax (missing comma, etc.) and redeploy.

**Site cache stuck**
Vercel sets long cache on `/data/*.json`. If you redeploy and don't see changes, hard-refresh (Cmd-Shift-R) once. Real users will get the new files within 5 minutes when the CDN edge expires.

Actually, looking at the cache headers — `/data/*.json` is NOT in the immutable rule yet. Let me confirm:
```bash
curl -sI https://vmra-preview-theta.vercel.app/data/standings.json | grep cache-control
```

If you see `max-age=0, must-revalidate` — good, no caching issue.
If you see `max-age=31536000` — the JSON is being cached for a year and won't update. Add an exclusion to `vercel.json`.

---

## Future upgrades (when you're ready)

- **Email-triggered**: scheduled agent watches `board@vmra.club` and runs the script automatically
- **Google Sheets sync**: team maintains a sheet, nightly job pulls CSV and rebuilds JSON
- **Photo OCR**: snap a phone photo of the standings sheet at the track, AI parses it, pipes into the script
- **Push notifications**: when standings update, send Telegram/Discord ping to subscribers

For now: this is the simplest reliable workflow. Ship updates fast, iterate later.
