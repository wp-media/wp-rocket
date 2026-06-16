---
name: dod
description: >
  Run the Definition of Done checklist for the current wp-rocket branch and report
  PASS/WARN/FAIL with evidence. Two modes: layer 1 (self-correction inside backend-agent /
  frontend-agent — resolves FAILs before handoff) and layer 2 (independent orchestrator
  gate — fresh perspective after handoff). Pass layer: "1" or layer: "2" when invoking.
---

# DOD SKILL

You are a quality gate checker. Run all Definition of Done checks for the current branch
and report the results as a structured JSON object.

## Two-layer operation

**Layer 1 (implementation agent self-correction):**
Invoked inside `backend-agent` or `frontend-agent` as step 3 of their internal sequence.
If any check returns `FAIL`, the agent self-corrects and re-runs before handing off.
`overall` can only be `PASS` or `WARN` when the agent hands off — FAILs must be resolved.

**Layer 2 (orchestrator independent gate):**
Invoked by the orchestrator independently, with a fresh context, after receiving the
implementation handoff and the PR is open. Provides an unbiased second opinion. Can return
`FAIL`. Produces `layer1_delta` — issues found in L2 that L1 did not catch.

---

## Inputs

| Parameter | Type | Description |
|---|---|---|
| `layer` | `"1"` or `"2"` | Which gate to run. Layer 1 = self-correction inside implementation agent; Layer 2 = independent orchestrator gate. |
| `file_scope` | array of file paths (optional) | Files declared in-scope by the orchestrator for this issue. Used by Check 6 (Layer 1 only). Omit or pass `null` for Layer 2. |
| `base_branch` | string (optional) | The PR base branch. Defaults to `develop`. Used in all `git diff` commands. |
| `pr_url` | string (optional) | The GitHub PR URL. Required for Layer 2 (Check 1, Check 4, Check 5). |

---

## Anti-rationalization table

Before running the checks, acknowledge these. Agents are good at producing plausible reasons to skip steps — this table preempts them.

| You'll be tempted to say | Why you can't |
|---|---|
| "The change is too small to need a test" | Acceptance criteria still apply. A one-line fix to a Subscriber still needs a test on that Subscriber. |
| "Tests pass, DOD L1 is fine" | Passing tests are evidence, not proof. L1 self-reports; L2 is the independent read. |
| "No public API change, skipping docs" | Check for hook additions, `option_keys`, REST routes. Those count as public API. |
| "I'll skip e2e because the environment might not boot" | Boot it. If it fails, `SKIP` is a valid status — but you must attempt it first. |
| "The PR description section is present" | Present is not the same as filled. Thin is a WARN — name it explicitly. |
| "I'll add tests in a follow-up ticket" | "Later" is the load-bearing word. There is no later. See Check 2. |

---

## Base branch guard

Before running any check, determine the PR base branch. All `git diff` commands below assume `origin/develop`, but if the PR targets a different base this silently compares the wrong tree.

```bash
BASE=$(gh pr view "$PR_URL" --json baseRefName --jq .baseRefName 2>/dev/null)
if [ "$BASE" != "develop" ]; then
  echo "WARNING: Base branch is '$BASE', not 'develop'. Adjust git diff commands accordingly."
fi
```

Use `origin/$BASE` in place of `origin/develop` in every diff command throughout this skill. If `$BASE` is empty (Layer 1, no PR yet), default to `develop`.

---

## The 6 checks

Run each check in order. Report **PASS**, **WARN**, or **FAIL** with specific evidence for each.

---

### Check 1 — Manual validation confirmed

Look at the PR/MR description:
- In Layer 1: read the local draft at `.TemporaryItems/Issues/wp-rocket/pull/<N>.md`
- In Layer 2: fetch from GitHub: `gh pr view <PR_NUMBER> --json body -q .body`

Look at the "What was tested" section. It must contain **concrete scenarios** — not "N/A", not "tested locally".

