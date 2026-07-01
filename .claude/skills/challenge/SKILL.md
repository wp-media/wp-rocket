---
name: challenge
description: Adversarially review a grooming spec before implementation starts. Finds hidden risks, unvalidated assumptions, and missing dependencies. Standalone entry point for the challenger agent.
argument-hint: <issue-number>
---

# Challenge

Standalone adversarial spec review. Runs the full challenger analysis on an existing
grooming spec and outputs a verdict (APPROVED / NEEDS_REVISION / BLOCKED) with
MoSCoW-classified findings. Posting to the GitHub issue is your choice — you are
prompted at the end.

## Step 1 — Locate files

This skill targets `wp-media/wp-rocket`. Use `$ARGUMENTS` as the issue number `N`. Verify both files exist:
- Issue file: `.ai/issues/<N>/issue.md`
- Spec file: `.ai/issues/<N>/spec.md`

If the spec does not exist, tell the user: "No spec found for issue #N. Run `/groom <N>`
first to produce one." Stop here.

If only the issue file is missing, sync it:
```bash
bash .claude/skills/orchestrator/scripts/issue-sync.sh <N>
```

## Step 2 — Invoke the challenger agent

Invoke the `challenger` sub-agent with:
- Issue number `N`
- Issue file path: `.ai/issues/<N>/issue.md`
- Spec file path: `.ai/issues/<N>/spec.md`
- `plan_version`: 1 (or detect from the spec's `Plan v<N>` header if present)
- `CURRENT_MODEL`: "standalone"
- `session_learnings`: read Section 13 of `AGENTS.md` if it exists, else pass empty string
- Repo: `wp-media/wp-rocket`

> **STANDALONE MODE** — one difference from the normal pipeline run:
> **Skip the StructuredOutput JSON return.** Output the full human-readable verdict
> (APPROVED / NEEDS_REVISION / BLOCKED) and findings in a section titled
> `## Challenge Report`, using the same format the orchestrator would receive
> (verdict, MoSCoW-classified findings, alternative suggestions).

## Step 3 — Offer to post

After the agent responds, display its `## Challenge Report` and ask:

> **Post this challenge report as a comment on issue #\<N\>?**
> Reply `yes` to post, `no` to finish here.

**If yes** — post with dedup:
```bash
EXISTING_ID=$(gh api repos/wp-media/wp-rocket/issues/<N>/comments \
  --jq '[.[] | select(.body | contains("<!-- ai-pipeline:challenge -->"))] | last | .id // empty')
```
Update with `gh api --method PATCH repos/wp-media/wp-rocket/issues/comments/$EXISTING_ID` if found, otherwise post a new comment. Body always starts with `<!-- ai-pipeline:challenge -->`.

**If no** — finish. Remind the user: if the verdict is NEEDS_REVISION, update the spec at
`.ai/issues/<N>/spec.md` before running `/groom <N>` again or starting implementation.
