# WP Rocket – AI Coding & Architecture Guidelines

This file defines NON-NEGOTIABLE rules for any AI-assisted work
(Claude Code, ChatGPT, JetBrains AI Assistant, Cursor, etc.)
in this repository.

Skills define behavioral guidance.
AGENTS.md defines mandatory guardrails.
If a conflict exists, AGENTS.md prevails.

---

## Operating Principles

These five rules apply to every agent, in every phase, before any skill-specific guidance loads.

1. **Surface assumptions before building.** If the spec or codebase leaves something ambiguous, state the assumption explicitly before acting on it — don't silently guess.
2. **Stop when requirements conflict.** If the issue, the spec, and the codebase contradict each other, stop and surface the conflict. Proceeding on a guess produces bugs that are hard to trace.
3. **Push back when warranted.** If the simplest correct solution differs from the plan, say so. Prefer boring, obvious solutions over clever ones. An elegant approach that introduces risk is worse than a dull one that doesn't.
4. **Touch only what you are asked to touch.** Scope discipline is the single biggest determinant of whether a PR is mergeable. Do not refactor adjacent code, rename unrelated identifiers, or "clean up while you're in the area."
5. **Verification is not optional.** "Seems right" never closes a task. Every change must be confirmed by running tests, tools, or a manual scenario — not by reading the code and inferring it should work.

---

## Project Configuration

Read by the GAS delivery pipeline (`gas-delivery-pipeline-templates`) agents and skills.

Locally, run every PHP command inside the wp-env tests container, never with a local PHP binary:
`docker exec -w /var/www/html/wp-content/plugins/wp-rocket <tests-wordpress container from docker ps> <command>`.

**Commands:**

| Setting | Value | Used by |
|---|---|---|
| Test command | `composer test-unit`, then `composer test-integration`. The full matrix (`composer run-tests`, 46 phpunit runs) is left to CI | implementer, dod |
| Test — targeted subset | `composer test-unit -- --filter <Name>` and `vendor/bin/phpunit --configuration tests/Integration/phpunit.xml.dist --group <Group>` | implementer, dod |
| Lint command | `composer phpcs-changed`, then `composer phpcs` | implementer, dod |
| Lint — auto-fix | `composer phpcs:fix` | implementer, dod |
| Static analysis | `composer run-stan` | dod |
| Build command | `npm run build:js && npm run build:css` (no JS lint script exists) | implementer |
| Install command | `composer install --no-scripts` and `npm install` | setup |

**Local run:**

| Setting | Value | Used by |
|---|---|---|
| Boot command | `bash bin/dev-up.sh` (wp-env, requires Docker) | qa-engineer, e2e-qa-tester |
| Base URL | `http://localhost:8888` | qa-engineer, e2e-qa-tester |
| Test credentials / login flow | `admin` / `password` at `/wp-login.php` (`#user_login`, `#user_pass`, `#wp-submit`) | qa-engineer, e2e-qa-tester |

**Labels & branches:**

| Setting | Value | Used by |
|---|---|---|
| AI label | `Made by AI` | pr-opener, ticket-writer, orchestrator |
| Epic label | `epics 🔥` | ticket-writer, orchestrator |
| Type labels | `type: bug`, `type: enhancement`, `type: new feature`, `type: sub-task`, `type: regression` | ticket-writer |
| Issue status transition | → `Ready for review` | orchestrator |
| Base branch | `develop` | all |
| Branch prefixes | `fix` / `enhancement` / `test` (see §6) | orchestrator |
| Worktree isolation | off | orchestrator |
| Protected branches (never push directly) | `develop` (merge queue), `trunk` | all |

## WordPress Project Configuration

Read by the `gas-wordpress-engineering` skills.

