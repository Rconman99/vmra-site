#!/usr/bin/env python3
"""
apply-race-update.py — Parse a templated race-update email and update the
JSON data files that drive the VMRA site.

USAGE:
  # Paste template into stdin:
  python3 scripts/apply-race-update.py
  # ...paste the template, then Ctrl+D when done

  # Or read from a file:
  python3 scripts/apply-race-update.py < update.txt
  python3 scripts/apply-race-update.py --file update.txt

  # Preview without writing:
  python3 scripts/apply-race-update.py --dry-run < update.txt

WHAT IT UPDATES:
  - public/data/standings.json     ← driver points
  - public/data/results.json       ← appends this race's results
  - public/data/news.json          ← prepends a new race-recap card

Run from the repo root (the directory above 'public/' and 'scripts/').
"""
import sys, os, json, re, argparse
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
DATA = ROOT / "public" / "data"
STANDINGS = DATA / "standings.json"
RESULTS = DATA / "results.json"
NEWS = DATA / "news.json"


def parse_template(text):
    """Parse a race-update template into a dict. Tolerant of missing sections."""
    out = {
        "date": None, "track": None, "round": None,
        "winner": None, "winner_note": None,
        "fast_time": None,
        "podium": [],
        "points": [],
        "attendance": None, "conditions": None, "notes": None,
    }

    # RACE line: "RACE: YYYY-MM-DD · track · Round N"
    m = re.search(r"^RACE:\s*(\d{4}-\d{2}-\d{2})\s*[·•|-]\s*([^·•|]+?)\s*[·•|-]\s*Round\s*(\d+)",
                  text, re.MULTILINE | re.IGNORECASE)
    if m:
        out["date"] = m.group(1).strip()
        out["track"] = m.group(2).strip()
        out["round"] = int(m.group(3))
    else:
        raise ValueError("Could not find a RACE: line. Expected format:\n"
                         "  RACE: 2026-04-19 · Evergreen Speedway · Round 1")

    # WINNER block
    win_match = re.search(
        r"^WINNER:\s*\n\s*#(\S+)\s+([^\n]+?)(?:\n\s+(?:Optional one-liner:\s*)?([^\n]+))?\s*\n",
        text, re.MULTILINE | re.IGNORECASE)
    if win_match:
        out["winner"] = {"car": win_match.group(1), "name": win_match.group(2).strip()}
        if win_match.group(3) and not win_match.group(3).strip().startswith("[") \
           and not win_match.group(3).strip().upper().startswith(("FAST", "PODIUM", "POINTS", "ATTEND", "COND", "NOTES")):
            out["winner_note"] = win_match.group(3).strip()

    # FAST TIME: #X Driver · time
    ft = re.search(r"^FAST TIME:\s*\n\s*#(\S+)\s+([^·•|\n]+?)\s*[·•|]\s*([^\n]+)",
                   text, re.MULTILINE | re.IGNORECASE)
    if ft:
        out["fast_time"] = {"car": ft.group(1), "name": ft.group(2).strip(), "time": ft.group(3).strip()}

    # PODIUM: 3 numbered lines
    pod_block = re.search(r"^PODIUM:\s*\n((?:\s*\d+\.\s+#\S+\s+[^\n]+\n?){1,5})",
                          text, re.MULTILINE | re.IGNORECASE)
    if pod_block:
        for ln in pod_block.group(1).strip().split("\n"):
            mp = re.match(r"\s*(\d+)\.\s+#(\S+)\s+(.+)", ln)
            if mp:
                out["podium"].append({
                    "position": int(mp.group(1)),
                    "car": mp.group(2),
                    "name": mp.group(3).strip()
                })

    # POINTS UPDATE: lines like "#30 Kyten Jones    574  (was 539)"
    pts_block = re.search(
        r"^POINTS UPDATE[^\n]*\n((?:\s*#\S+\s+[^\n]+\n?)+)",
        text, re.MULTILINE | re.IGNORECASE)
    if pts_block:
        for ln in pts_block.group(1).strip().split("\n"):
            mp = re.match(r"\s*#(\S+)\s+(.+?)\s+(\d+|\[new\]|no change|\d+)\s*(?:\(([^)]+)\))?", ln, re.IGNORECASE)
            if mp:
                car, name, new_pts, was = mp.group(1), mp.group(2).strip(), mp.group(3), mp.group(4)
                if new_pts.lower().startswith(("no change", "[")) and was:
                    # try to extract from "(was 488)" — keep the OLD value as the new (no change)
                    wm = re.search(r"was\s+(\d+)", was, re.IGNORECASE)
                    if wm:
                        new_pts = wm.group(1)
                if str(new_pts).isdigit():
                    out["points"].append({
                        "car": car,
                        "name": name,
                        "points": int(new_pts),
                        "was": was.strip() if was else None
                    })

    # Optional simple fields
    for field in ["ATTENDANCE", "CONDITIONS", "NOTES"]:
        m = re.search(rf"^{field}:\s*([^\n]+(?:\n(?!\s*[A-Z]+:)[^\n]+)*)",
                      text, re.MULTILINE | re.IGNORECASE)
        if m:
            val = m.group(1).strip()
            if not val.startswith("["):  # skip placeholder bracket prompts
                out[field.lower()] = val

    return out


