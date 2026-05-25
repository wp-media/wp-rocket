---
name: issue-workflow
description: Work on a GitHub issue by number for wp-media/wp-rocket. Sync the issue locally, analyze it, create a branch, implement minimal changes, and prepare a PR draft.
---

# Issue Workflow

Repository: `wp-media/wp-rocket`

When the user asks to work on an issue by number, such as:
- `/task 123`
- `issue 123`
- `#123`

follow this workflow:

1. Extract the issue number.
2. Run `.aiassistant/skills/issue-workflow/scripts/issue-sync.sh <issue-number>`.
3. Read `.TemporaryItems/Issues/wp-rocket/issues/<issue-number>.md`.
4. If `Parent Epic (GitHub)` or `Parent Epics (Task List)` has entries, sync each epic with `.aiassistant/skills/issue-workflow/scripts/issue-sync.sh <epic-number>` and read those files for context (this usually means the current issue is a subtask).
5. If the issue looks like an Epic (label `epics`, Issue Type = `EPIC`, Project field `Type` = `EPIC`, or `Sub-issues (GitHub)`/`Sub-issues (Task List)` has entries), ask whether to work the Epic as a whole or a specific sub-issue. If a sub-issue is chosen, run `.aiassistant/skills/issue-workflow/scripts/issue-sync.sh <sub-issue-number>` and proceed with the Epic context in mind.
6. If relationships are unclear or missing (including Issue Type being `unknown` because Issue Types are disabled, or Project `Type` being `unknown` because the issue is not in a Project or access is missing), proceed as a standalone issue unless an Epic signal is present. Only ask for an epic/sub-issue number when at least one explicit Epic signal or parent/sub-issue is detected.
7. **Invoke the `grooming-agent` sub-agent** — pass it the issue number and the path to the synced issue file. It will:
   - Read the issue and any parent epic for context.
   - Map the codebase using the knowledge graph (`.aiassistant/graph/dependency-graph.json`): locate target classes, trace dependencies, identify the responsible ServiceProvider and Subscribers.
   - Surface both a minimal fix and any refactor option where applicable — without deciding between them.
   - Write the implementation spec to `.TemporaryItems/Issues/wp-rocket/issues/<issue-number>-spec.md`.
   - Return the spec path.
8. **Invoke the `manager` sub-agent** — pass it the issue number and the spec path. It will:
   - Read the spec and make the scope decision (minimal fix vs refactor).
   - If the decision is ambiguous, it will ask you directly — answer before proceeding.
   - Determine which domains are affected (backend PHP, frontend JS/CSS, or both).
   - Return a structured dispatch plan.
9. Determine the base branch: default to `origin/develop` unless the user specified a different one (e.g. `origin/feature/mcp`). Determine the branch prefix from the issue type:
   - Bug / defect → `fix`
   - Enhancement / feature → `enhancement`
   - Test → `test`
   - Default → `fix`
   Run `.aiassistant/skills/issue-workflow/scripts/make-issue-branch.sh <issue-number> "<issue-title>" <prefix> <base-branch>`. Keep `<base-branch>` in context — it is passed to `lead-reviewer` and `qa-engineer`.
10. Follow `AGENTS.md`.
11. Based on the manager's dispatch plan, invoke the implementation agents:
    - If backend work is needed: **invoke `backend-agent`** — pass it the issue number, spec path, and dispatch plan.
    - If frontend work is needed: **invoke `frontend-agent`** — pass it the issue number, spec path, and dispatch plan.
    - If both are needed and independent per the dispatch plan: invoke backend first, then frontend.
    - **Maximum 3 attempts per agent.** If an agent still fails verification after 3 attempts, stop and report the remaining issues to the user.
12. Commit atomically after all implementation agents complete: one `git commit` per logical change set using Conventional Commits format. Every commit made by AI must include a `Co-Authored-By` trailer identifying the model that authored it (e.g. `Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>` or `Co-Authored-By: GPT-4o <noreply@openai.com>`). Use your own model name and provider.
13. **Invoke the `lead-reviewer` sub-agent** — pass it the issue number, the spec path, and the base branch. It will:
    - Review the diff against the implementation spec and project standards (architecture, PHP, JS, tests).
    - Return a structured verdict: **PASS** or **CHANGES REQUESTED** with specific blockers.
