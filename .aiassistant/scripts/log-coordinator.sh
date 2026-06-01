#!/bin/bash
# Log Coordinator: Real-time event queue to HTML log renderer
# Monitors orchestrator-events.jsonl and renders to workflow-log.html

set -e

ISSUE_ID="${1:?Issue ID required}"
EVENT_QUEUE=".TemporaryItems/Issues/wp-rocket/issue-${ISSUE_ID}/contracts/orchestrator-events.jsonl"
LOG_FILE=".TemporaryItems/Issues/wp-rocket/issue-${ISSUE_ID}-workflow-log.html"
TIMEOUT_SECONDS="${2:-3600}"

if [ ! -f "$EVENT_QUEUE" ]; then
  echo "Error: Event queue not found at $EVENT_QUEUE"
  exit 1
fi

echo "Log Coordinator: Issue #$ISSUE_ID"
echo "  Queue: $EVENT_QUEUE"
echo "  Log:   $LOG_FILE"
echo "  Timeout: ${TIMEOUT_SECONDS}s"
echo ""

# State tracking
LAST_LINE=0
START_TIME=$(date +%s)

# Initialize HTML log with template
init_html() {
  cat > "$LOG_FILE" <<'TEMPLATE'
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Issue Workflow Log</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #0d1117; color: #e6edf3; min-height: 100vh; padding: 20px; }
    .container { max-width: 1200px; margin: 0 auto; }
    .header { margin-bottom: 30px; }
    .title { font-size: 28px; font-weight: 700; color: #f0f6fc; margin-bottom: 10px; }
    .meta { font-size: 13px; color: #8b949e; }
    .status-badge { display: inline-block; padding: 6px 12px; border-radius: 16px; font-size: 12px; font-weight: 600; margin-top: 10px; }
    .status-running { background: #1a2e1a; color: #3fb950; }
    .status-complete { background: #1a2e1a; color: #3fb950; }
    .timeline { border-left: 2px solid #30363d; padding-left: 20px; margin-top: 20px; }
    .event { margin-bottom: 20px; padding: 12px; background: #161b22; border: 1px solid #30363d; border-radius: 6px; }
    .event-time { color: #7d8590; font-size: 12px; font-family: monospace; }
    .event-type { display: inline-block; background: #0d47a1; color: #58a6ff; padding: 2px 6px; border-radius: 3px; font-size: 11px; margin-left: 8px; }
    .event-source { color: #79c0ff; font-weight: 600; }
    .event-data { margin-top: 8px; padding: 8px; background: #0d1117; border-left: 2px solid #30363d; font-size: 12px; color: #c9d1d9; font-family: monospace; }
    .status-pass { color: #3fb950; }
    .status-fail { color: #f85149; }
    .status-warn { color: #d29922; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="title">Workflow Log — Issue #ISSUE_ID</div>
      <div class="meta">
        <span id="event-count">0 events</span> •
        <span id="status" class="status-badge status-running">RUNNING</span>
      </div>
    </div>
    <div id="timeline" class="timeline"></div>
  </div>
  <script>
    const timeline = document.getElementById('timeline');
    const eventCount = document.getElementById('event-count');
    const status = document.getElementById('status');
    let count = 0;

    window.addEvent = function(event) {
      count++;
      eventCount.textContent = count + ' event' + (count !== 1 ? 's' : '');

      const div = document.createElement('div');
      div.className = 'event';

      const time = new Date(event.timestamp).toLocaleTimeString();
      const type = event.type || 'unknown';
      const source = event.source || 'system';

      let statusClass = '';
      if (event.data && event.data.overall) {
        statusClass = event.data.overall === 'PASS' ? 'status-pass' :
                      event.data.overall === 'FAIL' ? 'status-fail' : 'status-warn';
      }

      div.innerHTML = `
        <div>
          <span class="event-time">${time}</span>
          <span class="event-type">${type}</span>
          <span class="event-source">${source}</span>
          ${statusClass ? '<span class="' + statusClass + '"> ' + event.data.overall + '</span>' : ''}
        </div>
        <div class="event-data">${JSON.stringify(event.data || {}, null, 2)}</div>
      `;

      timeline.appendChild(div);

      // Mark pipeline complete
      if (event.type === 'pipeline-complete') {
        status.textContent = 'COMPLETE';
        status.className = 'status-badge status-complete';
      }
    };
  </script>
</body>
</html>
TEMPLATE

  sed -i '' "s/ISSUE_ID/$ISSUE_ID/g" "$LOG_FILE"
}

# Render new events from queue
render_events() {
  local current_line=$(wc -l < "$EVENT_QUEUE" 2>/dev/null || echo 0)

  if [ "$current_line" -gt "$LAST_LINE" ]; then
    # Extract new events and append to HTML
    tail -n +$((LAST_LINE + 1)) "$EVENT_QUEUE" | while IFS= read -r line; do
      [ -z "$line" ] && continue
      # Escape for JavaScript
      line_escaped=$(echo "$line" | sed 's/\\/\\\\/g' | sed 's/"/\\"/g')
      echo "window.addEvent($line);" >> "$LOG_FILE.tmp"
    done

    # Append to HTML before </script>
    if [ -f "$LOG_FILE.tmp" ]; then
      sed -i '' '/<\/script>/r '"$LOG_FILE.tmp" "$LOG_FILE"
      rm -f "$LOG_FILE.tmp"
      LAST_LINE=$current_line
    fi
  fi
}

# Main loop
init_html
echo "✓ HTML template initialized"
echo ""
echo "Polling for events (timeout: ${TIMEOUT_SECONDS}s)..."

while true; do
  NOW=$(date +%s)
  ELAPSED=$((NOW - START_TIME))

  # Timeout check
  if [ $ELAPSED -ge $TIMEOUT_SECONDS ]; then
    echo "✓ Timeout reached ($TIMEOUT_SECONDS seconds)"
    break
  fi

  # Render new events
  render_events

  # Check for pipeline complete
  if grep -q '"type":"pipeline-complete"' "$EVENT_QUEUE" 2>/dev/null; then
    echo "✓ Pipeline complete event detected"
    sleep 2  # Final event flush
    render_events
    break
  fi

  # Poll interval
  sleep 2
done

echo "✓ Log coordinator finished"
echo "✓ Final log written to: $LOG_FILE"
