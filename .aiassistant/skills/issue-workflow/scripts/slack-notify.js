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
// Triggers — catches issue comments, PR comments, and PR-ready transitions
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

  // Require a GitHub URL in the output confirming the comment posted successfully
  const commentUrlMatch =
    output.match(/https:\/\/github\.com\/[^\s<>]+#issuecomment-\d+/) ||
    output.match(/https:\/\/github\.com\/[^\s<>]+#discussion_r\d+/);
  if (!commentUrlMatch) {
    process.stderr.write('slack-notify: no comment URL found in output — gh command may have failed\n');
    return;
  }
  const commentUrl = commentUrlMatch[0];

  const isIssue  = trigger.type === 'issue_comment';
  const refMatch = command.match(/gh (?:issue|pr) comment\s+([^\s"'\\]+)/);
  const refNum   = refMatch?.[1]?.match(/\d+/)?.[0] || '';

  const body        = extractBody(command);
  const commentType = detectCommentType(body);

  // For PR comments, try to extract the associated issue number from body text for threading
  const issueNum = isIssue ? refNum : (extractIssueFromBody(body) || '');
  const ghUrl    = isIssue
    ? `https://github.com/wp-media/wp-rocket/issues/${refNum}`
    : `https://github.com/wp-media/wp-rocket/pull/${refNum}`;

  const { text, attachments } = buildMessage(commentType, body, refNum, ghUrl, commentUrl, isIssue);

  // Thread all events for the same issue together in a single Slack thread
  const threadTs = issueNum ? getThreadTs(issueNum) : null;
  const payload  = { channel: CHANNEL_ID, text, attachments };
  if (threadTs) payload.thread_ts = threadTs;

  const postedTs = await postToSlack(payload);

  // Save the thread ts on first message so follow-up events can thread under it
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
    text:    `PR #${prNum} is now ready for review`,
    attachments: [{
      color: '#22c55e',
      blocks: [
        {
          type: 'section',
          text: { type: 'mrkdwn', text: `*PR #${prNum} — Ready for Review* 🎉\n\`wp-media/wp-rocket\`` },
          accessory: {
            type: 'button',
            text: { type: 'plain_text', text: 'Open PR', emoji: true },
            url: prUrl, style: 'primary'
          }
        },
        {
          type: 'context',
          elements: [{ type: 'mrkdwn', text: 'The AI delivery pipeline has finished — this PR is ready for human review.' }]
        }
      ]
    }]
  });
}

// ---------------------------------------------------------------------------
// Comment type detection — maps heading keywords to card type
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
// Message builder — routes to the right card design
// ---------------------------------------------------------------------------

function buildMessage(commentType, body, num, ghUrl, commentUrl, isIssue) {
  switch (commentType) {
    case 'pipeline_summary': return buildPipelineSummary(body, num, ghUrl, commentUrl);
    case 'grooming':         return buildStageCard('🔍', 'Grooming',        '#22c55e', body, num, ghUrl, commentUrl, isIssue);
    case 'challenger':       return buildStageCard('⚔️',  'Spec Review',     '#f59e0b', body, num, ghUrl, commentUrl, isIssue);
    case 'implementation':   return buildStageCard('⚙️',  'Implementation',  '#22d3ee', body, num, ghUrl, commentUrl, isIssue);
    case 'lead_review':      return buildStageCard('👀', 'Lead Review',      '#4f7cff', body, num, ghUrl, commentUrl, isIssue);
    case 'qa':               return buildStageCard('🧪', 'QA Report',        '#f472b6', body, num, ghUrl, commentUrl, isIssue);
    default:                 return buildGenericCard(body, num, ghUrl, commentUrl, isIssue);
  }
}

// ---------------------------------------------------------------------------
// Pipeline summary card — final orchestrator comment with full stage table
// ---------------------------------------------------------------------------

function buildPipelineSummary(body, issueNum, issueUrl, commentUrl) {
  const headingMatch = body.match(/^#{1,3}\s+(.+)$/m);
  const title = headingMatch?.[1]?.trim() || 'Delivery Pipeline — Complete';

  const prMatch     = body.match(/\*\*PR:\*\*\s*\[#(\d+)\]\(([^)]+)\)/);
  const statusMatch = body.match(/\*\*Status:\*\*\s*([^\n|*]+)/);
  const prNum   = prMatch?.[1];
  const prUrl   = prMatch?.[2];
  const status  = statusMatch?.[1]?.trim() || 'READY FOR REVIEW';
  const isReady = status.includes('READY');
  const color   = isReady ? '#22c55e' : '#ffa657';
  const headerIcon = isReady ? '✅' : '🔄';

  // Stage table → vertical readable list
  const tableRows = parseMarkdownTable(body);
  const stageText = tableRows.map(formatStageLine).join('\n');

  // Follow-up tickets
  const followupMatch = body.match(/\*\*Follow-up[^:]*:\*\*\s*(.+)/);
  const followup = followupMatch?.[1]?.trim() || 'None';

  const metaLine = [
    issueNum ? `<${issueUrl}|Issue #${issueNum}>` : null,
    prNum    ? `<${prUrl}|PR #${prNum}>` : null,
    `*Status:* ${status}`,
  ].filter(Boolean).join('  ·  ');

  const blocks = [
    { type: 'header', text: { type: 'plain_text', text: `${headerIcon} ${title}`, emoji: true } },
    {
      type: 'section',
      text: { type: 'mrkdwn', text: `${metaLine}\n\`wp-media/wp-rocket\`` },
      accessory: {
        type: 'button',
        text: { type: 'plain_text', text: 'View Comment', emoji: true },
        url: commentUrl
      }
    },
    { type: 'divider' },
    ...(stageText ? [{ type: 'section', text: { type: 'mrkdwn', text: stageText } }] : []),
    { type: 'divider' },
    {
      type: 'context',
      elements: [{ type: 'mrkdwn', text: `*Follow-up:* ${followup}  ·  <${commentUrl}|View on GitHub>` }]
    },
  ];

  const actions = [];
  if (issueNum) actions.push({ type: 'button', text: { type: 'plain_text', text: 'Issue', emoji: true }, url: issueUrl });
  if (prNum)    actions.push({ type: 'button', text: { type: 'plain_text', text: 'Pull Request', emoji: true }, url: prUrl, style: 'primary' });
  if (actions.length) blocks.push({ type: 'actions', elements: actions });

  return {
    text: `AI Pipeline · ${title} on Issue #${issueNum}`,
    attachments: [{ color, blocks }]
  };
}

function formatStageLine([stage = '', result = '', notes = '']) {
  const icon       = getStageIcon(result);
  const resultText = stripEmoji(result);
  const notesText  = (notes && notes.trim() !== '—') ? notes.trim() : '';
  const detail     = [resultText, notesText].filter(Boolean).join(' · ');
  return `${icon}  *${stage.trim()}*${detail ? `  —  ${detail}` : ''}`;
}

function getStageIcon(result) {
  if (result.includes('✅')) return '✅';
  if (result.includes('⏭')) return '⏭';
  if (result.includes('❌')) return '❌';
  if (result.includes('⚠')) return '⚠️';
  if (result.includes('🔄')) return '🔄';
  const r = result.toLowerCase();
  if (r.includes('pass') || r.includes('approved') || r.includes('all pass')) return '✅';
  if (r.includes('skip'))                                                       return '⏭';
  if (r.includes('fail') || r.includes('error'))                               return '❌';
  if (r.includes('warn'))                                                       return '⚠️';
  return '•';
}

// Strip common emoji characters from result text
const EMOJI_RE = /[\u{2600}-\u{27FF}\u{1F300}-\u{1F9FF}\u{FE00}-\u{FEFF}]|⏭|✅|❌/gu;
function stripEmoji(str) { return str.replace(EMOJI_RE, '').trim(); }

// ---------------------------------------------------------------------------
// Stage card — intermediate pipeline events (grooming, QA, etc.)
// ---------------------------------------------------------------------------

function buildStageCard(icon, stageName, color, body, num, ghUrl, commentUrl, isIssue) {
  const refType      = isIssue ? 'Issue' : 'PR';
  const headingMatch = body.match(/^#{1,3}\s+(.+)$/m);
  const subtitle     = headingMatch?.[1]?.trim() || `${stageName} Update`;

  // Clean preview: strip GitHub callouts, headings, and excess blank lines
  let preview = body
    .replace(/^>\s*\[!(?:NOTE|WARNING|TIP|IMPORTANT|CAUTION)\][^\n]*(\n>\s*[^\n]*)*/gm, '')
    .replace(/^>\s?/gm, '')
    .replace(/^#{1,3}\s+.+$/gm, '')
    .replace(/\n{3,}/g, '\n\n')
    .trim();
  if (preview.length > 380) preview = preview.slice(0, 380) + '…';

  const blocks = [
    {
      type: 'section',
      text: { type: 'mrkdwn', text: `${icon}  *${stageName}*  ·  ${refType} #${num}\n_${subtitle}_` },
      accessory: {
        type: 'button',
        text: { type: 'plain_text', text: 'View Comment', emoji: true },
        url: commentUrl
      }
    },
    ...(preview ? [{ type: 'section', text: { type: 'mrkdwn', text: preview } }] : []),
    {
      type: 'context',
      elements: [{ type: 'mrkdwn', text: `\`wp-media/wp-rocket\`  ·  <${ghUrl}|${refType} #${num}>  ·  <${commentUrl}|Open in GitHub>` }]
    }
  ];

  return {
    text: `${stageName} — ${refType} #${num}`,
    attachments: [{ color, blocks }]
  };
}

// ---------------------------------------------------------------------------
// Generic card — fallback for unrecognized comment types
// ---------------------------------------------------------------------------

function buildGenericCard(body, num, ghUrl, commentUrl, isIssue) {
  const refType = isIssue ? 'Issue' : 'PR';
  const title   = body.match(/^#{1,3}\s+(.+)$/m)?.[1]?.trim() || 'Pipeline Comment';

  let preview = body
    .replace(/^>\s*\[!(?:NOTE|WARNING|TIP|IMPORTANT|CAUTION)\][^\n]*(\n>\s*[^\n]*)*/gm, '')
    .replace(/^>\s?/gm, '')
    .replace(/\n{3,}/g, '\n\n')
    .trim();
  if (preview.length > 480) preview = preview.slice(0, 480) + '…';

  return {
    text: `AI Pipeline · ${refType} #${num}`,
    attachments: [{
      color: '#7d8590',
      blocks: [
        {
          type: 'section',
          text: { type: 'mrkdwn', text: `🤖  *${title}*\n<${ghUrl}|${refType} #${num}> · \`wp-media/wp-rocket\`` },
          accessory: {
            type: 'button',
            text: { type: 'plain_text', text: 'View Comment', emoji: true },
            url: commentUrl
          }
        },
        ...(preview ? [{ type: 'section', text: { type: 'mrkdwn', text: preview } }] : []),
        { type: 'context', elements: [{ type: 'mrkdwn', text: `<${commentUrl}|View on GitHub>` }] }
      ]
    }]
  };
}

// ---------------------------------------------------------------------------
// Markdown table parser
// ---------------------------------------------------------------------------

function parseMarkdownTable(body) {
  const rows = [];
  for (const line of body.split('\n')) {
    if (!line.startsWith('|')) continue;
    if (/^\|[\s\-:|]+\|$/.test(line)) continue; // separator row
    const cells = line.split('|').slice(1, -1).map(c => c.trim());
    if (cells[0]?.toLowerCase() === 'stage') continue; // header row
    if (cells.length >= 2) rows.push(cells);
  }
  return rows;
}

// ---------------------------------------------------------------------------
// Extract issue number from PR comment body text (e.g. "Issue #8229")
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
// Body extraction from raw bash command (heredoc, double-quoted, single-quoted)
// ---------------------------------------------------------------------------

function extractBody(command) {
  // HEREDOC: --body "$(cat <<'DELIM'\n...\nDELIM\n)"
  let m = command.match(/(?:--body|-b)\s+"?\$\(cat\s+<<['"]?(\w+)['"]?\n([\s\S]*?)\n\1/);
  if (m) return m[2].trim();

  // Double-quoted (skip if it starts with heredoc)
  m = command.match(/(?:--body|-b)\s+"((?:[^"\\]|\\.)*)"/s);
  if (m && !m[1].trimStart().startsWith('$(')) {
    return m[1].replace(/\\n/g, '\n').replace(/\\"/g, '"').trim();
  }

  // Single-quoted
  m = command.match(/(?:--body|-b)\s+'((?:[^'\\]|\\.)*)'/s);
  if (m) return m[1].trim();

  return '';
}

// ---------------------------------------------------------------------------
// Slack API — returns the message ts on success (used for threading)
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
