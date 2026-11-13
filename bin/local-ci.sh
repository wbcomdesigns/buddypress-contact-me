#!/usr/bin/env bash
# Local CI: php -l on PHP 8.1 and 8.5 (Docker), WPCS, PHPStan, text domain.
set -euo pipefail
cd "$(dirname "$0")/.."
for v in 8.1 8.5; do
	echo "== php -l $v"
	docker run --rm -v "$PWD":/p -w /p "php:$v-cli" sh -c \
		'find . -name "*.php" -not -path "./node_modules/*" -not -path "./vendor/*" -print0 | xargs -0 -n1 php -l | grep -v "^No syntax" || true'
done
echo "== phpcs (errors gate)"; phpcs -q -n
echo "== phpstan"; phpstan analyse -c phpstan.neon.dist --no-progress --memory-limit=2G || true
echo "== text domain"; npx grunt checktextdomain
