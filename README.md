# VMRA · 40th Anniversary Website (Preview)

Preview build of the proposed VMRA (Vintage Modified Racing Association) website, built for board review during April 2026.

## Context

- The club's existing site is inaccessible (former designer unreachable since Dec 2025)
- Big Mountain Mail (Jon Clayton) has a fresh WordPress install ready on the club's existing hosting
- The 40th Anniversary season is already racing — site needs to go live fast
- This preview demonstrates the proposed design direction before final build on WordPress

## Design direction

**"Pit Wall Meets Speedway"** — dark charcoal base, VMRA racing red as the power color, sodium-amber accent evoking old speedway lights, vintage typography (Anton + Space Grotesk + JetBrains Mono).

Includes SEO/AEO optimization:
- SportsOrganization, SportsEvent, FAQPage, and BreadcrumbList JSON-LD
- Full OpenGraph + Twitter card metadata
- Answer-first About block below hero
- Visible FAQ section mirroring the schema

## Deploy

Static HTML, no build step. Single `public/index.html`.

Built by Ryan Conwell (Vince's nephew) with Claude Code.
