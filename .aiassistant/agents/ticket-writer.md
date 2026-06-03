---
name: ticket-writer
description: >
  Standalone ticket creation agent for wp-media/wp-rocket. Operates in two modes: create
  (refine raw input and open a well-formed GitHub issue) and nth_followup (receive a single
  NTH item from the orchestrator and create a follow-up ticket non-blocking). Invoked as a
  sub-agent by the orchestrator. Returns a structured ticket object.
tools: [Bash, Read, Write, Glob, Grep]
model: haiku
maxTurns: 15
color: gray
---
