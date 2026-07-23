#!/usr/bin/env bash
# deploy-theme.sh — push WP theme files to the VMRA sites via the DirectAdmin API.
#
# There are TWO separate WordPress installs, each with its own copy of the
# theme and its own data files. They drift if you only deploy to one:
#
#   public  →  nwvintagemodified.com        (the real site)
#   temp    →  temp.nwvintagemodified.com   (staging)
#
# SSH (port 22) is disabled on this Big Mountain tier, so the GitHub Action's
# rsync step can't run. This uses the DA file-manager API instead.
#
# Credential lives in the macOS keychain — never in the repo, never in argv:
#   security add-generic-password -a wordpress \
#     -s "web3.bigmountainmail.com:2222" -w 'YOUR_KEY'
#
# Usage:
#   ./scripts/deploy-theme.sh                          # data files → both sites
#   ./scripts/deploy-theme.sh standings.json           # one file  → both sites
#   ./scripts/deploy-theme.sh --target public          # public only
#   ./scripts/deploy-theme.sh --target temp news.json  # one file, temp only
#   ./scripts/deploy-theme.sh --check                  # compare, deploy nothing
#   ./scripts/deploy-theme.sh --force standings.json   # ignore staleness guard
#
# STALENESS GUARD: a file is skipped when the live copy's "updated" date is
# NEWER than the local one — that means the site has content the repo doesn't,
# and pushing would destroy it. This has already happened once with news.json.
# Use --force only when you're certain the local copy should win.
#
# When Jon enables SSH, prefer the GitHub Action over this script.

set -uo pipefail

DA_HOST="web3.bigmountainmail.com:2222"
DA_USER="nwvintage"
REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
LOCAL_THEME="$REPO_ROOT/wp-theme/vmra"

PUBLIC_REMOTE="/domains/nwvintagemodified.com/public_html/wp-content/themes/vmra"
PUBLIC_URL="https://nwvintagemodified.com/wp-content/themes/vmra"
TEMP_REMOTE="/domains/temp.nwvintagemodified.com/public_html/wp-content/themes/vmra"
TEMP_URL="https://temp.nwvintagemodified.com/wp-content/themes/vmra"

TARGETS="public temp"
FORCE=0
CHECK_ONLY=0
FILES=()

while [[ $# -gt 0 ]]; do
  case "$1" in
    --target) TARGETS="$2"; shift 2 ;;
    --force)  FORCE=1; shift ;;
    --check)  CHECK_ONLY=1; shift ;;
    --all)    FILES=("__ALL__"); shift ;;
    -h|--help) sed -n '2,32p' "$0"; exit 0 ;;
    *)        FILES+=("$1"); shift ;;
  esac
done

KEY="$(security find-generic-password -a wordpress -s "$DA_HOST" -w 2>/dev/null)"
if [[ -z "$KEY" ]]; then
  echo "ERROR: no keychain entry for $DA_HOST (account: wordpress)" >&2
  echo "  security add-generic-password -a wordpress -s '$DA_HOST' -w 'YOUR_KEY'" >&2
  exit 1
fi

remote_for() { [[ "$1" == "public" ]] && echo "$PUBLIC_REMOTE" || echo "$TEMP_REMOTE"; }
url_for()    { [[ "$1" == "public" ]] && echo "$PUBLIC_URL"    || echo "$TEMP_URL"; }

json_updated() {  # read .updated from a JSON stream; empty if absent/invalid
  python3 -c "
import json,sys
try: print(json.load(sys.stdin).get('updated',''))
except Exception: print('')
" 2>/dev/null
}

live_updated() {  # $1=target $2=filename
  curl -s --max-time 20 "$(url_for "$1")/data/$2?cb=$(date +%s)" | json_updated
}

local_updated() { # $1=filename
  [[ -f "$LOCAL_THEME/data/$1" ]] && json_updated < "$LOCAL_THEME/data/$1" || echo ""
}

upload() {        # $1=local path  $2=remote dir  $3=label
  local src="$1" rdir="$2" label="$3" name resp
  name="$(basename "$src")"

  if [[ ! -f "$src" ]]; then
    echo "    SKIP  $name — not found locally"
    return 0
  fi

  if [[ "$name" == *.json ]] && ! python3 -m json.tool "$src" >/dev/null 2>&1; then
    echo "    FAIL  $name — invalid JSON, refusing to upload" >&2
    return 1
  fi

  resp="$(curl -s --max-time 60 -k -u "$DA_USER:$KEY" \
    -F "action=upload" -F "path=$rdir" \
    -F "file1=@$src;filename=$name" \
    "https://$DA_HOST/CMD_API_FILE_MANAGER" 2>&1)"

  if [[ "$resp" == *"error=0"* ]]; then
    echo "    OK    $name → $label"
    return 0
  fi
  echo "    FAIL  $name → $label: ${resp:0:120}" >&2
  return 1
}

