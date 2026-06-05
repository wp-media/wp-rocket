---
name: e2e
description: >
  Shared end-to-end test execution skill for wp-rocket at two tiers. Basic tier
  (grooming-agent + backend-agent + frontend-agent): behavioral verification and smoke
  tests for the primary happy path. Extended tier (qa-engineer): full acceptance criteria,
  regression, edge cases, screenshot evidence, and temporary spec authoring — extended
  tier is owned by the `e2e-qa-tester` sub-agent. Pass tier: "basic" or tier: "extended"
  when invoking.
---

# E2E SKILL (wp-rocket)

This skill provides end-to-end test execution at two tiers. The tooling is the same
(Playwright MCP + curl + WP-CLI); the difference is scope and depth.

The local WordPress environment is at `http://localhost:8888` (admin / password). Boot
or restart it with `bash bin/dev-up.sh`.

---

## Tier 1 — Basic

**Purpose:** behavioral verification and smoke tests. Fast enough to fit inside a planning
or implementation agent's execution window.

**Invokers:**
- `grooming-agent` — verify behavioral assumptions about the current system *before*
  writing the spec. Use to confirm: does the current feature behave as described in the
  issue? What does the current API or AJAX endpoint return for the scenario being changed?
- `backend-agent` / `frontend-agent` (post-implementation) — confirm the primary happy
  path works with the new code before handing off to lead-reviewer.

### Anti-rationalization table

| You'll be tempted to say | Why you can't |
|---|---|
| "The environment probably isn't up, I'll skip" | Run `bin/dev-up.sh`. It's idempotent. If it fails, log `SKIP` with the reason — do not silently omit the step. |
| "The change is backend-only, no need to smoke it" | The primary happy path must be verified. A backend change with no observable behavior change still needs a confirming assertion. |
| "I already read the code, I know it works" | "Seems right" never closes a task. Run the scenario. |
| "One scenario is too slow for this stage" | Basic tier is exactly one primary scenario. The cost is acceptable. |

### Basic tier process

1. Boot the environment (idempotent — safe to run if already up):
   ```bash
   bash bin/dev-up.sh
   ```
   If the script exits non-zero, set `status: "SKIP"`, note the reason, and do not block
   the pipeline.

2. Run the primary happy path scenario from the spec or grooming plan.

   **Backend / AJAX / REST:**
   ```bash
   # Public REST or AJAX
   curl -s -X POST http://localhost:8888/wp-admin/admin-ajax.php \
     -H "Cookie: $(cat .wp-session-cookie 2>/dev/null)" \
     -d 'action=rocket_<action>&nonce=...'

   # Cache headers
   curl -sI http://localhost:8888/ | grep -E '(x-rocket-|cf-cache|x-cache)'

   # WP-CLI inside the dev environment
   bin/wp <command>
   ```

   **Browser (settings page, dashboard notices, interactive UI):**
   Use the Playwright MCP directly for basic-tier smoke. Do not delegate to
   `e2e-qa-tester` at this tier (that is the extended tier path):
   ```
   mcp__playwright__navigate({ url: "http://localhost:8888/wp-login.php" })
   # login
   mcp__playwright__fill({ selector: "#user_login", value: "admin" })
   mcp__playwright__fill({ selector: "#user_pass", value: "password" })
   mcp__playwright__click({ selector: "#wp-submit" })
   # primary scenario
   mcp__playwright__navigate({ url: "http://localhost:8888/wp-admin/options-general.php?page=wprocket" })
   mcp__playwright__assert_text({ selector: "...", text: "..." })
   ```

   Take at most 1–2 screenshots if helpful, but do not publish them at this tier.

3. Report:
   ```json
   {
     "status": "PASS|FAIL|SKIP",
     "scenarios_tested": ["Settings page loads without errors after enabling X option"],
     "details": "Logged in as admin, navigated to /wp-admin/options-general.php?page=wprocket, confirmed no JS console errors and X toggle present"
   }
   ```

   `SKIP`: `bin/dev-up.sh` failed or environment unreachable. Record reason. Do not block
   the pipeline.