def update_standings(parsed, dry_run=False):
    """Apply the points deltas to standings.json. Resorts by points descending."""
    if not parsed["points"]:
        return [], []

    with open(STANDINGS) as f:
        data = json.load(f)

    by_car = {str(d["car"]): d for d in data["drivers"] if isinstance(d.get("position"), int)}
    changed, added = [], []

    for upd in parsed["points"]:
        car = str(upd["car"])
        if car in by_car:
            old = by_car[car]["points"]
            if old != upd["points"]:
                by_car[car]["points"] = upd["points"]
                changed.append(f'  #{car} {by_car[car]["name"]}: {old} → {upd["points"]}')
            # update name if it changed
            if upd["name"] != by_car[car]["name"]:
                by_car[car]["name"] = upd["name"]
        else:
            new_driver = {"car": car, "name": upd["name"], "points": upd["points"], "position": 0}
            data["drivers"].append(new_driver)
            by_car[car] = new_driver
            added.append(f'  + #{car} {upd["name"]}: {upd["points"]} pts')

    # Re-sort numeric-position drivers by points desc; keep "16-22" filler last
    fillers = [d for d in data["drivers"] if not isinstance(d.get("position"), int)]
    real = [d for d in data["drivers"] if isinstance(d.get("position"), int)]
    real.sort(key=lambda d: d["points"], reverse=True)
    for i, d in enumerate(real, start=1):
        d["position"] = i
    data["drivers"] = real + fillers

    data["updated"] = parsed["date"]
    if parsed["round"] and parsed["round"] > data.get("rounds_completed", 0):
        data["rounds_completed"] = parsed["round"]

    if not dry_run:
        with open(STANDINGS, "w") as f:
            json.dump(data, f, indent=2)
            f.write("\n")

    return changed, added


def append_results(parsed, dry_run=False):
    """Append this race to results.json."""
    with open(RESULTS) as f:
        data = json.load(f)

    entry = {
        "date": parsed["date"],
        "round": parsed["round"],
        "track": parsed["track"],
    }
    if parsed["winner"]:
        entry["winner"] = parsed["winner"]
        if parsed["winner_note"]:
            entry["winner_note"] = parsed["winner_note"]
    if parsed["fast_time"]:
        entry["fast_time"] = parsed["fast_time"]
    if parsed["podium"]:
        entry["podium"] = parsed["podium"]
    for k in ("attendance", "conditions", "notes"):
        if parsed.get(k):
            entry[k] = parsed[k]

    # Replace if same date+round already exists
    existing = [r for r in data["races"] if not (r.get("date") == entry["date"] and r.get("round") == entry["round"])]
    existing.append(entry)
    existing.sort(key=lambda r: r.get("date", ""), reverse=True)
    data["races"] = existing
    data["updated"] = parsed["date"]

    if not dry_run:
        with open(RESULTS, "w") as f:
            json.dump(data, f, indent=2)
            f.write("\n")

    return entry