14. If `lead-reviewer` returns **CHANGES REQUESTED**: address every blocker, re-run PHPCS and static analysis, commit the fixes, then re-invoke `lead-reviewer`.
    **Maximum 3 lead-reviewer attempts.** If still CHANGES REQUESTED after the 3rd attempt, stop and report all remaining blockers to the user.
15. Run `.aiassistant/skills/issue-workflow/scripts/init-pr-draft.sh <issue-number>`.
16. Fill every section of the PR draft at `.TemporaryItems/Issues/wp-rocket/pull/<issue-number>.md`. The file was already initialized from `refs/pr-template.md` by the script in step 15. Complete every section with relevant content — do not skip sections or invent a different structure. Replace all placeholder text with real content. Select exactly ONE `Type of change` checkbox that best describes the change; leave all others unchecked.
17. Run `git push` to publish the branch.
18. Create the GitHub PR using the **exact content of the filled draft** as the PR body. Do not summarise or rewrite it — copy it verbatim. Set as draft if implementation is still in progress.
    - **PR title format**: `Closes #<issue-number>: <short descriptive title>` (use `Fixes` instead of `Closes` only when the issue should auto-close on merge to a non-default branch).
    - After creating the PR, assign it to yourself and apply the **Made by AI** label if it exists in the repository: `gh pr edit <PR_number> --add-assignee @me --add-label "Made by AI"`.
    - If the `Made by AI` label does not exist, skip the label silently — do not create it.
19. **Invoke the `qa-engineer` sub-agent** — pass it the issue number, PR number, and base branch. It will:
    - Read the issue spec and PR diff.
    - Select validation strategies (API, Browser, Analysis) based on what changed.
    - For UI changes, delegate browser validation to the `e2e-qa-tester` sub-agent, which writes temporary Playwright specs, runs them, publishes screenshots via commit-SHA, and removes all temp files.
    - **Post the full QA report as a PR comment** (always, regardless of outcome — PASS, FAIL, or PARTIAL).
    - Return a structured test report (see format in `.aiassistant/agents/qa-engineer.md`).
20. If `qa-engineer` reports **FAIL** or **PARTIAL**: fix the identified blockers, re-commit, re-push, and re-run the agent before continuing.
    **Maximum 3 qa-engineer attempts.** If still FAIL or PARTIAL after the 3rd attempt, stop and report all remaining blockers to the user.
21. If `qa-engineer` reports **READY TO MERGE**:
    1. **Update the PR body** — edit the **"What was tested"** section under `## Detailed scenario` to include the full QA report: strategies used, each acceptance criterion with its validation method and result, and smoke-test outcomes. Use `gh pr edit <PR_number> --body "..."` with the updated body. Also update the local draft at `.TemporaryItems/Issues/wp-rocket/pull/<issue-number>.md` to match.
    2. **Convert the PR from draft to ready-for-review**: `gh pr ready <PR_number>`.
22. Monitor PR CI status checks until all pass. Report any failures with actionable details.

## Tooling — Prefer MCPs, Fall Back to Shell

This workflow uses MCP tools when available. Always prefer them over shell commands.
If an MCP tool is not available in the current session, fall back to the shell equivalent.

### Issue fetch
| Preferred (MCP) | Fallback |
|---|---|
| `mcp_github_github_issue_read` (method: `get`, `get_sub_issues`) | `issue-sync.sh <number>` → read `.TemporaryItems/…/<number>.md` |

### Branch creation
| Preferred (MCP) | Fallback |
|---|---|
| `mcp_gitkraken_git_branch` (action: `create`) + `mcp_gitkraken_git_checkout` | `make-issue-branch.sh <number> "<title>" <prefix>` |

### Staging & committing
| Preferred (MCP) | Fallback |
|---|---|
| `mcp_gitkraken_git_add_or_commit` (action: `add`, then `commit`) | `git add` / `git commit` in terminal |

### Pushing
| Preferred (MCP) | Fallback |
|---|---|
| `mcp_gitkraken_git_push` | `git push` in terminal |

### PR creation
| Preferred (MCP) | Fallback |
|---|---|
| `mcp_github_github_create_pull_request` + `mcp__GitKraken__pull_request_create` (`assign_to_me: true`) | `gh pr create` then `gh pr edit <number> --add-assignee @me` |

### CI monitoring
| Preferred (MCP) | Fallback |
|---|---|
| `github-pull-request_pullRequestStatusChecks` or `mcp_github_github_pull_request_read` (method: `get_check_runs`) | Ask user to check GitHub Actions |

