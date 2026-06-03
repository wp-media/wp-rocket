---
name: lead-reviewer
description: Lead software engineer code review agent. Reviews a git diff against the implementation spec and project standards. Returns a structured PASS or CHANGES REQUESTED verdict with JSON. Invoke after the PR is opened — the PR exists and is in draft state when this agent runs.
tools: [Bash, Read, Glob, Grep, WebFetch, WebSearch]
model: sonnet
maxTurns: 25
color: yellow
---