def prepend_news(parsed, dry_run=False):
    """Prepend a new race-recap news card to news.json."""
    if not parsed["winner"]:
        return None

    with open(NEWS) as f:
        data = json.load(f)

    rnd = parsed["round"] or len(data["items"]) + 1
    note = parsed.get("notes") or parsed.get("winner_note") or ""
    snippet_parts = []
    if parsed["winner_note"]:
        snippet_parts.append(parsed["winner_note"].rstrip(".") + ".")
    if parsed["fast_time"]:
        ft = parsed["fast_time"]
        snippet_parts.append(f"{ft['name']} set fast time at {ft['time']}.")
    if parsed["podium"] and len(parsed["podium"]) >= 3:
        third = parsed["podium"][2]
        snippet_parts.append(f"{third['name']} rounded out the podium.")
    if not snippet_parts and parsed.get("notes"):
        snippet_parts.append(parsed["notes"][:200])

    snippet = " ".join(snippet_parts) or f"{parsed['winner']['name']} took the win at {parsed['track']}."

    item = {
        "date": parsed["date"],
        "category": f"Race Recap · Round {str(rnd).zfill(2)}",
        "headline": f"{parsed['winner']['name']} Takes Round {rnd} at {parsed['track']}",
        "snippet": snippet,
        "byline": "The VMRA Desk",
        "link": "/standings/",
        "feature": True,
        "car_num": str(parsed["winner"]["car"])
    }

    # Drop any older feature flag (only one feature card at a time)
    for it in data["items"]:
        it.pop("feature", None)

    # De-dupe: if we already have an item with same date+round in the headline pattern, replace
    data["items"] = [it for it in data["items"] if not (it.get("date") == item["date"] and "Round " + str(rnd).zfill(2) in it.get("category", ""))]
    data["items"].insert(0, item)
    data["items"] = data["items"][:8]  # keep only the 8 most recent
    data["updated"] = parsed["date"]

    if not dry_run:
        with open(NEWS, "w") as f:
            json.dump(data, f, indent=2)
            f.write("\n")

    return item


def main():
    parser = argparse.ArgumentParser(description="Apply a race update to VMRA site data.")
    parser.add_argument("--file", "-f", help="Read template from this file instead of stdin.")
    parser.add_argument("--dry-run", "-n", action="store_true", help="Show what would change without writing.")
    args = parser.parse_args()

    if args.file:
        with open(args.file) as f:
            text = f.read()
    else:
        if sys.stdin.isatty():
            print("Paste your race-update template, then press Ctrl+D when done:\n", file=sys.stderr)
        text = sys.stdin.read()

    if not text.strip():
        print("ERROR: No input received.", file=sys.stderr)
        sys.exit(1)

    print("\n" + "=" * 60)
    print("PARSING")
    print("=" * 60)
    parsed = parse_template(text)
    print(f"  Race:    {parsed['date']} · {parsed['track']} · Round {parsed['round']}")
    if parsed["winner"]:
        print(f"  Winner:  #{parsed['winner']['car']} {parsed['winner']['name']}")
    if parsed["fast_time"]:
        ft = parsed["fast_time"]
        print(f"  Fast:    #{ft['car']} {ft['name']} · {ft['time']}")
    print(f"  Podium:  {len(parsed['podium'])} entries")
    print(f"  Points:  {len(parsed['points'])} drivers updated")

    print("\n" + "=" * 60)
    print("STANDINGS CHANGES" + (" (DRY RUN)" if args.dry_run else ""))
    print("=" * 60)
    changed, added = update_standings(parsed, dry_run=args.dry_run)
    if changed or added:
        for line in changed: print(line)
        for line in added:   print(line)
    else:
        print("  (no changes)")

    print("\n" + "=" * 60)
    print("RESULTS APPEND" + (" (DRY RUN)" if args.dry_run else ""))
    print("=" * 60)
    entry = append_results(parsed, dry_run=args.dry_run)
    print(f"  Added entry for {entry['date']} Round {entry['round']} at {entry['track']}")

    print("\n" + "=" * 60)
    print("NEWS ITEM" + (" (DRY RUN)" if args.dry_run else ""))
    print("=" * 60)
    news = prepend_news(parsed, dry_run=args.dry_run)
    if news:
        print(f"  Headline: {news['headline']}")
        print(f"  Snippet:  {news['snippet'][:80]}{'…' if len(news['snippet']) > 80 else ''}")
    else:
        print("  (no news item — winner missing)")

    print("\n" + "=" * 60)
    if args.dry_run:
        print("DRY RUN COMPLETE — no files written.")
        print("Re-run without --dry-run to apply.")
    else:
        print("WRITTEN. Now run:")
        print("  cd " + str(ROOT))
        print("  vercel deploy --prod --yes")
    print("=" * 60)


if __name__ == "__main__":
    main()
