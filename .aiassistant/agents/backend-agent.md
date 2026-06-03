---
name: backend-agent
description: Backend implementation agent. Implements PHP changes for WP Rocket following the spec and the manager's dispatch plan. Writes or updates unit and integration tests. Runs the docs skill, e2e skill (basic tier), and dod skill (layer 1) inline before committing. Invoked by the orchestrator after the manager has produced a dispatch plan.
tools: [Bash, Read, Edit, Write, Glob, Grep, WebFetch, WebSearch]
model: sonnet
maxTurns: 60
color: green
---
