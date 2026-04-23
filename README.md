# VMRA · 40th Anniversary Website

The Vintage Modified Racing Association (VMRA) website for the 2026 40th Anniversary
season. VMRA is a non-profit Pacific Northwest racing club founded by Vince Conwell
in 1986 at Spanaway Speedway.

- Live: https://vmra-preview-theta.vercel.app/
- Future: vmra.club (moving to Big Mountain Mail WordPress hosting later)

## Context

- The club's old site went dark when the former designer became unreachable in Dec 2025
- 40th Anniversary season is already racing — site needed to go live fast
- Static HTML + CSS + vanilla JS, no build step, deployed to Vercel

Built by Ryan Conwell (Vince's nephew) with Claude Code.

## Design direction

**"Pit Wall Meets Speedway"** — dark charcoal base, VMRA racing red as the power
color, sodium-amber accent evoking old speedway lights, vintage typography (Anton
+ Space Grotesk + JetBrains Mono + Archivo Black).

Includes SEO/AEO optimization: SportsOrganization / SportsEvent / FAQPage /
BreadcrumbList JSON-LD, full OpenGraph + Twitter card metadata, answer-first
About block below hero, visible FAQ mirroring the schema.

## Architecture

```
public/
├── index.html                    # Homepage (hero, race panel, standings teaser, etc.)
├── css/
│   ├── shell.css                 # Global chrome — nav, mobile menu, banner, footer.shell
│   └── home.css                  # Homepage-only modules (hero, cars video, news grid, ...)
├── js/
│   └── shell.js                  # Shared mobile-menu behavior (the only shared JS)
├── data/                         # JSON sources — edit these to update content
│   ├── schedule.json             # 11 races + tracks lookup (address, shape, website)
│   ├── standings.json            # 22-driver leaderboard
│   ├── news.json                 # news items (top one is featured)
│   └── results.json              # past race results
├── schedule/, standings/, racers/, rules/, classifieds/, contact/, tracks/
│                                 # Sub-pages — each uses shell.css + shell.js + its own local CSS
├── fonts/                        # self-hosted woff2
├── media/                        # images, videos, banner artwork
└── downloads/                    # PDFs for membership + rulebook + construction rules

scripts/
└── apply-race-update.py          # Parses a race-update email, patches JSON files
```

### The shell-CSS / shell-JS rule

**Do not duplicate global CSS or JS inside page files.** Global styles and shared
scripts live in `/css/shell.css` and `/js/shell.js` only. A page may include small
page-specific CSS inline if and only if it's truly unique to that page.

- Global CSS (only in `shell.css`): `nav.main`, `.nav-inner`, `.logo-lockup`,
  `.logo-mark`, `.logo-text`, `.nav-links`, `.nav-right`, `.nav-cta`, `.nav-toggle`,
  `.mobile-menu`, `.mobile-menu-inner`, `.mm-link`, `.mm-secondary`, `.mm-divider`,
  `@keyframes mmFade`, `.anniversary-banner`, `.ab-link`, `footer.shell` and
  children, responsive rules for all of those.
- Global JS (only in `shell.js`): the mobile-menu toggle IIFE — `navToggle` /
  `mobile-menu` click + Esc key + click-on-link-closes behavior + body-scroll lock.
- Homepage modules (only in `home.css`): `.hero`, `.race-panel`, `.countdown`,
  `.ticker`, `.upcoming-strip`, `.standings`, `.cars-block`, `.news-grid`, etc.

Standard markup order on every page:

```
<body>
  <section class="anniversary-banner">...</section>
  <nav class="main">...</nav>
  <div class="mobile-menu" id="mobile-menu">...</div>
  <!-- page hero + content -->
  <footer class="shell">...</footer>
</body>
```

### Data contract

Every claim on the site (track name, date, round number, "9 points + 2 specials"
etc.) must reconcile against `public/data/schedule.json`. If the site mentions a
track or date that isn't in the JSON, either the JSON is wrong or the claim is —
don't ship the mismatch.

## Local dev

```bash
# serve the public folder on port 5180
python3 -m http.server 5180 --directory public

# validate JSON before committing
python3 -c "import json; json.load(open('public/data/schedule.json'))"
```

## Deploy

```bash
# from the project root
vercel --prod --yes
```

Vercel CLI is already linked to the `vmra-preview` project (id
`prj_xkOAHUpiD0OuCSy72hHhyHAKR9xK`). Deploys auto-alias to
`vmra-preview-theta.vercel.app`.

### Git + Vercel workflow

```bash
# after editing files or applying a race update
git add -A
git commit -m "describe what changed"
git push
vercel --prod --yes
```

## Applying a weekly race update

Board emails a templated update after each race (see `RACE-UPDATE-TEMPLATE.md`).

```bash
# 1. paste the email into scripts/apply-race-update.py
# 2. run it — it patches standings.json / results.json / news.json
python3 scripts/apply-race-update.py

# 3. verify JSON
python3 -c "import json; [json.load(open(f'public/data/{f}.json')) for f in ['schedule','standings','news','results']]; print('all valid')"

# 4. commit + deploy
git add -A && git commit -m "Race update: Round N results" && git push
vercel --prod --yes
```

Full playbook lives in `WEEKLY-UPDATE-PLAYBOOK.md`.

## Key facts about the 2026 season

- 11-date 40th Anniversary tour: 9 points rounds + 2 non-points specials
- Tracks: Tri-City Raceway (West Richland WA), Evergreen Speedway (Monroe WA —
  "The Half-Mile"), Wenatchee Valley Super Oval (East Wenatchee WA), Stateline
  Speedway (Post Falls ID), South Sound Speedway (Rochester WA)
- Tire spec: Hoosier ST1 / ST2 / ST3 take-offs only, procured by VMRA
- Membership: $50 / yr · Protest fee: $150 cash · Shock claim: $150

## Files worth knowing

| File | Purpose |
|---|---|
| `RACE-UPDATE-TEMPLATE.md` | Email template the board fills out after each race |
| `ROSTER-NEEDS.md` | Which drivers still need real bios/photos on the racers page |
| `WEEKLY-UPDATE-PLAYBOOK.md` | Step-by-step for applying a race update |
| `scripts/apply-race-update.py` | 240-line parser that patches JSON from the email |
| `vercel.json` | Cache headers + cleanUrls |