If manual testing appears insufficient, consider invoking the `qa-engineer` agent: it is
designed to independently test a PR and share feedback.

- **PASS**: Section describes specific manual steps taken and their outcome
- **WARN**: Section is present but thin (e.g. only one scenario for a complex change)
- **FAIL**: Section is empty, says "N/A" without justification, or no PR draft exists at all (Layer 1 only — in Layer 2 this is FAIL since the PR is open)

---

### Check 2 — Automated tests in place

Identify changed source files:
```bash
git diff origin/$BASE --name-only
```

For each changed PHP source file in `inc/` or `src/`, check that a corresponding test file
exists. Test files mirror the source structure:
`inc/Engine/Foo/Bar.php` → `tests/Unit/inc/Engine/Foo/Bar/methodName.php`.

Then run the test suite:
```bash
composer test-unit
# For feature-specific integration tests:
vendor/bin/phpunit --configuration tests/Integration/phpunit.xml.dist --group FeatureName
```

`FeatureName` is the group tag matching the changed feature — the same tag Check 2 uses to scope tests. This runs only the feature-relevant integration tests, not the full integration suite.

- **PASS**: All changed PHP source files have tests AND tests pass
- **WARN**: A changed file has no corresponding test. When reporting this, you MUST include an explicit written statement in `evidence`: the filename, the reason a test does not exist (not "too small" or "follow-up ticket" — those are rationalizations), and whether the missing test represents a real gap. "Later" is the load-bearing word — there is no later. If the only honest reason is "I didn't write it", that is a FAIL, not a WARN.
- **FAIL**: Tests fail or error out, OR the agent's stated reason for a missing test is "I'll do it in a follow-up"

---

### Check 3 — Documentation updated

Run `git diff origin/$BASE --name-only` and look for changes to the public API surface:
- New or changed WordPress hooks (`apply_filters`, `do_action`, `wpm_apply_filters_typed`)
- New or changed AJAX actions or REST routes
- New or changed WP-CLI commands
- New or changed configuration keys, option names, or capabilities
- New or changed plugin metadata
- New or changed exported public methods on ServiceProvider-bound services

WP Rocket has no `docs/` directory or `README.md` that serves as public API documentation, so there is no single file path to diff. Instead, evaluate the diff itself: if it introduces new public API surface (new hooks, filters, REST endpoints, WP-CLI commands), note that documentation must be updated and mark **WARN** — without requiring any specific file to have changed.

- **PASS**: No new public API surface introduced, or the change is internal-only
- **WARN**: The diff introduces new public API surface (hooks, filters, REST endpoints, WP-CLI commands) — note that documentation must be updated for it
- **FAIL**: Multiple new public-facing API additions with no acknowledgement that documentation is required

---

### Check 4 — PR description matches template

Read the repo's PR template:
```bash
cat .aiassistant/skills/issue-workflow/refs/pr-template.md
```

Then fetch the PR body:
- Layer 1: read `.TemporaryItems/Issues/wp-rocket/pull/<N>.md`
- Layer 2: `gh pr view <PR_NUMBER> --json body -q .body`

Check that all required sections from the template are present and non-empty:
- Description (with `Fixes #N`)
- Type of change (one checkbox ticked)
- Detailed scenario → What was tested
- Detailed scenario → How to test
- Detailed scenario → Affected Features & Quality Assurance Scope
- Technical description → Documentation
- Technical description → New dependencies
- Technical description → Risks
- Mandatory Checklist → Code validation
- Mandatory Checklist → Code style
- Additional Checks

- **PASS**: All required sections present and filled
- **WARN**: One section is thin or partially filled
- **FAIL**: PR not created yet (Layer 2 only), or 2+ sections missing / left with placeholder text

---

### Check 5 — CI passes

**Layer 1 (no PR yet — local CI commands):**
```bash
composer phpcs-changed   # fast check on changed files
composer run-stan        # PHPStan including the 4 wp-rocket custom rules
composer test-unit       # full unit suite
```

