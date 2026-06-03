#!/usr/bin/env bash
# Seed the wp-env environment with test data for E2E tests.
# Idempotent — safe to run multiple times.
set -euo pipefail

cd "$(dirname "$0")/.."

WP="npx @wordpress/env run cli wp"

echo "Seeding test data..."

# Set a dummy license key if provided via env var (enables PRO features).
if [[ -n "${WP_ROCKET_TESTS_LICENSE_KEY:-}" ]]; then
  $WP eval "
    \$options = get_option( 'wp_rocket_settings', [] );
    \$options['consumer_key'] = '${WP_ROCKET_TESTS_LICENSE_KEY}';
    update_option( 'wp_rocket_settings', \$options );
  "
  echo "  License key set."
fi

# Flush the cache so the settings page starts from a clean state.
$WP eval "if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); echo 'cache-flushed'; }"

echo "Done seeding."
