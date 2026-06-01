# Orchestration Event Schema

All agents and the orchestrator emit structured events to a unified queue. The log-coordinator reads this queue and transforms events into HTML log entries, Slack messages, email notifications, and other outputs.

## Event Queue

**Path:** `.TemporaryItems/Issues/wp-rocket/issue-<N>/contracts/orchestrator-events.jsonl`

Newline-delimited JSON (JSONL). Agents append one event per line. The log-coordinator polls this file, reads new events (tracking line count), and processes each.

## Event Structure

All events follow this base schema:

```json
{
  "timestamp": "2026-06-01T14:32:15Z",
  "source": "orchestrator|grooming-agent|challenger|backend-agent|frontend-agent|dod-skill|lead-reviewer|qa-engineer|release-agent",
  "type": "routing_decision|agent_start|agent_complete|gate_complete|implementation_complete|escalation|nth_dispatch",
  "issue_id": "N",
  "data": { ... }
}
```

## Event Types

### routing_decision
Orchestrator makes a routing choice.

```json
{
  "type": "routing_decision",
  "step": "3",
  "decision": "skip-challenger",
  "reason": "XS + LOW + HIGH confidence",
  "signals": {
    "effort": "XS",
    "risk_level": "LOW",
    "complexity": "LOW",
    "grooming_confidence": "HIGH"
  }
}
```

### agent_start
Agent begins work (background spawn).

```json
{
  "type": "agent_start",
  "step": "2",
  "agent": "grooming-agent",
  "task_id": "abc123...",
  "inputs": { "issue_id": "42", "base_branch": "origin/develop" }
}
```

### agent_complete
Agent finishes and wrote its result file.

```json
{
  "type": "agent_complete",
  "step": "2",
  "agent": "grooming-agent",
  "verdict": "APPROVED|NEEDS_REVISION|BLOCKED",
  "result_file": ".../contracts/grooming-result.json",
  "summary": "one-line summary"
}
```

### gate_complete
Quality gate (DOD L2, Lead Review, QA) completes.

```json
{
  "type": "gate_complete",
  "step": "7|8|9",
  "gate": "dod-l2|lead-review|qa",
  "overall": "PASS|WARN|FAIL",
  "result_file": ".../contracts/dod-l2-result.json",
  "summary": "PASS — all 6 checks clean"
}
```

### implementation_complete
Backend or frontend implementation finishes.

```json
{
  "type": "implementation_complete",
  "step": "5",
  "domain": "backend|frontend",
  "result_file": ".../contracts/backend-result.json|frontend-result.json",
  "tests_passing": true,
  "dod_l1_overall": "PASS|WARN",
  "commit_sha": "abc123",
  "files_changed": 5
}
```

### escalation
Human decision required; pipeline paused.

```json
{
  "type": "escalation",
  "step": "8",
  "reason": "lead-reviewer CRITICAL blocker — architectural",
  "blocker": "what happened",
  "suggestion": "what to do next"
}
```

### nth_dispatch
Non-blocking follow-up tickets created.

```json
{
  "type": "nth_dispatch",
  "source_agent": "lead-reviewer",
  "items": [
    { "description": "suggestion", "severity": "NICE_TO_HAVE", "ticket_url": "https://..." }
  ]
}
```

## Writing Events

All agents and the orchestrator append events like this:

```bash
cat >> ".TemporaryItems/Issues/wp-rocket/issue-${ISSUE_ID}/contracts/orchestrator-events.jsonl" <<'EOF'
{"timestamp":"2026-06-01T14:32:15Z","source":"orchestrator","type":"routing_decision",...}
EOF
```

Use `>>` (append), never `>` (overwrite). Each event is one line.

Timestamp format: ISO 8601 UTC (e.g., `2026-06-01T14:32:15Z`).
Use the current time when writing the event.

## Atomic writes

To avoid partial writes if multiple processes append simultaneously:

```bash
# Write to a temp file first
cat > ".../temp-event-$$.jsonl" <<'EOF'
{...}
EOF

# Append atomically
cat ".../temp-event-$$.jsonl" >> ".../contracts/orchestrator-events.jsonl"
rm -f ".../temp-event-$$.jsonl"
```

Or use `flock` for a mutex:

```bash
(
  flock 200
  echo '{"..."}' >> ".../orchestrator-events.jsonl"
) 200>".../contracts/.lock"
```

## Log Coordinator Polling

The log-coordinator:
1. Tracks line count read so far (persisted in `.../contracts/.log-coordinator-state`)
2. Polls the event queue file every 5 seconds
3. Reads only new lines (since last line count)
4. Parses each JSON event
5. Transforms to HTML, Slack, email, etc.
6. Updates state file with new line count

This way, even if the orchestrator writes 10 events in a burst, the log-coordinator picks them all up and processes them as they accumulate.

## Future Extensions

Once the event queue is in place, future enhancements are simple:

- **Slack notifications:** log-coordinator reads events, filters by severity, posts to Slack
- **Email summaries:** log-coordinator collects events, sends digest at pipeline end
- **Metrics:** log-coordinator aggregates timing, gate results, pass rates
- **Webhooks:** log-coordinator POSTs events to external services
- **Real-time dashboard:** log-coordinator writes to a WebSocket server

None of these require changes to the orchestrator or implementation agents.