| Setting | Value |
|---|---|
| Text domain | `rocket` |
| Custom capability map | `rocket_manage_options` (general plugin management, default), `rocket_purge_cache`, `rocket_preload_cache`, `rocket_remove_unused_css`, `rocket_regenerate_critical_css`, `rocket_purge_cloudflare_cache`, `rocket_purge_sucuri_cache`, `rocket_purge_posts`, `rocket_purge_terms`, `rocket_purge_users`. Never use `manage_options` for WP Rocket actions. New capabilities must be added here and to `phpcs.xml` |
| REST namespace | `wp-rocket/v1` |
| Hook-registration style | Subscriber + ServiceProvider (see §3). Never call `add_action` / `add_filter` directly |
| Feature/module entry points | `inc/Engine/<Feature>/ServiceProvider.php` (see §3 module layout) |
| Admin JS / data layer | Vanilla JS in `src/js` (built to `assets/js`), no jQuery. Views in `views/` |
| PHPCS ruleset | `phpcs.xml` |
| Unit test base class | `WP_Rocket\Tests\Unit\TestCase` (stubs `rocket_get_constant()` from `$this->constants`) |
| Integration test base classes | `TestCase`, `AdminTestCase`, `AjaxTestCase` (see §4.2) |
| PHPUnit configs | `tests/Unit/phpunit.xml.dist`, `tests/Integration/phpunit.xml.dist` |
| Fixture / data-provider helper | Inherited `configTestData()` provider + fixture files under `tests/Fixtures/` mirroring the source path |
| Abilities vendor slug | `wp-rocket` (`wp-rocket/<ability-name>`) |
| Abilities registration | One class per ability implementing `WP_Rocket\Engine\Abilities\AbilitiesInterface`, bound in the feature's ServiceProvider, registered from its Subscriber on `wp_abilities_api_init` (categories on `wp_abilities_api_categories_init`). Reference: `inc/Engine/Abilities/Options/GetOptions.php` |
| Default ability capability | `rocket_manage_options` |
| Analytics/telemetry wrapper | `$this->track_event( 'MCP Ability Executed', [ 'ability' => …, 'context' => 'wp_plugin_mcp' ] )` (see `GetOptions::execute()`) |

---

The objective is to keep WP Rocket:

- WordPress.org compliant
- Architecturally consistent
- Secure
- Maintainable
- Review-friendly

This document applies to ALL automated or AI-generated changes.

---

# 1. Project Overview

WP Rocket is a single-edition commercial WordPress caching plugin maintained by WP Media.