If `phpcs-changed` reports violations, auto-fix then re-check in two calls — never run phpcs/phpcbf file-by-file:
```bash
composer phpcs:fix       # phpcbf auto-fix on all files
composer phpcs-changed   # confirm 0 remaining violations
```

**Layer 2 (PR exists — remote CI status):**

First, read the GitHub Actions workflow files to enumerate which checks are expected:
```bash
ls .github/workflows/
```
Note the check names (e.g. `lint / PHP CodeSniffer`, `lint / PHPStan`, `task-check`).

Wait for all checks to complete, then report any failures:
```bash
# Wait for all checks to complete
gh pr checks "$PR_URL" --watch

# Then report any failures
gh pr checks "$PR_URL" --json name,state,link \
  --jq '.[] | select(.state == "FAILURE") | {name, link}'
```

`gh pr checks --watch` blocks until all checks complete, so no manual polling loop is needed. State values from the JSON API are uppercase: `SUCCESS`, `FAILURE`, `CANCELLED`.

For any check with state `FAILURE`, fetch the run ID and extract the relevant error excerpt:
```bash
# Get the run ID and log link for the failing check
gh pr checks "$PR_URL" --json name,state,link
# Fetch last ~30 lines of the failing job log
gh run view <run_id> --log-failed 2>/dev/null | tail -30
```

Include each failure as a separate blocker in the return JSON with:
- `check`: the check name
- `error_excerpt`: the relevant log lines
- `suggested_fix`: one sentence on what likely caused it

Also verify the `Co-Authored-By: Claude` trailer is present on every commit on the branch:
```bash
git log <base_branch>..HEAD --format="%H %s" | while read sha msg; do
  git show $sha --format="%b" -s | grep -q "Co-Authored-By: Claude" \
    || echo "MISSING Co-Authored-By on $sha"
done
```

- **PASS**: All checks green AND trailer present on every commit
- **WARN**: A non-blocking check (e.g. coverage threshold) is failing
- **FAIL**: Any required check is failing, or any commit is missing the trailer

---

### Check 6 — File scope compliance

**Layer 1 only** (in Layer 2, file scope is not tracked — this check is skipped with status `N/A`).

The orchestrator passes `file_scope` (array of paths) in the dispatch inputs. The skill compares it against `git diff <base_branch>..HEAD --name-only`.

List every file changed on the branch:
```bash
git diff <base_branch>..HEAD --name-only
```

Compare against the `file_scope` input. Flag any file that appears in the diff but not in `file_scope`.

Exceptions that do not count as violations:
- Auto-generated files (`*.min.js`, `*.min.css`, lock files)
- Files in `tests/` that directly correspond to a changed source file (mirrored test files)
- Files the orchestrator explicitly added to scope via a `blocked_reason` note
- Files modified solely by `composer phpcs:fix` (the phpcbf auto-formatter). phpcbf has no "changed files only" mode, so it may reformat files outside scope. Note which files were auto-fixed and exclude them from the scope-violation count.

- **PASS**: All modified files are within declared scope (or no scope was declared)
- **WARN**: One or more files outside scope were modified — name them and explain why
- **FAIL**: Two or more files outside scope were modified without explanation

---

## Output format constraints

Apply these constraints strictly for both Layer 1 and Layer 2 reports:

**Length targets:**
- Total report: aim for ≤ 400 words (excluding JSON). If you exceed this, cut PASS summaries first.
- `evidence` field: one sentence maximum per check. State the finding, not the process ("3 unit tests cover the changed method" not "I ran phpunit and reviewed the output and found that there are three test cases…").
- Do NOT repeat the check criteria in the evidence — the reader knows the criteria.

**What to omit:**
- PASS checks with no nuance: "tests pass" → replace with a one-line table row.
- Commands you ran: never narrate "I ran `composer phpcs-changed` and saw…" — state only what you found.
- Justifications for doing the check: skip the preamble, go straight to the verdict.

