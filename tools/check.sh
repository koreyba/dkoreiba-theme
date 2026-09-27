#!/bin/sh
# Pre-push checks: PHP syntax, JS syntax, CSS brace balance. Run from the repo root.
set -e
docker run --rm -v "$PWD/blocksy-child":/t -w /t php:8.2-cli sh -c 'for f in $(find . -name "*.php"); do php -l "$f" >/dev/null || exit 1; done' && echo "php ok"
for f in blocksy-child/assets/js/*.js; do node --check "$f"; done && echo "js ok"
python3 - <<'PY'
import glob, sys
bad = 0
for f in glob.glob('blocksy-child/assets/css/*.css'):
    s = open(f).read(); d = 0
    for i, ch in enumerate(s):
        d += (ch == '{') - (ch == '}')
        if d < 0:
            print(f, 'extra } at line', s[:i].count('\n') + 1); bad = 1; d = 0
    if d: print(f, 'unclosed {'); bad = 1
print('css ok' if not bad else 'css FAILED'); sys.exit(bad)
PY