Core architectural patterns:
- **Subscriber pattern** — event-driven hooks via `Subscriber_Interface`
- **League Container** — dependency injection container
- **ServiceProvider pattern** — modules register their own bindings and subscribers
- **PSR-4 autoloading** — `WP_Rocket\` namespace maps to `inc/`

When modifying architecture:
- Follow existing patterns (Subscriber → Container → ServiceProvider).
- Prefer adding new ServiceProviders over modifying existing ones.
- Keep infrastructure concerns out of Subscriber classes.

---

# 2. Coding Standards & Static Analysis

Source of truth:

- Composer scripts (`composer.json`)
- PHPCS rulesets (`phpcs.xml` / `phpcs.xml.dist` / `phpcs.baseline.xml`)
- PHPStan / Psalm configs if present (`phpstan.neon`, `phpstan-baseline.neon`)
- WordPress Plugin Check: https://github.com/WordPress/plugin-check/
- CI pipeline rules

WP Rocket must remain compatible with WordPress.org validation rules.

Any change affecting public APIs, output, security, metadata, or plugin bootstrap
behavior must be evaluated against WordPress Plugin Check expectations.

AI MUST:

- Read `composer.json` first and use defined scripts (e.g. `lint`, `phpcs`, `phpcbf`, `test`, `phpstan`) instead of inventing commands.
- Auto-discover PHPCS configuration and follow it as the single source of truth.

## 2.1 Tooling Auto-Discovery (MANDATORY)

Before making changes that affect standards or formatting, the agent MUST locate and
respect the repository configuration files.

### Required reads (in this order)
1) `composer.json`
    - Use scripts defined in `"scripts"` whenever possible.
    - Prefer the exact commands used by CI.
    - Do not invent lint/test commands.

2) PHPCS ruleset / baseline (first match wins, but consider all if referenced):
    - `phpcs.xml`
    - `phpcs.xml.dist`
    - `phpcs.baseline.xml`
    - Any PHPCS file referenced by composer scripts or CI

3) Static analysis configs (if present / referenced):
    - `phpstan.neon`, `phpstan.neon.dist`
    - `phpstan-baseline.neon`

### Execution rules
- Do NOT hardcode PHPCS standards.
- Do NOT assume WordPress-Core or WordPress-Extra unless defined in the ruleset.
- If multiple PHPCS files exist, follow what is referenced by:
  a) Composer scripts, then
  b) CI configuration, then
  c) Root-level `phpcs.xml(.dist)`

If no PHPCS configuration exists, stop and ask.

## 2.2 PHPStan Custom Rules (MANDATORY)

WP Rocket ships four custom PHPStan rules. Every change must satisfy them:

| Rule | What it enforces |
|---|---|
| `DiscourageApplyFilters` | Use `wpm_apply_filters_typed()` instead of `apply_filters()` |
| `DiscourageWPOptionUsage` | Use injected Option objects instead of `get_option()` directly |
| `EnsureCallbackMethodsExistsInSubscribedEvents` | Every method name declared in `get_subscribed_events()` must exist in the class |
| `NoHooksInORM` | No WordPress hooks (`add_action`, `add_filter`, `apply_filters`) inside database Query/Table classes |

**`wpm_apply_filters_typed()` is mandatory for all new filters:**
```php
// ❌ Never — flagged by DiscourageApplyFilters
$value = apply_filters( 'rocket_my_filter', $default );

// ✅ Always — type-safe, with required docblock
/**
 * Filters the custom value.
 *
 * @param string $value The custom value.
 * @return string
 */
$value = wpm_apply_filters_typed( 'string', 'rocket_my_filter', $default );
```

Available types: `'string'`, `'integer'`, `'boolean'`, `'array'`, `'string[]'`.

**Option objects are mandatory for reading plugin settings:**
```php
// ❌ Never
$value = get_option( 'wp_rocket_settings' );

// ✅ Always — inject Options_Data via constructor
/** @var Options_Data */
private $options;

public function __construct( Options_Data $options ) {
    $this->options = $options;
}

$value = $this->options->get( 'option_key', $default );
```

---

# 3. Architectural Integrity

AI must NOT:

* Introduce global state.
* Add new singletons without discussion.
* Bypass the League Container / dependency injection patterns used in the project.
* Couple UI logic to infrastructure logic.
* Modify `inc/Dependencies/` without explicit instruction (vendored code).
* Use `add_action` / `add_filter` directly — always use Subscribers.
* Use `apply_filters()` directly — always use `wpm_apply_filters_typed()`.
* Use `get_option( 'wp_rocket_settings' )` directly — inject an `Options_Data` instance.

Follow existing patterns:

* **Subscriber** → implements `Subscriber_Interface`, declares `get_subscribed_events()`
* **ServiceProvider** → extends `AbstractServiceProvider`, binds services in `register()`
* **Context classes** → `inc/Engine/Feature/Context/Context.php` encapsulates "should this feature run?" logic; inject into Subscribers, never inline those checks
* **Container wiring** → via ServiceProvider only, never manual `new ClassName()`
* Strict types where already used
* Namespacing: `WP_Rocket\Engine\*` for engine features, `WP_Rocket\Admin\*` for admin

### Standard module directory structure

When adding a new feature module, follow this layout:

```
inc/Engine/MyFeature/
├── ServiceProvider.php       # binds all services and declares $provides
├── Context/
│   └── Context.php          # is this feature active? (injected into Subscriber)
├── Admin/
│   └── Subscriber.php       # admin-only hooks
├── Frontend/
│   ├── Controller.php       # business logic
│   └── Subscriber.php       # frontend hooks
└── Database/                # only when custom tables are needed
    ├── Tables/MyFeature.php
    ├── Queries/MyFeature.php
    ├── Rows/MyFeature.php
    └── Schemas/MyFeature.php
