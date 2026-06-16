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
- **Integration** — WordPress hooks, database operations, or hook interactions; extend the appropriate base class (`TestCase`, `AdminTestCase`, `AjaxTestCase`).

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

**Exception — Issue Workflow:** When operating under the issue-workflow skill (triggered by `/task <number>`, `issue <number>`, or `#<number>`), the agent MAY:

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

# 9. QA Agent

The `qa-engineer` sub-agent validates PRs automatically after step 19 of the issue workflow.
It reads the PR spec, selects a validation strategy (API / Browser / Analysis), and produces
a structured test report.

Agent definition: `.aiassistant/agents/qa-engineer.md`.

The local WordPress environment at `http://localhost:8888` (admin / password) is used for
browser validation via Playwright MCP. No containerised environment is required.

---

# 10. Skills Activation

The repository defines AI Skills under `/.aiassistant/skills`.

Agents MUST activate the relevant skill depending on the task:

- Template or UI changes → WordPress Compliance Skill
- Structural or architectural changes → WP Rocket Architecture Skill
- Core service modifications → Both skills
- Codebase exploration / dependency tracing → Knowledge Graph Skill

## 10.1 Knowledge Graph

A pre-built dependency graph is available at `.aiassistant/graph/dependency-graph.json`.

Before exploring the codebase structure (finding a class, tracing dependencies, checking
namespace boundaries), **read this file first**. It contains:
- `nodes`: per-file namespace, declared symbols, and imports.
- `symbol_index`: maps every fully-qualified PHP class/interface/trait/enum to its file.

Run `node bin/build-knowledge-graph.js` to refresh after structural changes (`--full` to force rebuild).

---

# 11. Repository Identity

Canonical GitHub repository: `wp-media/wp-rocket`

Unless explicitly instructed otherwise, all GitHub issue, PR, and branch workflows must
assume this repository.

---

# 12. Repository Specs

The repository may define task-specific implementation specs under:

`.aiassistant/specs/`

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