### Basic tier boundaries

- ✅ Do: verify the **one primary scenario** from the spec or grooming plan
- ✅ Do: probe current-system behavior (grooming-agent only) when an assumption needs verification
- 🚫 Do not: cover all acceptance criteria (that is extended tier)
- 🚫 Do not: write or commit Playwright specs (that is extended tier via `e2e-qa-tester`)
- 🚫 Do not: publish screenshots (that is extended tier)

---

## Tier 2 — Extended

**Purpose:** full acceptance criteria coverage, regression testing, edge cases, visual
comparison, and Playwright spec authoring with screenshot evidence.

**Invoker:** `qa-engineer` only.

**Execution:** the qa-engineer agent delegates browser flows to the `e2e-qa-tester`
sub-agent, which handles Playwright MCP driving, temporary spec authoring under
`.e2e-temp/`, screenshot publishing via a public GitHub Gist, and clean-up.

The qa-engineer agent itself handles:
- Strategy A (API / functional validation via curl and WP-CLI)
- Strategy C (test-suite-only fallback when the environment is unreachable)

For details, read:
- `.aiassistant/agents/qa-engineer.md` — strategy selection and report format
- `.aiassistant/agents/e2e-qa-tester.md` — browser flow execution, spec authoring, screenshot publishing

The extended tier writes Playwright specs to `.e2e-temp/` (gitignored, never committed)
and screenshots to `.e2e-screenshots/`. Screenshots are published to a public GitHub Gist
to obtain stable raw URLs (gists are always public, so the URLs never 404 in PR comments),
then the local files are deleted. WP Rocket's permanent E2E suite lives in an external
repository — nothing in `.e2e-temp/` or `.e2e-screenshots/` is ever committed to this repo.

### Video recording

Playwright MCP does not currently expose a video recording API. Playwright's native `page.video()` / `use: { video: 'on' }` config is available when running Playwright directly (not via MCP). Video recording is therefore **not available in the current Playwright MCP integration**.

If video evidence is needed for a specific investigation, note it as a blocker in the QA report and fall back to sequential screenshots at key steps.

Status: **not implemented** — pending Playwright MCP video support or a migration to direct Playwright CLI execution.

### On-demand promotion to permanent e2e suite

Temporary Playwright specs written during QA (in `.e2e-temp/`) are not currently promoted to the permanent e2e suite. The permanent suite lives in an external repository and requires separate process/tooling alignment.

**Status:** not yet implemented. Needs team discussion before a promotion path is designed.

---

## When to use which tier

| Invoker | Tier | Purpose |
|---|---|---|
| `grooming-agent` | Basic | Verify a behavioral assumption before writing the spec |
| `backend-agent` (post-implement) | Basic | Smoke the primary happy path before hand-off |
| `frontend-agent` (post-implement) | Basic | Smoke the primary happy path before hand-off |
| `qa-engineer` | Extended | Full acceptance criteria + regression + screenshots |

---

## wp-rocket-specific notes

- The dev script `bin/dev-up.sh` is **idempotent**. Always run it before testing — don't pre-check whether the environment is up.
- Admin credentials: `admin` / `password`.
- Settings page URL: `http://localhost:8888/wp-admin/options-general.php?page=wprocket`.
- Plugin activation check (no login needed):
  ```bash
  curl -s -o /dev/null -w "%{http_code}" http://localhost:8888/wp-admin/options-general.php?page=wprocket
  ```
- For cache-header tests, send a request to a front-end URL and inspect `X-Rocket-*` headers.
- The basic tier never writes Playwright spec files. If a flow is complex enough to need a deterministic spec, that signals it should go through the extended tier (qa-engineer + e2e-qa-tester).