```

### BerlinDB table versioning

Table version format is `YYYYMMDD`. Migrations are declared in `$upgrades`:

```php
protected $version = 20251006;
protected $upgrades = [
    20251006 => 'add_new_column',
];
protected function add_new_column(): void { /* ALTER TABLE … */ }
```

---

# 4. Testing & Validation

WP Rocket follows **Test-Driven Development**. Write tests before or alongside new code, not after.

## 4.1 Test location and commands

| Type | Location | Command |
|---|---|---|
| Unit | `tests/Unit/` | `composer test-unit` |
| Integration | `tests/Integration/` | `composer test-integration` |
| Specific group | — | `vendor/bin/phpunit --configuration tests/Integration/phpunit.xml.dist --group FeatureName` |

Test files **mirror** the source structure: `inc/Engine/Foo/Bar.php` → `tests/Unit/inc/Engine/Foo/Bar/methodName.php`.

## 4.2 Which test type to write

- **Unit** — business logic in isolation; mock all dependencies with Brain\Monkey / Mockery; no WordPress context needed.
- **Integration** — WordPress hooks, database operations, or hook interactions; extend the appropriate base class (`TestCase`, `AdminTestCase`, `AjaxTestCase`). These bases fail a test on any HTTP request it doesn't mock: key the responses by exact URL in `$this->config['http']` (vendor `HttpRequestTrait`), never with a bespoke `pre_http_request` filter or a Brain\Monkey `wp_remote_*` mock.

## 4.3 Key patterns

**Unit test with data provider:**
```php
class ProcessDataTest extends TestCase {
    /** @dataProvider dataProvider */
    public function testShouldReturnExpectedResult( $input, $expected ): void {
        $result = ( new MyService() )->process( $input );
        $this->assertSame( $expected, $result );
    }
    public function dataProvider(): array { return [ ... ]; }
}
```

**Integration test with fixture:**
```php
/** @group MyFeature */
class ProcessDataTest extends TestCase {
    /** @dataProvider configTestData */
    public function testShouldReturnExpectedResult( $config, $expected ): void { ... }
}
// fixture: tests/Fixtures/inc/Engine/MyFeature/…/processData.php → return [ 'scenario' => [ 'config' => …, 'expected' => … ] ];
```

## 4.4 Validation checklist

For every change:

1. Run `composer phpcs-changed` first (fast: checks only modified files), then `composer phpcs` before committing.
2. Run `composer run-stan` — satisfy all four custom PHPStan rules (§2.2).
3. Run the relevant test suite; no regressions.
4. Do not delete tests unless clearly obsolete.

If modifying templates:

* Validate escaping correctness.
* Ensure no functional regressions.

---

# 5. AI Working Protocol

AI must work in small, incremental changes.

After each logical change set:
- explain what changed
- explain why
- list potential edge cases

AI must NOT:

* Perform massive automated refactors without approval.
* Reorganize files without explicit instruction.
* Rewrite entire classes when a minimal fix is sufficient.

## 5.1 Git Commit & Push Policy

By default, AI may only **suggest** commit messages and must not run `git commit` or `git push`.

**Exception — Delivery Pipeline:** When operating under the GAS `orchestrator` skill (`/gas-delivery-pipeline-templates:orchestrator`, or triggered by `/task <number>`, `issue <number>`, or `#<number>`), the agent MAY:

1. Run atomic `git commit` calls — one commit per logical, self-contained change set.
2. Run `git push` exactly once after all commits are ready, to publish the branch.
3. Create a GitHub Pull Request using the prepared PR draft.
4. Monitor PR CI status checks until all pass or a failure is detected.

