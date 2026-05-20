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
7. Summarize the issue, feasibility, constraints, and blockers.
8. If a truly blocking ambiguity exists, ask before coding. Otherwise proceed conservatively.
9. **Map the codebase with the knowledge graph before touching any file.**
   Read `.aiassistant/graph/dependency-graph.json`. If `base_commit` ≠ current HEAD, refresh first: `node bin/build-knowledge-graph.js`.
   Answer these questions from the graph — do NOT use grep for class lookups:
   - **Where is the target class?** → `symbol_index["WP_Rocket\\Engine\\...\\ClassName"]`
   - **What does it depend on?** → `nodes[file].imports`
   - **Which ServiceProvider wires it?** → find files whose `imports` contain the target FQN — that file's ServiceProvider registers it
   - **Which Subscribers are in this module?** → filter `nodes` where `symbols[*].implements` includes `Subscriber_Interface` and `namespace` starts with the module prefix
   - **Constructor signature** → read the actual file once located; the graph gives the path, you read the constructor for argument types before modifying `register()` in the ServiceProvider
10. Determine the branch prefix from the issue type:
   - Bug / defect → `fix`
   - Enhancement / feature → `enhancement`
   - Test → `test`
   - Default → `fix`
   Run `.aiassistant/skills/issue-workflow/scripts/make-issue-branch.sh <issue-number> "<issue-title>" <prefix> origin/develop`.
   Always pass `origin/develop` as the fourth argument so the branch is always based on the latest remote, regardless of current working branch or worktree state. Use a different base ref only when the user explicitly requests it.
11. Follow `AGENTS.md`.
12. Activate the relevant skills:
   - `wp-rocket-architecture`
   - `wordpress-compliance`
13. Implement minimal changes following TDD:
   - Write or update tests **alongside** implementation (unit in `tests/Unit/`, integration in `tests/Integration/`).
   - Test files mirror source: `inc/Engine/Foo/Bar.php` → `tests/Unit/inc/Engine/Foo/Bar/methodName.php`.
   - Use `@group FeatureName` on integration tests for targeted runs.
   - New hooks **must** use `wpm_apply_filters_typed()` — never `apply_filters()`.
   - Reading plugin options **must** use the injected `Options_Data` instance — never `get_option()`.
   - All WordPress hooks **must** go through a Subscriber — never `add_action`/`add_filter` directly.
   - Run `composer test-unit` and confirm no regressions.
   - If integration tests exist for the module: `vendor/bin/phpunit --configuration tests/Integration/phpunit.xml.dist --group FeatureName` (use the direct phpunit command rather than `composer test-integration` to avoid conflicts with its default `--exclude-group` list).
14. Run linting and static analysis; fix all new violations before committing:
   - `composer phpcs-changed` first (fast pass on changed files only).
   - `composer phpcs` for a full check.
   - `composer run-stan` — verify all four custom PHPStan rules pass (§2.2 of AGENTS.md).
15. Commit atomically: one `git commit` per logical change set using Conventional Commits format.
16. Run `.aiassistant/skills/issue-workflow/scripts/init-pr-draft.sh <issue-number>`.
17. Fill every section of the PR draft at `.TemporaryItems/Issues/wp-rocket/pull/<issue-number>.md`. The file was already initialized from `refs/pr-template.md` by the script in step 16. Complete every section with relevant content — do not skip sections or invent a different structure. Replace all placeholder text with real content. Tick the appropriate `Type of change` checkbox.
18. Run `git push` to publish the branch.
19. Create the GitHub PR using the **exact content of the filled draft** as the PR body. Do not summarise or rewrite it — copy it verbatim. Set as draft if implementation is still in progress. After creating the PR, assign it to yourself:
    ```bash
    gh pr edit <PR_number> --add-assignee @me
    ```
20. **Invoke the `qa-engineer` sub-agent** — pass it the issue number and PR number. It will:
    - Read the issue spec and PR diff.
    - Select validation strategies (API, Browser, Analysis) based on what changed.
    - For UI changes, delegate browser validation to the `e2e-qa-tester` sub-agent, which writes temporary Playwright specs, runs them, publishes screenshots via commit-SHA, and removes all temp files.
    - **Post the full QA report as a PR comment** (always, regardless of outcome — PASS, FAIL, or PARTIAL).
    - Return a structured test report (see format in `.aiassistant/agents/qa-engineer.md`).
21. If `qa-engineer` reports **FAIL** or **PARTIAL**: fix the identified blockers, re-commit, re-push, and re-run the agent before continuing.
22. If `qa-engineer` reports **READY TO MERGE**:
    1. **Update the PR body** — edit the **"What was tested"** section under `## Detailed scenario` to include the full QA report: strategies used, each acceptance criterion with its validation method and result, and smoke-test outcomes. Use `gh pr edit <PR_number> --body "..."` with the updated body. Also update the local draft at `.TemporaryItems/Issues/wp-rocket/pull/<issue-number>.md` to match.
    2. **Convert the PR from draft to ready-for-review**: `gh pr ready <PR_number>`.
23. Monitor PR CI status checks until all pass. Report any failures with actionable details.

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

Commit message format: `type(scope): short description` (Conventional Commits).
Do not amend commits that have already been pushed.

## QA Pipeline — Sub-Agent Invocation

After the PR is created (step 19), QA runs automatically via two sub-agents defined in `.aiassistant/agents/`.

### qa-engineer (orchestrator)

Invoke after every PR. Provide:
- The issue number (for acceptance criteria)
- The PR number (for diff and "How to test" section)

```
Invoke sub-agent: qa-engineer
Inputs: issue #<N>, PR #<M>
```

The agent selects strategies automatically:
- **API/functional** — if backend logic changed (AJAX, hooks, WP-CLI, caching logic, data processing)
- **Browser/UI** — if admin UI changed; delegates to `e2e-qa-tester`
- **Analysis fallback** — if local environment is unavailable

Always posts the full report as a PR comment regardless of outcome.

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
PR created
  └─ invoke qa-engineer
       ├─ backend only    → Strategy A (API/WP-CLI)
       ├─ UI touched      → Strategy B → delegate to e2e-qa-tester
       │                       └─ writes .e2e-temp/ specs → runs → screenshots
       │                       └─ publishes screenshots via commit-SHA → cleans up
       │                       └─ returns results + permanent URLs to qa-engineer
       └─ env unavailable → Strategy C (Analysis)

qa-engineer always posts full report as PR comment (PASS, FAIL, or PARTIAL)
qa-engineer returns READY TO MERGE → update PR body with QA findings → mark PR ready for review
qa-engineer returns FAIL/PARTIAL   → fix blockers → re-run qa-engineer
```

---

## Epic And Sub-Issue Sync
The sync script auto-downloads parent epics and sub-issues into
`.TemporaryItems/Issues/wp-rocket/issues/`. To skip related sync, set
`WPROCKET_SYNC_RELATED=0` when invoking the script.
