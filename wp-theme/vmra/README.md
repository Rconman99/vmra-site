# VMRA 40th Anniversary · WordPress Theme

Custom WordPress theme that reproduces the VMRA static site
(vmra-preview-theta.vercel.app) inside WordPress so the board can post
race updates from the WP admin instead of editing JSON files.

**Status:** Phase 1 — theme scaffold with a working homepage. Other pages
(schedule, standings, racers, rules, classifieds, contact, tracks) are
deferred to Phase 2.

---

## What's in this folder

```
vmra/
├── style.css              WP theme header (required)
├── functions.php          Theme setup, enqueues CSS/JS, registers menus
├── header.php             Anniversary banner + nav + mobile menu
├── footer.php             4-column editorial footer
├── front-page.php         Homepage (hero, standings card, news grid)
├── index.php              Fallback for anything else
├── inc/
│   ├── enqueue.php        Loads shell.css, home.css, shell.js
│   ├── cpt-driver.php     Registers Drivers custom post type
│   ├── cpt-race.php       Registers Races custom post type
│   ├── cpt-track.php      Registers Tracks custom post type
│   ├── cpt-news.php       Registers News custom post type
│   └── admin-notices.php  Warns the admin if ACF isn't installed
├── assets/
│   ├── css/               shell.css, home.css
│   ├── js/                shell.js
│   ├── fonts/             Self-hosted woff2 (Anton, Space Grotesk, JetBrains Mono, Archivo Black)
│   ├── media/             Anniversary banner in 4 sizes / 3 formats + favicons
│   └── downloads/         Membership form, house rules, construction rules PDFs
├── data/                  Seed JSON from the static site (used as fallback
│                          until content is migrated into WP posts)
└── README.md              This file
```

---

## Installing the theme

### Step 1 — zip the folder

On your Mac (Terminal):

```bash
cd ~/Downloads/vmra-work/vmra-site/wp-theme
zip -r vmra.zip vmra/
```

### Step 2 — upload to WP

1. Log in to `https://temp.nwvintagemodified.com/wp-admin`.
2. Go to **Appearance → Themes → Add New Theme → Upload Theme**.
3. Choose `vmra.zip` and click **Install Now**.
4. Click **Activate**.

The site should now look like the Vercel preview (banner, nav, hero, footer).

### Step 3 — install Advanced Custom Fields (free)

The theme works without ACF, but you can't edit car numbers, race
rounds, or winners from the WP admin without it.

1. **Plugins → Add New**.
2. Search for **Advanced Custom Fields** (the free one by WP Engine).
3. Install and activate.

When ACF is active, the admin warning banner disappears and the
Drivers / Races / Tracks / News entries get their custom fields.

### Step 4 — set the homepage

By default, WP shows your latest posts on the front page. We want the
custom homepage template:

1. **Settings → Reading**.
2. "Your homepage displays" → **A static page**.
3. Homepage → pick (or create) a page called "Home".
4. Save.

---

## Content: how the pieces relate

The site revolves around four custom post types:

| Type    | What lives here                                    | Admin label |
|---------|----------------------------------------------------|-------------|
| Driver  | Permanent info per driver (name, car #, hometown, photos). Does NOT store points. | Drivers  |
| Race    | One post per round (date, track, event name, points scored by each driver, winner). | Races    |
| Track   | One post per oval (city/state, shape, length, website).                             | Tracks   |
| News    | Race recaps, previews, sponsor news, rule updates.                                  | News     |

**Standings are computed** by summing a driver's points across every
Race where they appear — so historical points are never lost, and
correcting a past race automatically updates the current standings.

---

## Posting a race update (the weekly flow)

After each race, the board sends a templated email with qualifying,
heat, main event, and show-up points. The plan is:

1. **Phase 3 — coming later.** A custom WP admin page will paste the
   email text, parse it, and auto-create the Race post + update
   driver points + create a News item.
2. **Today (manual).** Add a new Race post, attach the Track, set the
   winner, and fill in the results table. Then create a News article
   with the recap and a link back to the Race.

Seed data for the 2026 season (R01 already entered) ships in
`wp-theme/vmra/data/*.json` so the homepage renders something sensible
before you've added any posts.

---

## What's NOT in Phase 1

The following will come in a later session:

- [ ] `page-schedule.php` — full schedule template
- [ ] `page-standings.php` — full standings table + rookies section
- [ ] `page-racers.php` — full driver roster with filters
- [ ] `page-rules.php` — rules page
- [ ] `page-classifieds.php` — classifieds with driver filter
- [ ] `page-contact.php` — contact cards
- [ ] `page-tracks.php` — tracks grid with profiles
- [ ] `single-race.php` — race recap template
- [ ] `single-driver.php` — driver profile template
- [ ] `archive-news.php` — news index template
- [ ] ACF field groups exported as JSON (importable via ACF → Tools → Import)
- [ ] Race-update admin page (the automated email parser)
- [ ] Data migration script (JSON → WP posts)

---

## Design rules (don't break these)

The theme inherits the canonical rules from the static site.
If you edit CSS, keep these in mind:

1. **Global styles live in `shell.css` only.** Nav, mobile menu,
   banner, footer, grain + scanline overlays. Don't duplicate these
   inside page templates.
2. **Homepage-only modules live in `home.css` only.** Hero panels,
   news grid, standings card, etc.
3. **Shell JS lives in `shell.js` only.** The mobile-menu toggle.
   Don't add inline IIFEs to template files.
4. **No emojis in prose.** The editorial voice doesn't use them.
5. **Data reconciles to the JSON (or, later, CPT).** If the homepage
   mentions a track, date, or car number, it must exist in the data.

---

## Questions / issues

- GitHub: <https://github.com/Rconman99/vmra-site>
- Static preview (for visual reference): <https://vmra-preview-theta.vercel.app>
- Board email: <board@vmra.club>