Atomic commit rules:
- Each commit must pass PHPCS and static analysis before being committed.
- Commit message format: `type(scope): short description` (Conventional Commits).
- Do not squash unrelated changes into a single commit.
- Do not amend commits that have already been pushed.

---

# 6. PR Hygiene

Changes must:

* Be minimal.
* Be scoped.
* Have clear intent.
* Avoid noise in diff.
* Avoid unrelated formatting changes.

### Branch Naming Convention

Branches MUST follow these patterns:

- **Bug fixes**: `fix/{GitHub-issue-ID}-{description}`
- **Enhancements**: `enhancement/{GitHub-issue-ID}-{GitHub-issue-title}`
- **Tests**: `test/{GitHub-issue-ID}-{GitHub-issue-title}`

Rules:
- Lowercase letters, hyphens for spaces.
- Always include the GitHub issue ID.
- Keep descriptions concise (first 4 words max).

---

# 7. Security First

Always assume:

* User input is untrusted.
* Remote API responses are untrusted.
* Stored values may be tampered with.

Never:

* Store sensitive values in plain text without review.
* Introduce unsafe serialization.
* Echo unescaped dynamic data.

---

# 8. When in Doubt

Stop.
Explain the ambiguity.
Ask for clarification.

Architectural integrity is more important than speed.

---

# 9. QA Conventions

The GAS `qa-engineer` agent validates PRs at the end of the delivery pipeline and delegates
browser checks to `e2e-qa-tester`. This repo overrides `e2e-qa-tester` in
`.claude/agents/e2e-qa-tester.md` with the WP Rocket app flows; it drives the browser with
`playwright-cli`.

The local environment is wp-env (Docker), booted with `bash bin/dev-up.sh` at
`http://localhost:8888` (admin / password).

- **Browser QA is mandatory** when the diff touches JS, CSS, HTML or templates, or PHP that renders
  admin output: `rocket_notice_html()`, `rocket_notice_writing_permissions()`, `wp_admin_notice()`,
  `add_settings_error()`, `admin_notices` callbacks, or any PHP that echoes HTML for the browser.
- **Environment guards:** criteria rendered behind `rocket_valid_key()`,
  `get_rocket_option( 'consumer_key' )`, `RocketLicence`, `rocket_is_live_site()` or an `is_ssl()`
  gate may not render on localhost. Name the guard (file:line) and mark the criterion
  `CANNOT_VERIFY` instead of reporting a false result.
- **Smoke tests** adjacent to the change: the settings page
  (`/wp-admin/options-general.php?page=wprocket`) loads, the WP Rocket admin bar item renders on
  `/wp-admin/`, and the plugin deactivates/reactivates cleanly when bootstrap code changed.
- **Web-accessible file writes:** if the PR writes a file under `wp-content/`, trigger the write,
  then `curl` the generated file's real URL and confirm 403/404, never 200 with its content.
- Jest is not set up. Don't run JS unit tests.

---

# 10. Skills Activation

Generic pipeline and WordPress skills come from the GAS plugins (`gas-delivery-pipeline-templates`,
`gas-wordpress-engineering`). The repository only ships the WP Rocket-specific skills under
`.claude/skills/`.

Agents MUST activate the relevant skill depending on the task:

- Template, UI, output, hooks, sanitization or escaping changes → `gas-wordpress-engineering:wordpress-compliance`
- Structural or architectural changes, constants, PRs → `wp-rocket-architecture`
- Admin JS, CSS or templates → `wp-rocket-frontend-architecture`
- Core service modifications → `wp-rocket-architecture` + `gas-wordpress-engineering:wordpress-compliance`
- Adding or changing an ability → `gas-wordpress-engineering:wordpress-ability`
- Writing PHPUnit tests → `gas-wordpress-engineering:wordpress-phpunit-tests`
- Codebase exploration / dependency tracing → `gas-delivery-pipeline-templates:knowledge-graph`

