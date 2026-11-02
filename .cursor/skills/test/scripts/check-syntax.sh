#!/usr/bin/env bash
# Syntax-check the files prepared for the next commit.
# Exit 0 when every check passes, 1 when a check fails, 2 when git cannot be used.

set -u

root=$(git rev-parse --show-toplevel 2>/dev/null) || {
  echo "Not a git repository." >&2
  exit 2
}
cd "$root" || exit 2

if ! files=$(
  set -euo pipefail
  {
    git diff --cached --name-only --diff-filter=ACMR
    git diff --name-only --diff-filter=ACMR
    git ls-files --others --exclude-standard
  } | awk 'NF && !seen[$0]++'
); then
  echo "Could not list files prepared for commit." >&2
  exit 2
fi

skip() {
  case "$1" in
    vendor/* | api/vendor/* | */vendor/* | node_modules/* | */node_modules/*)
      return 0
      ;;
    api/storage/* | api/bootstrap/cache/*)
      return 0
      ;;
    *.lock | *.min.js | *.min.css | *.png | *.jpg | *.jpeg | *.gif | *.webp | *.svg | *.ico | *.pdf | *.woff | *.woff2)
      return 0
      ;;
  esac
  return 1
}

kept=""
while IFS= read -r file; do
  [ -n "$file" ] || continue
  [ -f "$file" ] || continue
  skip "$file" && continue
  kept="${kept}${file}"$'\n'
done <<< "$files"

if [ -z "$kept" ]; then
  echo "# empty"
  exit 0
fi

echo "# files"
printf '%s' "$kept"

echo "# syntax"
failed=0

check() {
  local file="$1"
  local tool="$2"
  shift 2
  local output
  if output=$("$@" 2>&1); then
    echo "pass ${tool} ${file}"
  else
    echo "fail ${tool} ${file}"
    printf '%s\n' "$output" | sed 's/^/  /'
    failed=1
  fi
}

while IFS= read -r file; do
  [ -n "$file" ] || continue
  case "$file" in
    *.blade.php | *.twig | *.css | *.md | *.yml | *.yaml)
      echo "skip ${file} (no syntax checker)"
      ;;
    *.php)
      if command -v php >/dev/null 2>&1; then
        check "$file" php php -l "$file"
      else
        echo "skip ${file} (php is not installed)"
        failed=1
      fi
      ;;
    *.js | *.mjs | *.cjs)
      if command -v node >/dev/null 2>&1; then
        check "$file" node node --check "$file"
      else
        echo "skip ${file} (node is not installed)"
        failed=1
      fi
      ;;
    *.json)
      if command -v php >/dev/null 2>&1; then
        check "$file" json php -r 'json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);' "$file"
      else
        echo "skip ${file} (php is not installed)"
        failed=1
      fi
      ;;
    *.sh)
      check "$file" bash bash -n "$file"
      ;;
    *)
      echo "skip ${file} (no syntax checker)"
      ;;
  esac
done <<< "$kept"

exit "$failed"