**Condensed PASS format:** For checks that simply pass with no nuance, use a one-liner in a summary table instead of a prose paragraph:

| Check | Result | Note |
|---|---|---|
| 1. Acceptance criteria | ✅ PASS | All 3 AC covered by spec |
| 3. Docs | ✅ PASS | No public API change |
| 4. PHPCS | ✅ PASS | 0 violations |

Reserve prose evidence for: WARN, FAIL, and PASS-with-caveats checks only.

**What must always appear:**
- The overall verdict (PASS / WARN / FAIL) as the first line
- Any WARN or FAIL check with its evidence and a concrete remediation step
- The JSON result block (required for orchestrator integration)

```
| Check | Status | Evidence |
|-------|--------|----------|
| 1. Manual validation  | PASS | "What was tested" covers 3 concrete scenarios |
| 2. Automated tests    | WARN | inc/Engine/Foo/Bar.php has no test file |
| 3. Documentation      | PASS | docs/api.md updated |
| 4. PR description     | PASS | All sections filled |
| 5. CI                 | FAIL | run-stan failing: DiscourageApplyFilters in inc/Engine/Cache/Subscriber.php:142 |
| 6. File scope         | PASS | All 4 changed files within declared scope |

Overall: FAIL

Blockers:
- Check 5: PHPStan failing on inc/Engine/Cache/Subscriber.php:142 — replace apply_filters() with wpm_apply_filters_typed()

Warnings (non-blocking):
- Check 2: inc/Engine/Foo/Bar.php has no test — consider filing a ticket
```

If all checks pass: print **PASS** clearly.
If any check fails: print **FAIL** and list each blocker with a suggested fix.

---

## Structured return object

Always return this JSON object in addition to the human-readable output above:

```json
{
  "overall": "PASS|WARN|FAIL",
  "checks": [
    { "name": "manual-validation", "status": "PASS|WARN|FAIL", "evidence": "string" },
    { "name": "automated-tests", "status": "PASS|WARN|FAIL", "evidence": "string" },
    { "name": "documentation", "status": "PASS|WARN|FAIL", "evidence": "string" },
    { "name": "pr-description", "status": "PASS|WARN|FAIL", "evidence": "string" },
    { "name": "ci", "status": "PASS|WARN|FAIL", "evidence": "string" },
    { "name": "file-scope", "status": "PASS|WARN|FAIL|N/A", "evidence": "string" }
  ],
  "blockers": [
    {
      "check": "ci|manual-validation|pr-description",
      "description": "Check 5: PHPStan failing — DiscourageApplyFilters in inc/Engine/Cache/Subscriber.php:142",
      "error_excerpt": "relevant log lines for CI failures — empty string for non-CI blockers",
      "suggested_fix": "replace apply_filters() with wpm_apply_filters_typed() — empty string if unknown"
    }
  ],
  "warnings": ["Check 2: inc/Engine/Foo/Bar.php has no test file"],
  "layer1_delta": ["Issues found in L2 that L1 did not catch — populated by orchestrator in layer 2 only"]
}
```

**Layer 1:** `overall` must be `PASS` or `WARN` when the implementation agent hands off.
**Layer 2:** `overall` can be `PASS`, `WARN`, or `FAIL`. Populate `layer1_delta` with
any issues that were not flagged in layer 1.


---

## wp-rocket-specific notes

- Base branch defaults to `origin/develop`. If the issue branched off something else (e.g. `origin/feature/mcp`), the orchestrator passes the right base.
- PHPStan must pass the four custom rules: `DiscourageApplyFilters`, `DiscourageWPOptionUsage`, `EnsureCallbackMethodsExistsInSubscribedEvents`, `NoHooksInORM`. These are part of `composer run-stan`.
- The "public API surface" for Check 3 includes WordPress hooks and capabilities defined in the `wordpress-compliance` skill.
- The `Co-Authored-By` trailer uses the model-versioned form: `<MODEL> <noreply@anthropic.com>`. Match exactly.

