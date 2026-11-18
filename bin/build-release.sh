#!/usr/bin/env bash
# Build dist/buddypress-contact-me-{version}.zip (runs local CI first).
set -euo pipefail
cd "$(dirname "$0")/.."
slug=buddypress-contact-me
rm -rf dist
bin/local-ci.sh
version=$(grep -m1 -E '^\s*\*\s*Version:' "$slug.php" | awk '{print $NF}')
rm -rf dist && mkdir -p "dist/$slug"
rsync -a --exclude-from=<(sed 's#^/##' .distignore) --exclude=dist ./ "dist/$slug/"
# Deflate every entry: the zip CLI stores tiny files (index.php stubs) as plain text, which upload firewalls block.
( cd dist && python3 -I -c 'import os,sys,zipfile
with zipfile.ZipFile(sys.argv[1], "w", zipfile.ZIP_DEFLATED) as z:
    for r, d, f in os.walk(sys.argv[2]):
        d.sort()
        for n in sorted(f): z.write(os.path.join(r, n))' "$slug-$version.zip" "$slug" )
[ "$(LC_ALL=C grep -a -c '<?php' "dist/$slug-$version.zip" || true)" = 0 ] || { echo "Zip has plain-text PHP"; exit 1; }
leaks=$(unzip -l "dist/$slug-$version.zip" | grep -iE '/docs/|/audit/|/bin/|/tests/|node_modules|CLAUDE|gruntfile|package' || true)
[ -z "$leaks" ] || { echo "Zip contains dev files:"; echo "$leaks"; exit 1; }
echo "Built dist/$slug-$version.zip"
