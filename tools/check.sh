#!/bin/sh
# Pre-push checks: PHP syntax, JS syntax, CSS brace balance. Run from the repo root.
set -e
if [ "$1" = "--live" ]; then
  python3 - <<'PY'
import re, subprocess, sys
html = subprocess.run(['curl','-s','-m','20','https://stage.dkoreiba.com/'], capture_output=True).stdout.decode('utf-8','replace')
urls = sorted(set(re.findall(r'(/wp-content/cache/minify/[a-z0-9]+\.js\?x\d+)', html)))
bad = 0
for u in urls:
    n = len(subprocess.run(['curl','-s','-m','20','https://stage.dkoreiba.com'+u], capture_output=True).stdout)
    print(u, n); bad |= (n == 0)
print('live minify ' + ('FAILED: empty files' if bad or not urls else 'ok')); sys.exit(1 if bad or not urls else 0)
PY
  exit $?
fi
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
