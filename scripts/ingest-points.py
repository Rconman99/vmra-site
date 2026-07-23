#!/usr/bin/env python3
"""
ingest-points.py — turn a VMRA points worksheet (.xlsx) into standings.json

The club's worksheet has a fixed shape:
  row 0            header; col A names the venue/date for the round
  rows 1..N        main standings, until a row whose col A is "Rookie of year"
  after that row   rookie standings, same columns

Columns: Driver | Car# | Qualify | Heat | Main | Show up | Points on Date |
         Previous Races Totals | TotalSeason Points

Uses only the standard library — an .xlsx is a zip of XML, so no pandas or
openpyxl needed.

Usage:
  python3 scripts/ingest-points.py "VMRA points work sheet.xlsx"
  python3 scripts/ingest-points.py sheet.xlsx --round 5 --dry-run

Writes wp-theme/vmra/data/standings.json, then deploy with:
  ./scripts/deploy-theme.sh standings.json
"""

import argparse
import json
import re
import sys
import zipfile
from pathlib import Path
from xml.etree import ElementTree as ET

NS = "{http://schemas.openxmlformats.org/spreadsheetml/2006/main}"

# The worksheet abbreviates names; the site shows them in full. Keyed by car
# number because that's what's stable — points follow the car, not the driver.
FULL_NAMES = {
    "68": "Bart Hecter Sr / Bart Hecter Jr",
    "8": "Jason Quatsoe",
    "23": "Kahl Cheth",
    "82": "Vince Conwell",
    "25B": "B. Greiner",
    "57": "Shane Strimple",
    "72": "C. Forney",
    "22": "Steve Woods",
    "77": "Vern Huson",
    "25RT": "RT Greiner",
    "51": "Dave Trapp",
    "7": "Mike Clother",
    "79": "J. Boczar",
    "10": "Bryan Roundtree",
    "2": "Rick Villyard",
    "92": "T. McCartney",
    "6": "R. Learch",
    "11": "B. Cole",
    "30": "Kyten Jones",
    "37": "Mitch Woods",
    "65": "Randy Adams",
    "66": "G. Nash",
    "23x": "Chad Broom",
}


def read_rows(xlsx_path):
    """Extract the first worksheet as a list of row-lists."""
    z = zipfile.ZipFile(xlsx_path)

    shared = []
    if "xl/sharedStrings.xml" in z.namelist():
        root = ET.fromstring(z.read("xl/sharedStrings.xml"))
        for si in root.findall(f"{NS}si"):
            shared.append("".join(t.text or "" for t in si.iter(f"{NS}t")))

    # sheet1.xml isn't guaranteed; find the first worksheet part
    sheet_name = next(
        (n for n in z.namelist() if n.startswith("xl/worksheets/sheet")), None
    )
    if not sheet_name:
        sys.exit("ERROR: no worksheet found in the workbook")

    sheet = ET.fromstring(z.read(sheet_name))

    def col_index(ref):
        letters = re.match(r"([A-Z]+)", ref).group(1)
        n = 0
        for ch in letters:
            n = n * 26 + (ord(ch) - 64)
        return n - 1

    rows = []
    for row in sheet.iter(f"{NS}row"):
        cells = {}
        for c in row.findall(f"{NS}c"):
            v = c.find(f"{NS}v")
            if v is None:
                continue
            cells[col_index(c.get("r"))] = (
                shared[int(v.text)] if c.get("t") == "s" else v.text
            )
        if cells:
            rows.append([cells.get(i, "") for i in range(max(cells) + 1)])
    return rows


def to_int(val):
    try:
        return int(float(val))
    except (TypeError, ValueError):
        return 0


def parse(rows):
    """Split the sheet into (venue_label, main_drivers, rookie_drivers)."""
    if not rows:
        sys.exit("ERROR: worksheet is empty")

    venue = str(rows[0][0]).strip() if rows[0] else ""

    main, rookies, in_rookies = [], [], False

    for row in rows[1:]:
        name = str(row[0]).strip() if len(row) > 0 else ""
        if not name:
            continue

        if name.lower().startswith("rookie"):
            in_rookies = True
            continue

        car = str(row[1]).strip() if len(row) > 1 else ""
        if not car:
            continue

        # TotalSeason Points is the last column; fall back to Points on Date
        total = to_int(row[8]) if len(row) > 8 else 0
        if total == 0 and len(row) > 6:
            total = to_int(row[6])

        entry = {
            "car": car,
            "name": FULL_NAMES.get(car, name),
            "points": total,
            "night": to_int(row[6]) if len(row) > 6 else 0,
        }
        (rookies if in_rookies else main).append(entry)

    if not main:
        sys.exit("ERROR: parsed zero drivers — is this the expected layout?")

    return venue, main, rookies


