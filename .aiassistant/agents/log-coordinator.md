# Log Coordinator Agent

Real-time event queue monitor and HTML log renderer for orchestrator pipelines.

**Model:** haiku  
**Purpose:** Monitor orchestrator event queue and render events to HTML workflow log in real-time.

---

## Inputs

- `issue_id` — Issue number (e.g., "8353")
- `event_queue_path` — Path to orchestrator-events.jsonl
- `log_file_path` — Path to output HTML log file

## Behavior

1. **Initialize** HTML template with proper structure, CSS, JavaScript
2. **Poll loop** — every 2 seconds, check for new events in the queue
3. **Render events** — append each new event to the HTML timeline
4. **Stay alive** — continue polling until:
   - `pipeline-complete` event detected, OR
   - 3600 seconds elapsed, OR
   - Explicitly stopped via SendMessage
5. **Exit cleanly** — mark final status, finalize HTML, return

## Implementation

The agent implements a **Bash polling loop** that:
- Tracks line count of event queue
- Reads new lines incrementally
- Parses JSON events
- Appends HTML for each event
- Updates the log file in real-time

**No complex prompt interpretation** — just: read queue → render HTML → check for complete → repeat.

---

## Usage in Orchestrator

### Spawn at Pipeline Start

```bash
# Early in orchestrator (Step 1 initialization)
log_coordinator_id = spawn_agent(
  log-coordinator,
  issue_id: "8353",
  event_queue_path: ".TemporaryItems/Issues/wp-rocket/issue-8353/contracts/orchestrator-events.jsonl",
  log_file_path: ".TemporaryItems/Issues/wp-rocket/issue-8353-workflow-log.html",
  run_in_background: true
)
# Returns immediately — agent runs in background
```

### Monitor (Optional)

```bash
# Mid-pipeline, check if agent is still running
SendMessage(to: log_coordinator_id, message: "status")
# Agent responds with: events processed, current state, etc.
```

### Cleanup (Optional)

```bash
# At end, explicitly stop if needed (usually not required)
SendMessage(to: log_coordinator_id, message: "stop")
# Agent exits gracefully
```

---

## Output

**HTML Log File** at `log_file_path`:
- Interactive timeline of all events
- Event timestamps, types, sources, data
- Status badges (PASS/FAIL/WARN)
- Real-time updates as pipeline progresses
- Self-contained (no external dependencies)

---

## Customization

Users can modify the HTML template in the agent prompt below to change:
- CSS styling and colors
- Event rendering format
- Status badge appearance
- Typography and spacing

---

## Agent Prompt

You are the log-coordinator for orchestrator workflows.

**Your job:** Monitor the event queue and render events to an HTML log file in real-time.

**Inputs:** issue_id, event_queue_path, log_file_path

**Main loop:**
1. Create HTML template with embedded event rendering JavaScript
2. Get initial line count of event_queue_path
3. Every 2 seconds:
   - Read current line count
   - If increased: read new lines, parse JSON, append HTML
   - Check for "pipeline-complete" event
   - If found or 3600s elapsed: exit with status JSON
4. Stay alive until one of those exit conditions

**HTML template structure:**
- Dark GitHub-like theme
- Header with issue title and status badge
- Timeline section (empty initially)
- JavaScript addEvent(event) function to inject events
- CSS for styling events

Keep it simple. No complex logic. Just read → render → loop.

Return JSON on exit:
```json
{
  "status": "complete",
  "issue_id": "...",
  "events_processed": N,
  "log_file": "path/to/log.html",
  "reason": "pipeline-complete | timeout"
}
```
