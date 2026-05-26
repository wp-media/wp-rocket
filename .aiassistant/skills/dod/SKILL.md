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

## The 5 checks

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
git diff origin/develop --name-only
```
(Substitute the actual base branch if not `origin/develop`.)

For each changed PHP source file in `inc/` or `src/`, check that a corresponding test file
exists. Test files mirror the source structure:
`inc/Engine/Foo/Bar.php` → `tests/Unit/inc/Engine/Foo/Bar/methodName.php`.

Then run the test suite:
```bash
composer test-unit
# For feature-specific integration tests:
vendor/bin/phpunit --configuration tests/Integration/phpunit.xml.dist --group <FeatureName>
```

- **PASS**: All changed PHP source files have tests AND tests pass
- **WARN**: A changed file has no corresponding test (flag it explicitly by filename)
- **FAIL**: Tests fail or error out

---

### Check 3 — Documentation updated

Run `git diff origin/develop --name-only` and look for changes to the public API surface:
- New or changed WordPress hooks (`apply_filters`, `do_action`, `wpm_apply_filters_typed`)
- New or changed AJAX actions or REST routes
- New or changed configuration keys, option names, or capabilities
- New or changed plugin metadata
- New or changed exported public methods on ServiceProvider-bound services

Then check if docs were updated:
```bash
git diff origin/develop -- docs/ README.md
```

- **PASS**: Doc files updated for every public-facing change, or no public API change occurred
- **WARN**: A public API or hook changed with no doc update (flag which file)
- **FAIL**: Multiple public-facing changes with zero documentation updates

---

### Check 4 — PR description matches template

Read the repo's PR template:
```bash
cat .github/PULL_REQUEST_TEMPLATE.md 2>/dev/null
```

(Falls back to `.aiassistant/skills/issue-workflow/refs/pr-template.md` if no GitHub
template exists — same content.)

Then fetch the PR body:
- Layer 1: read `.TemporaryItems/Issues/wp-rocket/pull/<N>.md`
- Layer 2: `gh pr view <PR_NUMBER> --json body -q .body`

Check that all required sections from the template are present and non-empty:
- Description (with `Fixes #N`)
- Type of change (one checkbox ticked)
- What was tested
- How to test
- Affected Features & Quality Assurance Scope
- Technical description
- Mandatory Checklist items

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

**Layer 2 (PR exists — remote CI status):**
Poll GitHub Actions status:
```bash
# Poll until complete or timeout (5 min, 30s polling)
for i in $(seq 1 10); do
  STATUS=$(gh pr checks "$PR_URL" 2>/dev/null)
  echo "$STATUS" | grep -qE "^(pending|in_progress)" || break
  sleep 30
done
gh pr checks "$PR_URL"
```

Also verify the `Co-Authored-By: Claude` trailer is present on every commit on the branch:
```bash
git log origin/develop..HEAD --format="%H %s" | while read sha msg; do
  git show $sha --format="%b" -s | grep -q "Co-Authored-By: Claude" \
    || echo "MISSING Co-Authored-By on $sha"
done
```

- **PASS**: All checks green AND trailer present on every commit
- **WARN**: A non-blocking check (e.g. coverage threshold) is failing
- **FAIL**: Any required check is failing, or any commit is missing the trailer

---

## Output format

```
| Check | Status | Evidence |
|-------|--------|----------|
| 1. Manual validation | PASS | "What was tested" covers 3 concrete scenarios |
| 2. Automated tests   | WARN | inc/Engine/Foo/Bar.php has no test file |
| 3. Documentation     | PASS | docs/api.md updated |
| 4. PR description    | PASS | All sections filled |
| 5. CI                | FAIL | run-stan failing: DiscourageApplyFilters in inc/Engine/Cache/Subscriber.php:142 |

Overall: BLOCKED

Blockers:
- Check 5: PHPStan failing on inc/Engine/Cache/Subscriber.php:142 — replace apply_filters() with wpm_apply_filters_typed()

Warnings (non-blocking):
- Check 2: inc/Engine/Foo/Bar.php has no test — consider filing a ticket
```

If all checks pass: print **READY TO MERGE** clearly.
If blocked: list each blocker with a suggested fix.

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
    { "name": "ci", "status": "PASS|WARN|FAIL", "evidence": "string" }
  ],
  "blockers": ["Check 5: PHPStan failing — DiscourageApplyFilters in inc/Engine/Cache/Subscriber.php:142"],
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
- The `Co-Authored-By` trailer uses the model-versioned form: `Claude Sonnet 4.6 <noreply@anthropic.com>`. Match exactly.