def rank(drivers):
    """Sort by points desc, then assign 1-based positions."""
    ordered = sorted(drivers, key=lambda d: -d["points"])
    return [
        {
            "position": i,
            "car": d["car"],
            "name": d["name"],
            "points": d["points"],
        }
        for i, d in enumerate(ordered, 1)
    ]


def build_intro(venue, standings, rounds):
    if len(standings) < 2:
        return f"Standings after {rounds} rounds."

    p1, p2 = standings[0], standings[1]
    gap = p1["points"] - p2["points"]
    where = f" at {venue}" if venue else ""

    intro = (
        f"{rounds} rounds down. {p1['name']} #{p1['car']} leads on "
        f"{p1['points']} points{where}"
    )
    if gap == 0:
        intro += f", tied with {p2['name']} #{p2['car']}."
    else:
        intro += (
            f", {gap} ahead of {p2['name']} #{p2['car']} on {p2['points']}."
        )
    if len(standings) > 2:
        p3 = standings[2]
        intro += f" {p3['name']} #{p3['car']} sits third on {p3['points']}."
    intro += " Points stay with the car, not the driver."
    return intro


def main():
    ap = argparse.ArgumentParser(description=__doc__,
                                 formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("xlsx", help="path to the points worksheet")
    ap.add_argument("--round", type=int, help="rounds completed (default: keep existing + 1)")
    ap.add_argument("--date", help="date for 'updated' field, YYYY-MM-DD")
    ap.add_argument("--dry-run", action="store_true", help="print, don't write")
    args = ap.parse_args()

    xlsx = Path(args.xlsx).expanduser()
    if not xlsx.exists():
        sys.exit(f"ERROR: {xlsx} not found")

    out_path = Path(__file__).resolve().parent.parent / "wp-theme/vmra/data/standings.json"

    venue, main_rows, rookie_rows = parse(read_rows(xlsx))

    # A given week's sheet may omit drivers who didn't show. The season roster
    # should still list them at 0 rather than dropping them off the page.
    try:
        existing = json.loads(out_path.read_text())
        seen = {d["car"] for d in main_rows}
        for prior in existing.get("drivers", []):
            if prior["car"] not in seen:
                main_rows.append({
                    "car": prior["car"],
                    "name": prior["name"],
                    "points": prior["points"],
                    "night": 0,
                })
    except Exception:
        pass

    standings = rank(main_rows)
    rookies = rank(rookie_rows)

    # Carry forward round count / date unless overridden
    rounds = args.round
    if rounds is None:
        try:
            rounds = json.loads(out_path.read_text()).get("rounds_completed", 0) + 1
        except Exception:
            rounds = len([d for d in standings if d["points"] > 0]) and 1

    from datetime import date as _date
    updated = args.date or _date.today().isoformat()

    payload = {
        "season": "2026",
        "updated": updated,
        "rounds_completed": rounds,
        "intro": build_intro(venue, standings, rounds),
        "drivers": standings,
        "rookies": {
            "note": "Rookie of the Year standings exclude the 20-point show-up bonus.",
            "drivers": rookies,
        },
    }

    print(f"Sheet header : {venue}")
    print(f"Rounds       : {rounds}")
    print(f"Drivers      : {len(standings)}   Rookies: {len(rookies)}")
    print()
    for d in standings[:8]:
        print(f"  P{d['position']:<2} #{d['car']:<5} {d['name'][:32]:<32} {d['points']}")
    print()

    unmapped = [d["car"] for d in standings if d["car"] not in FULL_NAMES]
    if unmapped:
        print(f"NOTE: no full-name mapping for car(s): {', '.join(unmapped)}")
        print("      Add them to FULL_NAMES in this script.\n")

    if args.dry_run:
        print("(dry run — nothing written)")
        return

    out_path.write_text(json.dumps(payload, indent=2, ensure_ascii=False) + "\n")
    print(f"Wrote {out_path}")
    print("Next: ./scripts/deploy-theme.sh standings.json")


if __name__ == "__main__":
    main()
