#!/usr/bin/env node
// PostToolUse hook — forwards wp-rocket pipeline GitHub comments to Slack
'use strict';

const https = require('https');
const fs    = require('fs');
const path  = require('path');

const SLACK_TOKEN = process.env.SLACK_BOT_TOKEN;
const CHANNEL_ID  = 'C0B680PH44T'; // #wp-rocket-issue-workflow
const THREADS_DIR = path.join(process.cwd(), '.TemporaryItems', 'Issues', 'wp-rocket', 'slack-threads');

setTimeout(() => process.exit(0), 9000);

let raw = '';
process.stdin.setEncoding('utf8');
process.stdin.on('data', c => { raw += c; });
process.stdin.on('end', async () => {
  try { await run(raw); } catch (e) { process.stderr.write(`slack-notify error: ${e.message}\n`); }
  process.exit(0);
});

// ---------------------------------------------------------------------------
// Triggers — issue comments, PR comments, PR-ready transitions
// ---------------------------------------------------------------------------

const TRIGGERS = [
  { test: cmd => cmd.includes('gh issue comment'), type: 'issue_comment' },
  { test: cmd => cmd.includes('gh pr comment'),    type: 'pr_comment'   },
  { test: cmd => /\bgh pr ready\b/.test(cmd),      type: 'pr_ready'     },
];

