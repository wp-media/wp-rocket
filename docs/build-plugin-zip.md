# Build the Plugin Zip

This repository includes a repeatable build script for creating an installable WordPress plugin archive.

## Requirements

- PHP compatible with the project, currently PHP 7.3+
- Composer 2
- `zip`

JavaScript and Sass assets are already committed in `assets/`. Rebuild them before packaging only when files in `src/js` or `src/scss` changed:

```bash
npm install
npm run build:css
npm run build:js
```

## Build Locally

From the repository root:

```bash
composer install
bin/build-plugin-zip.sh
```

In GitHub Codespaces, the default `php` shim may point to an older Oryx build that fails on Ubuntu 24.04 with an OpenSSL 1.1 error. Use the bundled PHP 8.4 build for Composer commands:

```bash
PATH=/usr/local/php/8.4.15/bin:$PATH XDEBUG_MODE=off composer install
PATH=/usr/local/php/8.4.15/bin:$PATH XDEBUG_MODE=off bin/build-plugin-zip.sh
```

The script creates `build/wp-rocket.zip`. To choose another file name:

```bash
bin/build-plugin-zip.sh wp-rocket-custom.zip
```

The zip contains the production plugin directory `wp-rocket/`, installs Composer production dependencies, and excludes development-only paths such as `bin/`, `docs/`, `src/`, `tests/`, `.github/`, and local dependency folders. It packages the current working tree, so uncommitted local edits are included.

## Install in WordPress

1. Open the WordPress admin dashboard.
2. Go to Plugins > Add New > Upload Plugin.
3. Upload `build/wp-rocket.zip`.
4. Activate WP Rocket.

This build does not require a WP Rocket account email, API key, or license validation to unlock the local plugin functionality.

## GitHub Actions

The `Build Plugin Zip Release` workflow runs only when a pull request is merged into `develop`, `master`, or `main`. It builds `build/wp-rocket.zip`, creates a GitHub Release tagged as `wp-rocket-{branch}-{short-sha}`, and uploads the zip as a release asset. It does not run for regular pushes or open pull request updates.
