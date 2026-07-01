---
name: groom
description: Groom a single GitHub issue — produce an implementation spec and optionally post the grooming summary as a GitHub comment. Standalone entry point for the grooming-agent.
argument-hint: <issue-number>
---

# Groom

Standalone grooming for a single issue. Runs the full grooming analysis and writes the spec
to disk. Posting to GitHub is your choice — you are prompted at the end.

## Step 1 — Config

This skill targets `wp-media/wp-rocket`. The issue and spec files live at the wp-rocket
temp convention:
- Issue file: `.ai/issues/<N>/issue.md`
- Spec file: `.ai/issues/<N>/spec.md`

## Step 2 — Sync the issue

Ensure the issue file exists at `.ai/issues/<N>/issue.md`. Run:

```bash
bash .claude/skills/orchestrator/scripts/issue-sync.sh <N>
```

If the file already exists and is recent (less than 5 minutes old), skip the sync.

## Step 3 — Invoke the grooming agent

Invoke the `grooming-agent` sub-agent with the following invocation context:

> Issue number: `<N>`
> Issue file path: `.ai/issues/<N>/issue.md`
> Repo: `wp-media/wp-rocket`
> complexity_signal: derive from the issue content yourself
>
> **STANDALONE MODE** — two differences from the normal pipeline run:
> 1. **Skip Step 5 (posting to GitHub).** Instead, include the full comment body you would
>    have posted in a section titled `## Grooming Comment Draft` at the end of your response.
>    Use exactly the same format the pipeline would post (including the
>    `<!-- ai-pipeline:grooming-plan -->` marker), so it is ready to post as-is.
> 2. **Skip the StructuredOutput JSON return.** Return a short human-readable summary instead:
>    effort, risk, complexity, and any open questions.

The spec file is still written to `.ai/issues/<N>/spec.md` as normal.

## Step 4 — Offer to post

After the agent responds, display its `## Grooming Comment Draft` to the user, then ask:

> **Post this as a comment on issue #\<N\>?**
> Reply `yes` to post as-is, `no` to finish without posting, or paste edited text to post a
> modified version.

**If yes** — check for an existing grooming comment first (dedup):

```bash
EXISTING_ID=$(gh api repos/wp-media/wp-rocket/issues/<N>/comments \
  --jq '[.[] | select(.body | contains("<!-- ai-pipeline:grooming-plan -->"))] | last | .id // empty')
```

Update with `gh api --method PATCH repos/wp-media/wp-rocket/issues/comments/$EXISTING_ID` if found, otherwise post a new comment.

**If no** — confirm the spec was written to `.ai/issues/<N>/spec.md` and finish.

**If edited** — use the user-provided text as the comment body and post.
