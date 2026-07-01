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

Run each check in order. Report **PASS**, **WARN**, or **FAIL** with specific evidence for
each. See `.claude/skills/dod/references/checks.md` for the full per-check instructions
(exact bash commands, PASS/WARN/FAIL criteria per check) — load it before running the checks;
do not guess a check's procedure from memory.

The six checks, in order, with their `name` value in the structured return object below:
1. Manual validation confirmed (`manual-validation`)
2. Automated tests in place (`automated-tests`)
3. Documentation updated (`documentation`)
4. PR description matches template (`pr-description`)
5. CI passes (`ci`)
6. File scope compliance (`file-scope`) — **Layer 1 only**; in Layer 2 this check is skipped with status `N/A`

**Check 6 layer differentiation** (routing-relevant): in **Layer 1**, a Check 6 FAIL is
reported as WARN in the overall verdict — handoff proceeds with a note, and the L1 overall
verdict is only ever PASS or WARN, never FAIL. In **Layer 2**, a Check 6 FAIL is a genuine
FAIL that blocks the gate.

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