## 10.1 Knowledge Graph

A dependency graph is generated at `.claude/graph/dependency-graph.json` (not tracked in git).

Before exploring the codebase structure (finding a class, tracing dependencies, checking
namespace boundaries), **read this file first**. It contains:
- `nodes`: per-file namespace, declared symbols, and imports.
- `symbol_index`: maps every fully-qualified PHP class/interface/trait/enum to its file.

Build or refresh it from the repo root with `node "<knowledge-graph skill base directory>/scripts/build-graph.js"`
(`--full` to force a rebuild). The builder is bundled in the
`gas-delivery-pipeline-templates:knowledge-graph` skill; this repo does not contain
`.claude/skills/knowledge-graph/`. If the builder can't run, fall back to grep/glob.

---

# 11. Repository Identity

Canonical GitHub repository: `wp-media/wp-rocket`

Unless explicitly instructed otherwise, all GitHub issue, PR, and branch workflows must
assume this repository.

---

# 12. Repository Specs

The repository may define task-specific implementation specs under:

`.claude/specs/` (e.g. `.claude/specs/phpcs/` for escaped output, nonce verification, and
validated/sanitized input remediation)

Specs provide detailed guidance for recurring technical problems
(e.g. PHPCS warnings, architecture migrations, WordPress compliance patterns).

When a relevant spec exists, agents must follow it in addition to:

• AGENTS.md
• the applicable skills


# AI Task Priority

When executing tasks, agents must prioritize:

1. Security
2. WordPress.org compliance
3. Architectural integrity
4. Backward compatibility
5. Minimal diffs
6. Performance

AGENTS.md remains the final authority.

---

# 13. Session Learnings

**Human-curated only.** Never regenerate this section with an LLM — doing so degrades
agent success rates. After each pipeline run, a human adds entries for findings that were
surprising and are not already derivable from the code or other sections of this file.

Format per entry:
```
- **[YYYY-MM-DD] [module or area]**: What was surprising. What the correct approach is.
```

Agents MUST read this section. It takes precedence over any assumption derived from the
spec or skill files when there is a conflict.

---

_No entries yet. Add one after the first surprising pipeline finding._

---

# 14. Delivery Pipeline Conventions

Project-specific guidance for the GAS delivery pipeline agents. It extends §1–§7 and never
overrides them.

## Grooming conventions

Effort is sized by the grooming and challenger agents; this repo does not define its own scale.

**Always flag as HIGH risk regardless of effort:**
- Multisite behavior
- The cache serving or purge path
- Writes to `.htaccess`, `wp-config.php`, `advanced-cache.php` or other server/WordPress config files
- BerlinDB schema changes (`$version` / `$upgrades`, see §3)
- Settings (`wp_rocket_settings`) structure or migrations
- Activation, deactivation, uninstall or upgrade routines
- Licence, updater or remote API calls
- Any file written under `wp-content/` (backups, exports, logs, generated config) that may hold
  secrets, API keys, tokens, licence data or PII. It must live outside the web-served tree or be
  served only through an authenticated PHP handler. `.htaccess deny` does nothing on Nginx, an
  `index.php` stub doesn't block direct requests to sibling files, and a guessable filename is not a
  mitigation. Flag a missing real mitigation as MUST_HAVE.

**Places that often need to change too:** WordPress option names, hooks, Subscribers and their
ServiceProvider, and multisite handling.

**Knowledge graph** (§10.1), scan dirs `inc`, `src`. Useful queries:
- Locate a class: `symbol_index["WP_Rocket\\…"]`.
- Subscribers in a module: nodes whose declared class implements `Subscriber_Interface`.
- The ServiceProvider wiring a service: nodes extending `AbstractServiceProvider` that import it.
- A `Frontend` namespace importing from `Admin` is a red flag.
- `inc/Dependencies/` is vendored: ignore it.