## Git Operations

This skill operates under the **Issue Workflow exception** defined in AGENTS.md §5.1.

You MAY:
1. Run atomic commits — one per logical change set, only after PHPCS + static analysis pass.
2. Push once all commits are ready.
3. Create the GitHub Pull Request using the filled PR draft from `.TemporaryItems/Issues/wp-rocket/pull/<issue-number>.md`.
4. Monitor CI status checks until all pass or a failure is detected and reported.

Commit message format: `type(scope): short description` (Conventional Commits), followed by a `Co-Authored-By` trailer identifying the AI model on every AI-authored commit. Use your own model name and provider.
Do not amend commits that have already been pushed.

## Agent Pipeline

This workflow uses four sub-agents defined in `.aiassistant/agents/`. Each runs in an isolated context and communicates via structured output.

### grooming-agent (issue analyst)

Invoke after fetching the issue (step 7). Provide:
- The issue number
- The path to the synced issue file

```
Invoke sub-agent: grooming-agent
Inputs: issue #<N>, issue file path
```

Produces `.TemporaryItems/Issues/wp-rocket/issues/<N>-spec.md` after mapping the codebase via the knowledge graph. The implementing agent reads this spec before writing any code.

### lead-reviewer (code reviewer)

Invoke after all commits are made (step 16). Provide:
- The issue number
- The spec path
- The base branch

```
Invoke sub-agent: lead-reviewer
Inputs: issue #<N>, spec path, base branch
```

Returns **PASS** or **CHANGES REQUESTED** with specific blockers. Loop until PASS before proceeding to push.

### qa-engineer (QA orchestrator)

Invoke after the PR is created (step 22). Provide:
- The issue number (for acceptance criteria)
- The PR number (for diff and "How to test" section)
- The base branch

```
Invoke sub-agent: qa-engineer
Inputs: issue #<N>, PR #<M>, base branch
```

The agent selects strategies automatically:
- **API/functional** — if backend logic changed (AJAX, hooks, WP-CLI, caching logic, data processing)
- **Browser/UI** — if admin UI changed; delegates to `e2e-qa-tester`
- **Analysis fallback** — if local environment is unavailable

### e2e-qa-tester (browser specialist)

Invoked by `qa-engineer` automatically for UI changes. Can also be invoked directly:

```
Invoke sub-agent: e2e-qa-tester
Inputs: acceptance criteria, changed frontend files, PR number
```

It will:
1. Walk through the admin UI flows using Playwright MCP
2. Write temporary Playwright specs under `.e2e-temp/`
3. Run those specs against the local environment (`npx playwright test .e2e-temp/`)
4. Capture screenshots and publish them via the commit-SHA method
5. Remove all temp files (`.e2e-temp/` and `.e2e-screenshots/`)
6. Return per-criterion results and permanent screenshot URLs

Note: WP Rocket's permanent E2E suite lives in an external repository. All `.e2e-temp/` files are for QA validation only and are never committed permanently.

### Decision tree

```
Issue fetched
  └─ invoke grooming-agent → maps knowledge graph → writes <N>-spec.md

Spec ready
  └─ implement following spec
  └─ invoke lead-reviewer
       ├─ PASS             → push → open PR
       └─ CHANGES REQUESTED → fix blockers → re-invoke lead-reviewer

PR created
  └─ invoke qa-engineer
       ├─ backend only     → Strategy A (API/WP-CLI)
       ├─ UI touched       → Strategy B → delegate to e2e-qa-tester
       │                        └─ writes .e2e-temp/ specs → runs → screenshots
       │                        └─ publishes screenshots via commit-SHA → cleans up
       │                        └─ returns results + permanent URLs to qa-engineer
       └─ env unavailable  → Strategy C (Analysis)

qa-engineer always posts full report as PR comment (PASS, FAIL, or PARTIAL)
qa-engineer returns READY TO MERGE → update PR body with QA findings → mark PR ready for review
qa-engineer returns FAIL/PARTIAL   → fix blockers → re-run qa-engineer
```

---

## Epic And Sub-Issue Sync
The sync script auto-downloads parent epics and sub-issues into
`.TemporaryItems/Issues/wp-rocket/issues/`. To skip related sync, set
`WPROCKET_SYNC_RELATED=0` when invoking the script.
