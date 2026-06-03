---
name: issue-workflow
description: Work on a GitHub issue by number for wp-media/wp-rocket. Fetches the issue and hands control to the orchestrator skill (running inline in this conversation), which manages grooming, spec review, implementation, lead review, CI, and QA end-to-end.
---

# Issue Workflow

Repository: `wp-media/wp-rocket`

When the user asks to work on an issue by number, such as:
- `/task 123`
- `issue 123`
- `#123`

follow this workflow. The orchestrator runs **inline in this conversation** — read the
user's opening message before kicking it off, since it uses that for escalation
calibration (high autonomy / standard / high oversight).

## Tooling

Use shell commands as the primary approach. The GitHub MCP (`mcp_github_*`) may be used if it is connected — but shell is always the safe fallback and is preferred for reliability.

| Operation | Shell (primary) | GitHub MCP (if connected) |
|---|---|---|
| Issue fetch | `bash .aiassistant/skills/issue-workflow/scripts/issue-sync.sh <N>` | `mcp_github_github_issue_read` |
| Branch creation | `bash .aiassistant/skills/issue-workflow/scripts/make-issue-branch.sh` | — |
| Staging & committing | `git add` / `git commit` | — |
| Pushing | `git push -u origin <branch>` | — |
| PR creation | `gh pr create` | `mcp_github_github_create_pull_request` |
| CI monitoring | `gh pr checks <PR#>` | `mcp_github_github_pull_request_read` |

## Steps

1. **Extract** the issue number from the user's message.

2. **Fetch the issue** — run `bash .aiassistant/skills/issue-workflow/scripts/issue-sync.sh <N>` (or use the MCP equivalent). Read the resulting file at `.TemporaryItems/Issues/wp-rocket/issues/<N>.md`.

3. **Check for parent epics** — if `Parent Epic (GitHub)` or `Parent Epics (Task List)` has entries, sync each parent with `issue-sync.sh <epic-N>` and read those files for context.

4. **Check if this is an Epic** — if the issue has label `epics`, Issue Type `EPIC`, or has sub-issues listed, ask the user: "Work the epic as a whole, or a specific sub-issue?" If a sub-issue is chosen, sync it and proceed with the epic context in mind.

5. **Determine base branch** — default is `origin/develop` unless the user specified otherwise.

6. **Invoke the `orchestrator` skill inline** (do not spawn it as a sub-agent — it runs in this conversation context so it can read the user's intent for escalation calibration):
   > Inputs: issue number `N`, issue file `.TemporaryItems/Issues/wp-rocket/issues/<N>.md`, base branch

The orchestrator skill manages everything from here: calibration → grooming → spec review → dispatch → implementation → lead review → push & PR → CI → QA → finalize. It spawns the specialist agents (`grooming-agent`, `challenger`, `backend-agent`, `frontend-agent`, `release-agent`, `lead-reviewer`, `qa-engineer`, `ticket-writer`) as isolated sub-agents, but the orchestrator itself stays inline so it can surface decisions back to the user naturally.

Monitor progress at `.TemporaryItems/Issues/wp-rocket/issue-<N>-workflow-log.html`.
