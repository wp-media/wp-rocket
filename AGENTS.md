# Repository Guidelines

## Project Structure & Module Organization

WP Rocket is a WordPress plugin. The bootstrap is `wp-rocket.php`; core PHP lives in `inc/`, with newer namespaced code under `inc/Engine`, `inc/API`, `inc/Addon`, and `inc/ThirdParty`. Templates are in `views/`. Source assets are in `src/js` and `src/scss`; compiled assets are committed under `assets/js` and `assets/css`. Translations live in `languages/`, dynamic compatibility data in `dynamic-lists*.json`, and tests in `tests/Unit`, `tests/Integration`, and `tests/Fixtures`.

## Build, Test, and Development Commands

Install PHP and JS dependencies with `composer install` and `npm install`. Common commands:

- `npm run build:css` builds all Sass bundles into `assets/css`.
- `npm run build:js` builds JavaScript bundles into `assets/js`.
- `npm run watch:css` / `npm run watch:js` rebuild assets while editing.
- `npm run makepot` regenerates `languages/rocket.pot` using WP-CLI i18n.
- `composer test-unit` runs the unit PHPUnit suite.
- `composer test-integration` runs the default integration suite.
- `composer run-tests` runs the broader configured test matrix.
- `composer phpcs`, `composer phpcs-changed`, and `composer phpcs:fix` check or fix PHP coding standards.
- `composer run-stan` runs PHPStan with the repository baseline.

## Coding Style & Naming Conventions

Follow WordPress Coding Standards and `phpcs.xml`. Use tabs, LF line endings, final newlines, and trimmed trailing whitespace as defined in `.editorconfig`. PHP supports 7.3+, so keep syntax compatible. Prefer short array syntax (`[]`). Prefix global functions, hooks, and symbols with `rocket`, `wp_rocket`, `WPMedia`, or `WPRocket`. Use the `rocket` text domain.

## Testing Guidelines

Place unit tests under `tests/Unit` and integration tests under `tests/Integration`, mirroring the `inc/` path under test. Test files commonly end in `Test.php`, for example `tests/Unit/inc/Logger/Logger/DebugEnabledTest.php`. Add tests for behavior changes, and use targeted scripts for plugin-specific groups, such as `composer test-integration-cloudflare`.

## Commit & Pull Request Guidelines

Recent commits use short imperative or release-oriented subjects, for example `update version to 3.21.3` or `Prepare transifex before alpha release`. Keep commits focused and reference the issue when applicable.

Branches should follow `.github/instructions/pull-request.instructions.md`: `fix/{issue-id}-{description}`, `enhancement/{issue-id}-{title}`, or `test/{issue-id}-{title}`. PRs must use the WP Media template headings exactly, include `Fixes #123` when closing an issue, describe user impact, list testing steps, and justify unchecked checklist items.

## Security & Configuration Tips

Do not report vulnerabilities in public issues; email `contact@wp-media.me` as noted in `CONTRIBUTING.md`. Never commit local credentials, customer data, or environment-specific WordPress configuration.
