---
name: qa
description: Run QA validation on a pull request — boots the local environment, tests acceptance criteria, and optionally posts the report as a PR comment. Standalone entry point for the qa-engineer agent.
argument-hint: <PR-number-or-URL>
---

# QA

Standalone QA run for any PR. Boots the local environment, validates every acceptance
criterion, and produces a test report. Posting to GitHub is your choice — you are prompted
at the end.

## Step 1 — Config

This skill targets `wp-media/wp-rocket`. Specs live at `.TemporaryItems/Issues/wp-rocket/issues/<N>-spec.md`.

## Step 2 — Resolve the PR

Use `$ARGUMENTS` as the PR number or URL. If empty, resolve from the current branch:

```bash
gh pr list --head "$(git branch --show-current)" --json number,url -q '.[0] | "\(.number) \(.url)"'
```

If no PR is found, tell the user and stop.

Get the base branch:
```bash
gh pr view <PR_NUMBER> --json baseRefName -q .baseRefName
```

## Step 3 — Invoke the qa-engineer agent

Invoke the `qa-engineer` sub-agent with:
- PR number and PR URL
- Base branch from Step 2
- Repo: `wp-media/wp-rocket`

> **STANDALONE MODE** — two differences from the normal pipeline run:
> 1. **Skip the PR-comment posting step.** Instead, output the full QA report as
>    formatted Markdown in your response, in a section titled `## QA Report`. Use the same
>    format the pipeline would post (including the `<!-- ai-pipeline:qa-report -->` marker).
> 2. **Skip the StructuredOutput JSON return.** Output a short human-readable summary
>    instead: overall result, pass/fail per criterion, and any blockers.

All other steps run normally — the local environment is booted, acceptance
criteria are tested, and the full validation is performed.

## Step 4 — Offer to post

After the agent responds, display its `## QA Report` and ask:

> **Post this QA report to PR #\<PR_NUMBER\>?**
> Reply `yes` to post, `no` to finish here.

**If yes** — post with dedup: check for an existing `<!-- ai-pipeline:qa-report -->` comment,
update it with `gh api --method PATCH repos/wp-media/wp-rocket/issues/comments/$EXISTING_ID` if found, otherwise create a new comment.

**If no** — confirm the QA run is complete and finish.