# Which data files are we acting on?
if [[ ${#FILES[@]} -eq 0 ]]; then
  DATA_FILES=()
  for f in "$LOCAL_THEME"/data/*.json; do DATA_FILES+=("$(basename "$f")"); done
elif [[ "${FILES[0]}" == "__ALL__" ]]; then
  DATA_FILES=()
else
  DATA_FILES=("${FILES[@]}")
fi

# ---------- comparison table ----------
echo ""
echo "  file             local        public       temp         action"
echo "  ---------------- ------------ ------------ ------------ ------------------"

declare -a PLAN_PUBLIC PLAN_TEMP
for name in "${DATA_FILES[@]}"; do
  loc="$(local_updated "$name")";  [[ -z "$loc" ]] && loc="—"
  pub="$(live_updated public "$name")"; [[ -z "$pub" ]] && pub="—"
  tmp="$(live_updated temp "$name")";   [[ -z "$tmp" ]] && tmp="—"

  act=""
  for t in $TARGETS; do
    [[ "$t" == "public" ]] && live="$pub" || live="$tmp"
    if [[ "$loc" == "—" ]]; then
      # No "updated" field to compare — can't apply the staleness guard.
      # Treat as manual-only unless --force says otherwise.
      if [[ $FORCE -eq 1 ]]; then
        act="${act}${t}:push "
        [[ "$t" == "public" ]] && PLAN_PUBLIC+=("$name") || PLAN_TEMP+=("$name")
      else
        act="${act}${t}:no-date "
      fi
    elif [[ "$live" != "—" && "$live" > "$loc" && $FORCE -eq 0 ]]; then
      act="${act}${t}:BLOCKED "
    elif [[ "$live" == "$loc" ]]; then
      act="${act}${t}:same "
    else
      act="${act}${t}:push "
      [[ "$t" == "public" ]] && PLAN_PUBLIC+=("$name") || PLAN_TEMP+=("$name")
    fi
  done

  printf "  %-16s %-12s %-12s %-12s %s\n" "$name" "$loc" "$pub" "$tmp" "$act"
done
echo ""

if [[ $CHECK_ONLY -eq 1 ]]; then
  echo "  (--check: nothing deployed)"
  echo ""
  echo "  BLOCKED means the live file is NEWER than local — pushing would"
  echo "  overwrite site content the repo doesn't have. Pull it down first,"
  echo "  or use --force if you're certain local should win."
  exit 0
fi

# ---------- deploy ----------
FAILED=0
for t in $TARGETS; do
  rdir="$(remote_for "$t")"

  if [[ ${#FILES[@]} -gt 0 && "${FILES[0]}" == "__ALL__" ]]; then
    echo "  $t — full theme sync"
    while IFS= read -r f; do
      rel="${f#$LOCAL_THEME/}"; sub="$(dirname "$rel")"
      [[ "$sub" == "." ]] && d="$rdir" || d="$rdir/$sub"
      upload "$f" "$d" "$t" || FAILED=$((FAILED+1))
    done < <(find "$LOCAL_THEME" -type f ! -name '.DS_Store' ! -name '._*' ! -name '*.zip')
    continue
  fi

  [[ "$t" == "public" ]] && plan=("${PLAN_PUBLIC[@]:-}") || plan=("${PLAN_TEMP[@]:-}")
  [[ -z "${plan[0]:-}" ]] && { echo "  $t — nothing to push"; continue; }

  echo "  $t"
  for name in "${plan[@]}"; do
    [[ -z "$name" ]] && continue
    upload "$LOCAL_THEME/data/$name" "$rdir/data" "$t" || FAILED=$((FAILED+1))
  done
done

# ---------- verify ----------
echo ""
echo "  verifying live:"
for t in $TARGETS; do
  for name in "${DATA_FILES[@]}"; do
    u="$(live_updated "$t" "$name")"
    printf "    %-8s %-16s %s\n" "$t" "$name" "${u:-unreachable}"
  done
done
echo ""

[[ $FAILED -gt 0 ]] && { echo "  $FAILED upload(s) failed" >&2; exit 1; }
echo "  done"