## Review conventions

Load `wp-rocket-architecture` and `gas-wordpress-engineering:wordpress-compliance` for every diff,
plus `wp-rocket-frontend-architecture` when it touches JS, CSS or templates.

**Security:**
- Escape at the output boundary (`esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`); use
  the pre-escaped i18n helpers and never double-escape.
- `wp_unslash()` before any `sanitize_*` call; use type-appropriate sanitizers.
- Forms and side-effect requests: `wp_nonce_field()` + `check_admin_referer()`, with nonce actions
  named `rocket_<feature>_<action>`, plus a WP Rocket capability check (see the capability map).
- No blanket `phpcs:ignore`.
- Sensitive files under `wp-content/`: see the last HIGH-risk item in Grooming conventions. A
  confirmed instance is at least HIGH; an exploitable one is CRITICAL.

**Architecture:** the four PHPStan custom rules (§2.2), no direct `add_action` / `add_filter`
(Subscribers only), constants read through `rocket_get_constant()`.

**Sibling updates to check outside the diff:**
- Option key added or changed → migration, default value and sanitize callback.
- Hook added → registered with the right priority and documented.
- Behavior changed → related UI state (notice flags such as `_notice_displayed`, transients,
  cache keys) updated too.
- Import/export changed → all read and write paths stay consistent.

**Observable behavior:** third-party plugins depend on hook timing, filter return shapes, cache
header presence and admin notice order. Treat any change to them as a potential breaking change.

**Tests:** new logic is covered in `tests/Unit/` and/or `tests/Integration/`; integration tests carry
`@group FeatureName`.

## IMPLEMENTER configuration

Single implementer for PHP and JS/CSS. Commit format and permissions: §5.1.

**Common pitfalls:**
- ❌ Running PHP tests or lint with a local PHP binary → ✅ run them in the wp-env container (see
  Project Configuration).
- ❌ Running the full test matrix when the spec doesn't name tests → ✅ run the targeted unit and
  integration tests for the changed code; CI runs the full matrix.
- ❌ jQuery, inline event handlers or `innerHTML` in admin JS → ✅ vanilla DOM APIs, event delegation,
  data passed with `wp_localize_script()`.
- ❌ `apply_filters()`, `get_option( 'wp_rocket_settings' )`, raw constants → ✅
  `wpm_apply_filters_typed()`, injected `Options_Data`, `rocket_get_constant()`.
- ❌ Invoking the bare `docs` or `dod` skill (`anthropic-skills:docs` also exists) → ✅ use the fully
  qualified `gas-delivery-pipeline-templates:docs` and `gas-delivery-pipeline-templates:dod`.

## Documentation conventions

**Docs location:** none. WP Rocket has no developer `docs/` directory.

**Public API surface:** hooks (`do_action`, `wpm_apply_filters_typed`), AJAX actions, REST routes
(`wp-rocket/v1`), WP-CLI commands, option keys, custom capabilities, ServiceProvider-bound public
services, BerlinDB tables, and plugin metadata.

- Document a new filter in the docblock above its `wpm_apply_filters_typed()` call (`@param` and
  `@return` with the typed value). That docblock is the reference.
- Add a new capability to the capability map in WordPress Project Configuration and to
  `phpcs.xml`.
- BerlinDB changes: bump `$version` (`YYYYMMDD`) and declare the upgrade in `$upgrades`.
- Skip `inc/Dependencies/` (vendored).

## Definition of Done

- `composer run-stan` passes, including the four custom rules (§2.2).
- `composer phpcs-changed` passes.
- The PR body follows `.claude/skills/orchestrator/refs/pr-template.md` with headings copied
  exactly. The `PR Template Checker` CI (`wp-media/pr-checklist-action`) fails otherwise. Ticking
  the Chore or Release type skips most of its checks, and filling "Unticked items justification"
  opts out of the checkbox check.
