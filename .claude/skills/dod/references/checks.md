# The 6 checks

Run each check in order. Report **PASS**, **WARN**, or **FAIL** with specific evidence for each.

---

### Check 1 — Manual validation confirmed

Look at the PR/MR description:
- In Layer 1: read the local draft at `.ai/issues/<N>/pull.md`
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
cat .claude/skills/issue-workflow/references/pr-template.md
```

Then fetch the PR body:
- Layer 1: read `.ai/issues/<N>/pull.md`
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
  git show $sha --format="%b" -s | grep -qE "Co-Authored-By: .+ <noreply@anthropic.com>" \
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

**Layer differentiation:**
- **Layer 1:** a Check 6 FAIL is reported as WARN in the overall verdict — handoff proceeds with a note. The L1 overall verdict is only ever PASS or WARN, never FAIL.
- **Layer 2:** a Check 6 FAIL is a genuine FAIL that blocks the gate.
