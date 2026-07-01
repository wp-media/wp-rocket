---
name: e2e
description: Run a basic E2E behavioral probe — one primary scenario smoke test for the grooming step.
---

# E2E SKILL — Basic tier

## Config

This skill targets `wp-media/wp-rocket`:
- `E2E_URL` = `http://localhost:8888`
- `E2E_BOOT` = `bash bin/dev-up.sh`
- `E2E_SETTINGS` = `http://localhost:8888/wp-admin/options-general.php?page=wprocket`

The local WordPress environment is at `http://localhost:8888` (admin / password). Boot
or restart it with `bash bin/dev-up.sh`.

This skill provides a single behavioral probe using the Playwright MCP and `curl`. WP Rocket
does **not** commit Playwright specs — this skill never writes, commits, or publishes test
artifacts. It exists only to verify one primary scenario quickly.

---

## Basic tier — behavioral probe

**Purpose:** behavioral verification and smoke tests. Fast enough to fit inside a planning
agent's execution window.

**Invoker:**
- `grooming-agent` — verify behavioral assumptions about the current system *before*
  writing the spec. Use to confirm: does the current feature behave as described in the
  issue? What does the current API or AJAX endpoint return for the scenario being changed?

### Anti-rationalization table

| You'll be tempted to say | Why you can't |
|---|---|
| "The environment probably isn't up, I'll skip" | Run `bash bin/dev-up.sh`. It's idempotent. If it fails, log `SKIP` with the reason — do not silently omit the step. |
| "The change is backend-only, no need to smoke it" | The primary happy path must be verified. A backend change with no observable behavior change still needs a confirming assertion. |
| "I already read the code, I know it works" | "Seems right" never closes a task. Run the scenario. |
| "One scenario is too slow for this stage" | Basic tier is exactly one primary scenario. The cost is acceptable. |

### Process

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
     -d 'action=<action>&nonce=...'

   # Cache headers
   curl -sI http://localhost:8888/ | grep -E '(x-cache|cf-cache)'
   ```

   **Browser (settings page, dashboard notices, interactive UI):**
   Use the Playwright MCP directly for the basic-tier smoke:
   ```
   mcp__playwright__navigate({ url: "http://localhost:8888/wp-login.php" })
   # login
   mcp__playwright__fill({ selector: "#user_login", value: "admin" })
   mcp__playwright__fill({ selector: "#user_pass", value: "password" })
   mcp__playwright__click({ selector: "#wp-submit" })
   # primary scenario
   mcp__playwright__navigate({ url: "http://localhost:8888/wp-admin/options-general.php?page=wprocket" })
   mcp__playwright__snapshot({})
   # Inspect snapshot output to confirm expected element/text is present
   ```

   Take at most 1–2 screenshots if helpful, but do not publish them.

3. Report:
   ```json
   {
     "status": "PASS|FAIL|SKIP",
     "scenarios_tested": ["Settings page loads without errors after enabling X option"],
     "details": "Logged in as admin, navigated to the WP Rocket settings page, confirmed no JS console errors and X toggle present"
   }
   ```

   `SKIP`: `bash bin/dev-up.sh` failed or environment unreachable. Record reason. Do not
   block the pipeline.

### Boundaries

- Do: verify the **one primary scenario** from the spec or grooming plan
- Do: probe current-system behavior (grooming-agent only) when an assumption needs verification
- Do not: cover all acceptance criteria — that belongs to the `qa-engineer` + `e2e-qa-tester` path
- Do not: write, commit, or publish Playwright specs — WP Rocket does not commit Playwright specs
- Do not: publish screenshots

---

## Project-specific notes

- The boot script `bash bin/dev-up.sh` is **idempotent**. Always run it before testing — don't pre-check whether the environment is up. Seed data with `bash bin/dev-seed.sh` if needed.
- Admin credentials: `admin` / `password`.
- Settings page URL: `http://localhost:8888/wp-admin/options-general.php?page=wprocket`.
- Plugin activation check:
  ```bash
  wp plugin list --name=wp-rocket
  ```
- The basic tier never writes Playwright spec files and is invoked by grooming-agent only. Implementation agents (backend-agent, frontend-agent) do not invoke the e2e skill — full E2E validation belongs to the qa-engineer + e2e-qa-tester tier.
