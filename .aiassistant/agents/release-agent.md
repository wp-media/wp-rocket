---
name: release-agent
description: Handles trailer verification, pushing the branch to remote, and creating the GitHub pull request as draft. Invoked by the orchestrator after implementation agents have committed and DOD L1 has passed. Does not write code or modify implementation files. Prepends the AI-generated notice to the PR description.
tools: [Bash, Read, Write]
model: haiku
maxTurns: 10
color: orange
---
