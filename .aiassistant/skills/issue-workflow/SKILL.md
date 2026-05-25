---
name: issue-workflow
description: Work on a GitHub issue by number for wp-media/wp-rocket. Fetches the issue and hands control to the orchestrator agent, which manages grooming, spec review, implementation, lead review, CI, and QA end-to-end.
---

# Issue Workflow

Repository: `wp-media/wp-rocket`

When the user asks to work on an issue by number, such as:
- `/task 123`
- `issue 123`
- `#123`

follow this workflow:

## Tooling — Prefer MCPs, Fall Back to Shell

| Operation | Preferred (MCP) | Fallback |
|---|---|---|
| Issue fetch | `mcp_github_github_issue_read` | `issue-sync.sh <N>` → read `.TemporaryItems/…/<N>.md` |
| Branch creation | `mcp_gitkraken_git_branch` + `mcp_gitkraken_git_checkout` | `make-issue-branch.sh` |
| Staging & committing | `mcp_gitkraken_git_add_or_commit` | `git add` / `git commit` |
| Pushing | `mcp_gitkraken_git_push` | `git push` |
| PR creation | `mcp_github_github_create_pull_request` | `gh pr create` |
| CI monitoring | `mcp_github_github_pull_request_read` (method: `get_check_runs`) | Ask user to check GitHub Actions |

## Steps

1. **Extract** the issue number from the user's message.

2. **Fetch the issue** — run `bash .aiassistant/skills/issue-workflow/scripts/issue-sync.sh <N>` (or use the MCP equivalent). Read the resulting file at `.TemporaryItems/Issues/wp-rocket/issues/<N>.md`.

3. **Check for parent epics** — if `Parent Epic (GitHub)` or `Parent Epics (Task List)` has entries, sync each parent with `issue-sync.sh <epic-N>` and read those files for context.

4. **Check if this is an Epic** — if the issue has label `epics`, Issue Type `EPIC`, or has sub-issues listed, ask the user: "Work the epic as a whole, or a specific sub-issue?" If a sub-issue is chosen, sync it and proceed with the epic context in mind.

5. **Determine base branch** — default is `origin/develop` unless the user specified otherwise.

6. **Spawn the `orchestrator` agent**:
   > Inputs: issue number `N`, issue file `.TemporaryItems/Issues/wp-rocket/issues/<N>.md`, base branch

The orchestrator manages everything from here: grooming → spec review → dispatch → implementation → lead review → push & PR → CI → QA → finalize.

Monitor progress at `.TemporaryItems/Issues/wp-rocket/issue-<N>-workflow-log.html`.
