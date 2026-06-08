# HTML Log Format Reference

> Read this file only when you need to write or update a log event.
> Do not load it at session start — load it on demand to keep orchestrator context lean.

Generate `.TemporaryItems/Issues/wp-rocket/issue-<N>-workflow-log.html`. Rewrite the
full file on each update. The event list only grows — never remove past events.

## Event types
| Type | Color | Icon | Meaning |
|---|---|---|---|
| `decision` | `#4f7cff` blue | ⟲ | Orchestrator routing decision with reasoning |
| `agent` | varies | ◆ | Agent invoked — input summary + JSON output |
| `gate` | green/red/orange | ⬡ | Orchestrator quality gate (DOD L2) |
| `escalation` | `#f85149` red | ⚠ | Human intervention needed |
| `parallel` | `#7d8590` gray | ⤢ | Non-blocking NTH dispatch to ticket-writer |

**Agent accent colors (use inline `style="color:..."`):**
- grooming-agent: `#22c55e`
- challenger: `#f59e0b`
- backend-agent / frontend-agent: `#22d3ee`
- release-agent: `#a855f7`
- lead-reviewer: `#4f7cff`
- qa-engineer: `#f472b6`
- ticket-writer: `#94a3b8`

## HTML structure

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Issue #N — Workflow Log · wp-rocket</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #0d1117; color: #e6edf3; min-height: 100vh; font-size: 14px; line-height: 1.5; }

    /* ── Header ── */
    .header { background: #161b22; border-bottom: 1px solid #30363d; padding: 24px 32px; display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; }
    .issue-ref { font-size: 12px; color: #7d8590; margin-bottom: 6px; letter-spacing: .02em; }
    .issue-title { font-size: 20px; font-weight: 700; color: #f0f6fc; line-height: 1.3; }
    .issue-meta { font-size: 13px; color: #8b949e; margin-top: 8px; }
    .status-badge { font-size: 12px; font-weight: 700; padding: 6px 16px; border-radius: 20px; white-space: nowrap; flex-shrink: 0; margin-top: 4px; letter-spacing: .04em; }
    .status-running { background: #1a2e1a; color: #3fb950; border: 1px solid #238636; animation: pulse 2s infinite; }
    .status-pass    { background: #1a2e1a; color: #3fb950; border: 1px solid #238636; }
    .status-failed  { background: #2d0f0f; color: #f85149; border: 1px solid #6e1a1a; }
    @keyframes pulse { 0%,100%{opacity:1}50%{opacity:.55} }

    /* ── Decisions strip ── */
    .decisions { display: flex; border-bottom: 1px solid #21262d; overflow-x: auto; background: #161b22; }
    .decision-item { padding: 14px 24px; border-right: 1px solid #21262d; white-space: nowrap; flex-shrink: 0; }
    .decision-label { color: #7d8590; display: block; margin-bottom: 4px; font-size: 11px; text-transform: uppercase; letter-spacing: .07em; font-weight: 600; }
    .decision-value { color: #e6edf3; font-weight: 600; font-size: 13px; }
    .decision-value a { color: #79c0ff; text-decoration: none; }
    .decision-value a:hover { text-decoration: underline; }

    /* ── Timeline & phases ── */
    .timeline { padding: 24px 32px 40px; display: flex; flex-direction: column; gap: 6px; max-width: 960px; margin: 0 auto; }
    .phase-label { font-size: 11px; font-weight: 700; color: #484f58; text-transform: uppercase; letter-spacing: .1em; padding: 16px 4px 6px; margin-top: 4px; border-top: 1px solid #21262d; }
    .phase-label:first-child { border-top: none; padding-top: 4px; }

    /* ── Event row ── */
    .event-wrapper { display: flex; flex-direction: column; border-radius: 10px; }
    .event { display: grid; grid-template-columns: 28px 130px 1fr auto 20px; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; border: 1px solid #21262d; background: #161b22; cursor: pointer; user-select: none; transition: background .1s; }
    .event-wrapper.open .event { border-radius: 10px 10px 0 0; border-bottom-color: transparent; }
    .event:hover { background: #1c2128; }
    .event-icon { font-size: 16px; line-height: 1; text-align: center; }
    .event-type { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; white-space: nowrap; }
    .event-summary { font-size: 13px; color: #c9d1d9; }
    .event-step { font-size: 11px; font-weight: 600; color: #484f58; background: #21262d; border-radius: 12px; padding: 2px 10px; white-space: nowrap; font-family: monospace; }
    .event-chevron { font-size: 16px; color: #484f58; transition: transform .15s; text-align: center; line-height: 1; }
    .event-wrapper.open .event-chevron { transform: rotate(90deg); color: #8b949e; }

    /* Event type border accents */
    .event[data-type="decision"] { border-color: #1e2d5a; }
    .event[data-type="gate"][data-status="pass"] { border-color: #1a3020; }
    .event[data-type="gate"][data-status="warn"] { border-color: #6e4a00; }
    .event[data-type="gate"][data-status="fail"] { border-color: #6e1a1a; background: #160808; }
    .event[data-type="escalation"] { border-color: #6e1a1a; background: #160808; }
    .event[data-type="parallel"] { opacity: .7; }

    /* ── Detail panel ── */
    .event-detail { display: none; background: #0d1117; border: 1px solid #21262d; border-top: none; border-radius: 0 0 10px 10px; padding: 20px 20px 20px 60px; }
    .event-wrapper.open .event-detail { display: block; }
    .detail-sections { display: flex; flex-direction: column; gap: 16px; }
    .detail-section { display: flex; flex-direction: column; gap: 6px; }
    .detail-section.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 32px; }
    .detail-section.two-col > * { display: flex; flex-direction: column; gap: 6px; }
    .detail-label { font-size: 11px; font-weight: 700; color: #8b949e; text-transform: uppercase; letter-spacing: .07em; }
    .detail-body { font-size: 13px; color: #c9d1d9; line-height: 1.65; }
    .detail-body strong { color: #f0f6fc; }
    .detail-body a { color: #79c0ff; text-decoration: none; }
    .detail-body a:hover { text-decoration: underline; }
    .detail-body pre { background: #161b22; border: 1px solid #30363d; border-radius: 8px; padding: 14px 16px; font-family: "SF Mono", "Cascadia Code", monospace; font-size: 12px; color: #e6edf3; overflow-x: auto; white-space: pre-wrap; word-break: break-word; margin-top: 6px; line-height: 1.6; }
    .detail-body code { background: #21262d; padding: 2px 6px; border-radius: 4px; font-family: "SF Mono", monospace; font-size: 12px; color: #79c0ff; }
    .file-list { display: flex; flex-direction: column; gap: 4px; margin-top: 4px; }
    .file-item { display: flex; gap: 10px; align-items: baseline; }
    .file-name { font-family: "SF Mono", monospace; font-size: 12px; color: #79c0ff; white-space: nowrap; }
    .file-desc { font-size: 13px; color: #8b949e; }
    .detail-verdict { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 20px; letter-spacing: .03em; }
    .verdict-pass { background: #1a2e1a; color: #3fb950; border: 1px solid #238636; }
    .verdict-skip { background: #1c2128; color: #7d8590; border: 1px solid #30363d; }
    .verdict-warn { background: #2d2000; color: #ffa657; border: 1px solid #6e4a00; }
    .verdict-fail { background: #2d0f0f; color: #f85149; border: 1px solid #6e1a1a; }

    footer { font-size: 12px; color: #484f58; padding: 20px 32px; border-top: 1px solid #21262d; max-width: 960px; margin: 0 auto; }
    footer code { font-family: monospace; font-size: 11px; color: #7d8590; }
  </style>
</head>
<body>

<div class="header">
  <div>
    <div class="issue-ref">wp-media/wp-rocket · Issue #N</div>
    <div class="issue-title">ISSUE_TITLE</div>
    <div class="issue-meta">Branch: BRANCH &nbsp;·&nbsp; Calibration: CALIBRATION_MODE &nbsp;·&nbsp; Started: START_TIME</div>
  </div>
  <span class="status-badge status-running">● OVERALL_STATUS</span>
</div>

<div class="decisions">
  <div class="decision-item"><span class="decision-label">Scope</span><span class="decision-value">—</span></div>
  <div class="decision-item"><span class="decision-label">Domains</span><span class="decision-value">—</span></div>
  <div class="decision-item"><span class="decision-label">Branch prefix</span><span class="decision-value">—</span></div>
  <div class="decision-item"><span class="decision-label">Acceptance criteria</span><span class="decision-value">— items</span></div>
  <div class="decision-item"><span class="decision-label">Pull request</span><span class="decision-value">—</span></div>
</div>

<div class="timeline">
  <!-- Phase labels group events. Use: Setup · Branch & Implementation · PR Creation · Quality Gates · Finalize -->
  <!-- Events appended here as the pipeline runs — never pre-populated -->
</div>

<footer>Last updated: TIMESTAMP &nbsp;·&nbsp; <code>.TemporaryItems/Issues/wp-rocket/issue-N-workflow-log.html</code></footer>

<script>
document.querySelectorAll('.event').forEach(function(e) {
  e.addEventListener('click', function() {
    this.closest('.event-wrapper').classList.toggle('open');
  });
});
</script>
</body>
</html>
```

## Event HTML patterns

Phase label — insert before the first event of each pipeline phase:
```html
<div class="phase-label">Setup</div>
<!-- phases: Setup · Branch &amp; Implementation · PR Creation · Quality Gates · Finalize -->
```

### ROUTING DECISION
```html
<div class="event-wrapper">
  <div class="event" data-type="decision">
    <div class="event-icon" style="color:#4f7cff">⟲</div>
    <div class="event-type" style="color:#4f7cff">Routing</div>
    <div class="event-summary">Post-grooming: skip CHALLENGER — XS + LOW + HIGH confidence</div>
    <div class="event-step">step N</div>
    <div class="event-chevron">›</div>
  </div>
  <div class="event-detail">
    <div class="detail-sections">
      <div class="detail-section two-col">
        <div>
          <div class="detail-label">Routing signals</div>
          <div class="detail-body">effort=XS · risk_level=LOW · complexity=LOW · grooming_confidence=HIGH</div>
        </div>
        <div>
          <div class="detail-label">Decision</div>
          <div class="detail-body">Skip CHALLENGER — all skip conditions met. Proceed to branch creation.</div>
        </div>
      </div>
      <div class="detail-section">
        <div class="detail-label">Orchestrator reasoning</div>
        <div class="detail-body">WHY_THIS_ROUTING_DECISION — what made it clear or borderline, which risk_notes excerpt was weighed</div>
      </div>
    </div>
  </div>
</div>
```

### AGENT event
```html
<div class="event-wrapper">
  <div class="event" data-type="agent">
    <div class="event-icon" style="color:AGENT_COLOR">◆</div>
    <div class="event-type" style="color:AGENT_COLOR">AGENT_NAME</div>
    <div class="event-summary">ONE_LINE_RESULT_SUMMARY</div>
    <div class="event-step">step N</div>
    <div class="event-chevron">›</div>
  </div>
  <div class="event-detail">
    <div class="detail-sections">
      <div class="detail-section two-col">
        <div>
          <div class="detail-label">LABEL_1</div>
          <div class="detail-body">CONTENT_1</div>
        </div>
        <div>
          <div class="detail-label">LABEL_2</div>
          <div class="detail-body">CONTENT_2</div>
        </div>
      </div>
      <div class="detail-section">
        <div class="detail-label">Return JSON (excerpt)</div>
        <div class="detail-body"><pre>{ ... }</pre></div>
      </div>
    </div>
  </div>
</div>
```

### GATE event (DOD L2)
```html
<div class="event-wrapper">
  <div class="event" data-type="gate" data-status="pass">
    <div class="event-icon" style="color:#22c55e">⬡</div>
    <div class="event-type" style="color:#22c55e">DOD L2</div>
    <div class="event-summary">PASS — all 6 checks clean, Co-Authored-By trailer present on N commits</div>
    <div class="event-step">step N</div>
    <div class="event-chevron">›</div>
  </div>
  <div class="event-detail">
    <div class="detail-sections">
      <div class="detail-section">
        <div class="detail-label">Checks</div>
        <div class="detail-body"><pre>1. Manual validation → PASS
2. Automated tests   → PASS (N tests)
3. Documentation     → PASS (or SKIP — no public API change)
4. PR description    → PASS (all sections filled)
5. CI                → PASS (gh pr checks all green)
6. File scope        → PASS (N/A in L2)
Co-Authored-By trailer → present on all N commits</pre></div>
      </div>
      <div class="detail-section">
        <div class="detail-label">Layer 1 delta</div>
        <div class="detail-body">Issues caught by L2 that L1 missed (or "None")</div>
      </div>
    </div>
  </div>
</div>
```

For FAIL: use `data-status="fail"` and `style="color:#f85149"`. For WARN: `data-status="warn"` and `style="color:#ffa657"`.

### ESCALATION event
```html
<div class="event-wrapper">
  <div class="event" data-type="escalation">
    <div class="event-icon" style="color:#f85149">⚠</div>
    <div class="event-type" style="color:#f85149">Escalation</div>
    <div class="event-summary">CHALLENGER BLOCKED after 1 revision — human decision needed</div>
    <div class="event-step">step N</div>
    <div class="event-chevron">›</div>
  </div>
  <div class="event-detail">
    <div class="detail-sections">
      <div class="detail-section two-col">
        <div>
          <div class="detail-label">What happened</div>
          <div class="detail-body">EXACT_BLOCKER_OR_ERROR</div>
        </div>
        <div>
          <div class="detail-label">What was tried</div>
          <div class="detail-body">Agents invoked + loop count</div>
        </div>
      </div>
      <div class="detail-section">
        <div class="detail-label">Suggested next steps</div>
        <div class="detail-body">1. OPTION_FROM_ALTERNATIVE_SUGGESTIONS<br>2. OPTION_FROM_ALTERNATIVE_SUGGESTIONS</div>
      </div>
    </div>
  </div>
</div>
```

### PARALLEL (NTH dispatch)
```html
<div class="event-wrapper">
  <div class="event" data-type="parallel">
    <div class="event-icon" style="color:#7d8590">⤢</div>
    <div class="event-type" style="color:#7d8590">NTH Dispatch</div>
    <div class="event-summary">ticket-writer dispatched — N items from AGENT_NAME (non-blocking)</div>
    <div class="event-step">step N</div>
    <div class="event-chevron">›</div>
  </div>
  <div class="event-detail">
    <div class="detail-sections">
      <div class="detail-section">
        <div class="detail-label">Items dispatched</div>
        <div class="detail-body">ITEM_1 (COULD_HAVE)<br>ITEM_2 (NICE_TO_HAVE)</div>
      </div>
    </div>
  </div>
</div>
```

## Event detail panel content — per agent

**Reasoning discipline (applies to every agent event):**
Every AGENT event detail panel must include a full-width **Reasoning** section populated
from the agent's `reasoning` field (or reconstructed from its output if not returned
explicitly). Three sub-fields, each a distinct paragraph or bulleted list:
- **Alternatives considered** — other approaches or options the agent weighed
- **Hesitations** — what was unclear, ambiguous, or uncertain during the run
- **Decision rationale** — why the chosen approach won over the alternatives

This is the primary debugging and improvement surface. If reasoning is thin, push the
agent to elaborate before writing the HTML. "No alternatives considered" and "No
hesitations" are red flags — they mean the agent did not reflect, not that the task was
trivial.

---

**ROUTING DECISION:**
- Routing signals: `effort` · `risk_level` · `complexity` · `grooming_confidence`
- Decision: next agent/step and why
- Orchestrator reasoning: what made the case clear or borderline, which `risk_notes` excerpt was weighed, what alternative routing was discarded and why

**grooming-agent AGENT event:**
- Reasoning: why this approach over alternatives; what in the spec was ambiguous; what was assumed vs. confirmed
- Key signals: effort · risk_level · complexity · confidence · open_questions count
- Return JSON: compact grooming JSON

**challenger AGENT event:**
- Verdict: `<span class="detail-verdict verdict-pass">APPROVED</span>` / `verdict-warn NEEDS_REVISION` / `verdict-fail BLOCKED`
- Reasoning: what risks were weighed; what made the verdict clear or a close call; which findings were borderline MUST_HAVE vs SHOULD_HAVE
- Feedback: MUST_HAVE/SHOULD_HAVE items classified (or "No blocking findings")
- NTH items dispatched: COULD_HAVE/NICE_TO_HAVE count

**backend-agent / frontend-agent AGENT event:**
- Reasoning: alternatives considered · hesitations · decision rationale (from `reasoning` field)
- Implementation decisions: key choices made during implementation
- Files modified: list with one-line description each
- docs result: DONE/SKIP + files
- e2e_smoke result: PASS/FAIL/SKIP + scenarios
- DOD L1 result: checks with PASS/WARN and counts
- Commit: SHA + message

**release-agent AGENT event:**
- PR: URL + title
- Trailer verified: yes (N commits)
- Branch pushed: yes
- PR number

**lead-reviewer AGENT event:**
- Verdict: badge (PASS / REQUEST_CHANGES)
- Reasoning: what was examined most carefully; what was a close call in criticality classification; what made the verdict clear
- Blockers: list by criticality (or "None")
- Nice-to-haves dispatched: count

**qa-engineer AGENT event:**
- Environment boot: only include when Strategy B was used **or** when boot failed (as a failure explanation). Omit entirely for backend-only runs where boot succeeded and Strategy B was not used — `gh pr checkout`, `bin/dev-up.sh exit 0`, and `localhost:8888 HTTP 200` are setup noise, not QA findings.
- Reasoning: why each strategy was selected or skipped; what made any criterion borderline; what was uncertain in the evidence
- Strategies considered: list each (A/B/C) with one-line reason it was used or skipped
- AC results: each criterion → PASS / FAIL / PARTIAL with method and evidence
- Blockers: list (or "None")
- Report: PR comment URL

**DOD L2 GATE event:**
- Checks: 6 checks with output excerpt
- Trailer verification: result per commit
- Layer 1 delta: issues L2 caught that L1 missed (or "None")
- CI Checks: each → PASS / FAIL
- Failures: error excerpt + fix applied (or "None")
