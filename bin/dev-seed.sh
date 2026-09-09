#!/usr/bin/env bash
# Seed the wp-env environment with test data for E2E tests.
# Idempotent — safe to run multiple times.
set -euo pipefail

cd "$(dirname "$0")/.."

WP="npx @wordpress/env run cli wp"

echo "Seeding test data..."

# Fall back to the local PHPUnit credentials file if no env vars were passed in.
LICENSE_FILE="tests/env/local/license.php"
if [[ -z "${WP_ROCKET_TESTS_LICENSE_KEY:-}" && -f "$LICENSE_FILE" ]]; then
  WP_ROCKET_TESTS_LICENSE_KEY="$(php -r "require '$LICENSE_FILE'; echo defined('ROCKET_KEY') ? ROCKET_KEY : '';")"
  WP_ROCKET_EMAIL="${WP_ROCKET_EMAIL:-$(php -r "require '$LICENSE_FILE'; echo defined('ROCKET_EMAIL') ? ROCKET_EMAIL : '';")}"
  if [[ -n "$WP_ROCKET_TESTS_LICENSE_KEY" ]]; then
    echo "  Using credentials from $LICENSE_FILE."
  fi
fi

# Set a dummy license key if provided via env var (enables PRO features).
if [[ -n "${WP_ROCKET_TESTS_LICENSE_KEY:-}" ]]; then
  $WP eval "
    \$options = get_option( 'wp_rocket_settings', [] );
    \$options['consumer_key'] = '${WP_ROCKET_TESTS_LICENSE_KEY}';
    update_option( 'wp_rocket_settings', \$options );
  "
  echo "  License key set."

  # Set WP_ROCKET_EMAIL and WP_ROCKET_KEY as wp-config constants for dual validation.
  # Always set the key constant if we have a license key
  $WP config set WP_ROCKET_KEY "${WP_ROCKET_TESTS_LICENSE_KEY}" --raw

  # Only set the email constant if explicitly provided
  if [[ -n "${WP_ROCKET_EMAIL:-}" ]]; then
    $WP config set WP_ROCKET_EMAIL "$WP_ROCKET_EMAIL" --raw
  fi
  echo "  wp-config constants set."
fi

# Flush the cache so the settings page starts from a clean state.
$WP eval "if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); echo 'cache-flushed'; }"

echo "Done seeding."