async function run(raw) {
  if (!SLACK_TOKEN) { process.stderr.write('slack-notify: SLACK_BOT_TOKEN not set\n'); return; }

  let data;
  try { data = JSON.parse(raw); } catch { return; }

  if (data.tool_name !== 'Bash') return;
  const command = data.tool_input?.command || '';
  const trigger = TRIGGERS.find(t => t.test(command));
  if (!trigger) return;

  const output = getOutput(data);

  if (trigger.type === 'pr_ready') {
    await handlePrReady(command, output);
    return;
  }

  // Require a GitHub URL in the output confirming the comment was posted
  const commentUrlMatch =
    output.match(/https:\/\/github\.com\/[^\s<>]+#issuecomment-\d+/) ||
    output.match(/https:\/\/github\.com\/[^\s<>]+#discussion_r\d+/);
  if (!commentUrlMatch) {
    process.stderr.write('slack-notify: no comment URL in gh output — command may have failed\n');
    return;
  }
  const commentUrl = commentUrlMatch[0];

  const isIssue  = trigger.type === 'issue_comment';
  const refMatch = command.match(/gh (?:issue|pr) comment\s+([^\s"'\\]+)/);
  const refNum   = refMatch?.[1]?.match(/\d+/)?.[0] || '';
  const ghUrl    = isIssue
    ? `https://github.com/wp-media/wp-rocket/issues/${refNum}`
    : `https://github.com/wp-media/wp-rocket/pull/${refNum}`;

  const body        = extractBody(command);
  const commentType = detectCommentType(body);

  // For PR comments, try to extract the associated issue number for threading
  const issueNum = isIssue ? refNum : (extractIssueFromBody(body) || '');

  const { text, blocks } = buildMessage(commentType, body, refNum, ghUrl, commentUrl, isIssue);

  // Thread all events for the same issue together
  const threadTs = issueNum ? getThreadTs(issueNum) : null;
  const payload  = { channel: CHANNEL_ID, text, blocks };
  if (threadTs) payload.thread_ts = threadTs;

  const postedTs = await postToSlack(payload);
  if (!threadTs && issueNum && postedTs) saveThreadTs(issueNum, postedTs);
}

// ---------------------------------------------------------------------------
// PR ready handler
// ---------------------------------------------------------------------------

async function handlePrReady(command, output) {
  const prNum =
    command.match(/\bgh pr ready\s+(\d+)/)?.[1] ||
    output.match(/pull\/(\d+)/)?.[1];
  if (!prNum) return;
  const prUrl = `https://github.com/wp-media/wp-rocket/pull/${prNum}`;

  await postToSlack({
    channel: CHANNEL_ID,
    text:    `PR #${prNum} is ready for review`,
    blocks: [
      {
        type: 'section',
        text: { type: 'mrkdwn', text: `🎉  *PR #${prNum} — Ready for Review*\nThe AI delivery pipeline is done. This PR is open for human review.` },
        accessory: { type: 'button', text: { type: 'plain_text', text: 'Open PR' }, url: prUrl, style: 'primary' }
      },
      { type: 'context', elements: [{ type: 'mrkdwn', text: '`wp-media/wp-rocket`' }] }
    ]
  });
}

// ---------------------------------------------------------------------------
// Comment type detection
// ---------------------------------------------------------------------------

function detectCommentType(body) {
  const h = (body.match(/^#{1,3}\s+(.+)$/m)?.[1] || '').toLowerCase();
  if (h.includes('delivery pipeline') || h.includes('pipeline — complete') || h.includes('pipeline complete')) return 'pipeline_summary';
  if (h.includes('groomin'))                                                                                    return 'grooming';
  if (h.includes('challenger') || h.includes('spec review'))                                                   return 'challenger';
  if (h.includes('implementation') || h.includes('dev complete'))                                              return 'implementation';
  if (h.includes('lead review') || h.includes('code review') || h.includes('pr review'))                      return 'lead_review';
  if (/ qa\b/.test(h) || h.includes('quality assurance') || h.includes('test report'))                        return 'qa';
  return 'generic';
}

// ---------------------------------------------------------------------------
// Message builder
// ---------------------------------------------------------------------------

function buildMessage(commentType, body, num, ghUrl, commentUrl, isIssue) {
  switch (commentType) {
    case 'pipeline_summary': return buildPipelineSummary(body, num, ghUrl, commentUrl);
    case 'grooming':         return buildStageCard('🔍', 'Grooming complete',   body, num, ghUrl, commentUrl, isIssue, buildGroomingDetail);
    case 'challenger':       return buildStageCard('⚔️',  'Spec Review',         body, num, ghUrl, commentUrl, isIssue, buildChallengerDetail);
    case 'implementation':   return buildStageCard('⚙️',  'Implementation',      body, num, ghUrl, commentUrl, isIssue, null);
    case 'lead_review':      return buildStageCard('👀', 'Lead Review',          body, num, ghUrl, commentUrl, isIssue, buildReviewDetail);
    case 'qa':               return buildStageCard('🧪', 'QA Report',            body, num, ghUrl, commentUrl, isIssue, buildQADetail);
    default:                 return buildGenericCard(body, num, ghUrl, commentUrl, isIssue);
  }
}

// ---------------------------------------------------------------------------
// Pipeline summary — header + markdown table of stages
// ---------------------------------------------------------------------------

function buildPipelineSummary(body, issueNum, issueUrl, commentUrl) {
  const title = body.match(/^#{1,3}\s+(.+)$/m)?.[1]?.trim() || 'Delivery Pipeline — Complete';

  const prMatch     = body.match(/\*\*PR:\*\*\s*\[#(\d+)\]\(([^)]+)\)/);
  const statusMatch = body.match(/\*\*Status:\*\*\s*([^\n|*]+)/);
  const prNum   = prMatch?.[1];
  const prUrl   = prMatch?.[2];
  const status  = statusMatch?.[1]?.trim() || 'READY FOR REVIEW';
  const isReady = status.includes('READY');

  // Build the markdown table from the stage table in the comment body
  const tableRows  = parseMarkdownTable(body);
  const tableLines = [
    '| Stage | Status | Notes |',
    '|---|---|---|',
    ...tableRows.map(([stage = '', result = '', notes = '']) => {
      const notesClean = (notes && notes !== '—') ? notes.trim() : '—';
      return `| ${stage.trim()} | ${result.trim()} | ${notesClean} |`;
    })
  ];

  // Follow-up tickets
  const followupMatch = body.match(/\*\*Follow-up[^:]*:\*\*\s*(.+)/);
  const followup = followupMatch?.[1]?.trim() || 'None';

  const statusEmoji = isReady ? '✅' : '🔄';
  const metaLine = [
    issueNum ? `<${issueUrl}|Issue #${issueNum}>` : null,
    prNum    ? `<${prUrl}|PR #${prNum}>` : null,
    `*${status}*`,
  ].filter(Boolean).join('  ·  ');

  const blocks = [
    { type: 'header', text: { type: 'plain_text', text: `${statusEmoji} ${title}`, emoji: true } },
    {
      type: 'section',
      text: { type: 'mrkdwn', text: `${metaLine}\n\`wp-media/wp-rocket\`` },
      accessory: { type: 'button', text: { type: 'plain_text', text: 'View Comment' }, url: commentUrl }
    },
    { type: 'divider' },
    {
      type: 'markdown',
      text: tableLines.join('\n') + `\n\n**Follow-up:** ${followup}`
    },
    { type: 'divider' },
  ];

  const actions = [];
  if (issueNum) actions.push({ type: 'button', text: { type: 'plain_text', text: `Issue #${issueNum}` }, url: issueUrl });
  if (prNum)    actions.push({ type: 'button', text: { type: 'plain_text', text: `Pull Request #${prNum}` }, url: prUrl, style: 'primary' });
  if (actions.length) blocks.push({ type: 'actions', elements: actions });

  return {
    text: `${statusEmoji} ${title} · Issue #${issueNum}`,
    blocks
  };
}

// ---------------------------------------------------------------------------
// Stage card — compact header + optional detail block
// ---------------------------------------------------------------------------

function buildStageCard(icon, label, body, num, ghUrl, commentUrl, isIssue, detailFn) {
  const refType = isIssue ? 'Issue' : 'PR';
  const subtitle = body.match(/^#{1,3}\s+(.+)$/m)?.[1]?.trim() || label;

  const blocks = [
    {
      type: 'section',
      text: { type: 'mrkdwn', text: `${icon}  *${label}*  ·  <${ghUrl}|${refType} #${num}>\n_${subtitle}_` },
      accessory: { type: 'button', text: { type: 'plain_text', text: 'View Comment' }, url: commentUrl }
    }
  ];

  // Add a rich detail block if we know how to extract meaningful structure from this comment type
  const detail = detailFn ? detailFn(body) : null;
  if (detail) blocks.push({ type: 'markdown', text: detail });

  blocks.push({
    type: 'context',
    elements: [{ type: 'mrkdwn', text: `\`wp-media/wp-rocket\`  ·  <${ghUrl}|${refType} #${num}>  ·  <${commentUrl}|Open in GitHub>` }]
  });

  return { text: `${label} — ${refType} #${num}`, blocks };
}

// ---------------------------------------------------------------------------
// Detail extractors — pull 3-4 structured facts from each comment type
// ---------------------------------------------------------------------------

function buildGroomingDetail(body) {
  const effort     = body.match(/\*\*[Ee]ffort[^:]*:\*\*\s*([^\n,|]+)/)?.[1]?.trim()
                  || body.match(/effort[:\s]+([XxSsMmLl]+)/)?.[1]?.trim() || null;
  const risk       = body.match(/\*\*[Rr]isk[^:]*:\*\*\s*([^\n,|]+)/)?.[1]?.trim()
                  || body.match(/risk[_\s]*level[:\s]+([A-Z]+)/i)?.[1]?.trim() || null;
  const confidence = body.match(/\*\*[Cc]onfidence[^:]*:\*\*\s*([^\n,|]+)/)?.[1]?.trim()
                  || body.match(/confidence[:\s]+([A-Z]+)/i)?.[1]?.trim() || null;
  const openQ      = (body.match(/open[_ ]questions?/gi) || []).length > 0
                  ? (body.match(/^\d+\.\s+/gm) || []).length || null
                  : null;

  const parts = [];
  if (effort)     parts.push(`**Effort:** ${effort}`);
  if (risk)       parts.push(`**Risk:** ${risk}`);
  if (confidence) parts.push(`**Confidence:** ${confidence}`);
  const meta = parts.join('  ·  ');

  const lines = [meta].filter(Boolean);
  if (openQ) lines.push(`**Open questions:** ${openQ} documented, proceeding with assumptions`);

  return lines.length ? lines.join('\n') : null;
}

function buildChallengerDetail(body) {
  const verdict = body.match(/\*\*(APPROVED|NEEDS_REVISION|BLOCKED)\*\*/)?.[1]
               || (body.toLowerCase().includes('approved') ? 'APPROVED' : null);
  const must = (body.match(/MUST_HAVE/g) || []).length;
  const should = (body.match(/SHOULD_HAVE/g) || []).length;

  const lines = [];
  if (verdict) lines.push(`**Verdict:** ${verdict}`);
  if (must)    lines.push(`**Must-have findings:** ${must}`);
  if (should)  lines.push(`**Should-have findings:** ${should}`);
  return lines.length ? lines.join('  ·  ') : null;
}

function buildReviewDetail(body) {
  const verdict  = body.match(/\*\*(PASS|REQUEST_CHANGES|APPROVED)\*\*/)?.[1]
                || (body.toLowerCase().includes('approved') ? 'APPROVED'
                  : body.toLowerCase().includes('request') ? 'REQUEST_CHANGES' : null);
  const critical = (body.match(/CRITICAL/g) || []).length;
  const high     = (body.match(/\bHIGH\b/g) || []).length;

  const lines = [];
  if (verdict)  lines.push(`**Verdict:** ${verdict}`);
  if (critical) lines.push(`**Critical blockers:** ${critical}`);
  if (high)     lines.push(`**High blockers:** ${high}`);
  return lines.length ? lines.join('  ·  ') : null;
}

function buildQADetail(body) {
  // Try to extract AC results as a task list
  const acLines = [];
  for (const line of body.split('\n')) {
    const m = line.match(/AC\d+[^—–-]*[—–-]\s*(.+)/);
    if (!m) continue;
    const passed = line.includes('PASS') || line.includes('✅');
    acLines.push(`- [${passed ? 'x' : ' '}] ${m[1].trim()}`);
    if (acLines.length >= 8) break; // cap at 8 items
  }
  return acLines.length ? acLines.join('\n') : null;
}

// ---------------------------------------------------------------------------
// Generic card — fallback
// ---------------------------------------------------------------------------

function buildGenericCard(body, num, ghUrl, commentUrl, isIssue) {
  const refType = isIssue ? 'Issue' : 'PR';
  const title   = body.match(/^#{1,3}\s+(.+)$/m)?.[1]?.trim() || 'Pipeline Comment';

  return {
    text: `AI Pipeline · ${refType} #${num}`,
    blocks: [
      {
        type: 'section',
        text: { type: 'mrkdwn', text: `🤖  *${title}*\n<${ghUrl}|${refType} #${num}> · \`wp-media/wp-rocket\`` },
        accessory: { type: 'button', text: { type: 'plain_text', text: 'View Comment' }, url: commentUrl }
      },
      { type: 'context', elements: [{ type: 'mrkdwn', text: `<${commentUrl}|View on GitHub>` }] }
    ]
  };
}

// ---------------------------------------------------------------------------
// Markdown table parser
// ---------------------------------------------------------------------------

function parseMarkdownTable(body) {
  const rows = [];
  for (const line of body.split('\n')) {
    if (!line.startsWith('|')) continue;
    if (/^\|[\s\-:|]+\|$/.test(line)) continue;
    const cells = line.split('|').slice(1, -1).map(c => c.trim());
    if (cells[0]?.toLowerCase() === 'stage') continue;
    if (cells.length >= 2) rows.push(cells);
  }
  return rows;
}

// ---------------------------------------------------------------------------
// Extract issue number from PR comment body text
// ---------------------------------------------------------------------------

function extractIssueFromBody(body) {
  return body.match(/[Ii]ssue\s*[:#]?\s*#?(\d+)/)?.[1] || null;
}

// ---------------------------------------------------------------------------
// Thread persistence — one Slack thread per GitHub issue
// ---------------------------------------------------------------------------

function getThreadTs(issueNum) {
  try { return fs.readFileSync(path.join(THREADS_DIR, `issue-${issueNum}.ts`), 'utf8').trim(); }
  catch { return null; }
}

function saveThreadTs(issueNum, ts) {
  try {
    fs.mkdirSync(THREADS_DIR, { recursive: true });
    fs.writeFileSync(path.join(THREADS_DIR, `issue-${issueNum}.ts`), ts);
  } catch (e) {
    process.stderr.write(`slack-notify: could not save thread ts: ${e.message}\n`);
  }
}

// ---------------------------------------------------------------------------
// Tool output extraction
// ---------------------------------------------------------------------------

function getOutput(data) {
  const content = data.tool_response?.content;
  if (Array.isArray(content)) return content.map(c => c.text || '').join('');
  return data.tool_response?.output || data.tool_result?.output || '';
}

// ---------------------------------------------------------------------------
// Body extraction from raw bash command (heredoc / double-quoted / single-quoted)
// ---------------------------------------------------------------------------

function extractBody(command) {
  let m = command.match(/(?:--body|-b)\s+"?\$\(cat\s+<<['"]?(\w+)['"]?\n([\s\S]*?)\n\1/);
  if (m) return m[2].trim();

  m = command.match(/(?:--body|-b)\s+"((?:[^"\\]|\\.)*)"/s);
  if (m && !m[1].trimStart().startsWith('$(')) {
    return m[1].replace(/\\n/g, '\n').replace(/\\"/g, '"').trim();
  }

  m = command.match(/(?:--body|-b)\s+'((?:[^'\\]|\\.)*)'/s);
  if (m) return m[1].trim();

  return '';
}

// ---------------------------------------------------------------------------
// Slack API — returns ts on success (used for threading)
// ---------------------------------------------------------------------------

async function postToSlack(payload) {
  const body = JSON.stringify(payload);
  return new Promise(resolve => {
    const req = https.request(
      {
        hostname: 'slack.com',
        path:     '/api/chat.postMessage',
        method:   'POST',
        headers:  {
          'Content-Type':   'application/json',
          'Authorization':  `Bearer ${SLACK_TOKEN}`,
          'Content-Length': Buffer.byteLength(body)
        }
      },
      res => {
        let respData = '';
        res.on('data', d => { respData += d; });
        res.on('end', () => {
          try {
            const resp = JSON.parse(respData);
            if (!resp.ok) {
              process.stderr.write(`slack-notify: Slack API error: ${resp.error}\n`);
              resolve(null);
            } else {
              resolve(resp.ts || null);
            }
          } catch (e) {
            process.stderr.write(`slack-notify: response parse error: ${e.message}\n`);
            resolve(null);
          }
        });
      }
    );
    req.on('error', e => { process.stderr.write(`slack-notify: network error: ${e.message}\n`); resolve(null); });
    req.setTimeout(8000, () => { req.destroy(); resolve(null); });
    req.write(body);
    req.end();
  });
}
