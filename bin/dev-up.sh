#!/usr/bin/env bash
# Boot the wp-env Docker environment and activate the plugin.
# Idempotent — safe to run if the environment is already running.
set -euo pipefail

SEED="${1:-}"

cd "$(dirname "$0")/.."

echo "Starting wp-env..."
npx @wordpress/env start

echo "Activating plugin..."
npx @wordpress/env run cli wp plugin activate wp-rocket

if [[ "$SEED" != "--no-seed" ]]; then
  bash bin/dev-seed.sh
fi

echo "Done. WordPress is available at http://localhost:8888"
echo "Admin: http://localhost:8888/wp-admin  (admin / password)"
