#!/usr/bin/env node
// PostToolUse hook: forwards gh issue comment calls to #wp-rocket-issue-workflow
'use strict';

const https = require('https');

const SLACK_TOKEN = process.env.SLACK_BOT_TOKEN;
const CHANNEL_ID = 'C0B680PH44T'; // #wp-rocket-issue-workflow

setTimeout(() => process.exit(0), 8000);

let raw = '';
process.stdin.setEncoding('utf8');
process.stdin.on('data', c => { raw += c; });
process.stdin.on('end', async () => {
  try { await run(raw); } catch {}
  process.exit(0);
});

async function run(raw) {
  if (!SLACK_TOKEN) return;

  let data;
  try { data = JSON.parse(raw); } catch { return; }

  if (data.tool_name !== 'Bash') return;

  const command = data.tool_input?.command || '';
  if (!command.includes('gh issue comment')) return;

  const issueMatch = command.match(/gh issue comment\s+([^\s"'\\]+)/);
  if (!issueMatch) return;
  const issueRef = issueMatch[1];
  const issueNum = issueRef.match(/\d+/)?.[0] || issueRef;
  const issueUrl = `https://github.com/wp-media/wp-rocket/issues/${issueNum}`;

  // Require a valid comment URL — no URL means the command failed
  const output = getOutput(data);
  const commentUrlMatch = output.match(/https:\/\/github\.com\/[^\s]+#issuecomment-\d+/);
  if (!commentUrlMatch) return;
  const commentUrl = commentUrlMatch[0];

  const body = extractBody(command);
  const blocks = buildBlocks(body, issueNum, issueUrl, commentUrl);
  const headingMatch = body.match(/^#{1,3}\s+(.+)$/m);
  const title = headingMatch?.[1]?.trim() || 'Comment Posted';

  await postToSlack({
    channel: CHANNEL_ID,
    text: `AI Pipeline · ${title} on Issue #${issueNum}`,
    blocks
  });
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
// Body extraction from raw bash command
// ---------------------------------------------------------------------------

function extractBody(command) {
  // HEREDOC: --body "$(cat <<'ANY_DELIMITER'\n...\nANY_DELIMITER\n)"
  let m = command.match(/(?:--body|-b)\s+"?\$\(cat\s+<<['"]?(\w+)['"]?\n([\s\S]*?)\n\1/);
  if (m) return m[2].trim();

  // Double-quoted (skip if it starts with $( — unmatched heredoc)
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
// Block Kit builders
// ---------------------------------------------------------------------------

function buildBlocks(body, issueNum, issueUrl, commentUrl) {
  if (body.includes('| Stage |') || body.includes('|Stage|')) {
    return buildPipelineSummaryBlocks(body, issueNum, issueUrl, commentUrl);
  }
  return buildGenericBlocks(body, issueNum, issueUrl, commentUrl);
}

function buildPipelineSummaryBlocks(body, issueNum, issueUrl, commentUrl) {
  const headingMatch = body.match(/^#{1,3}\s+(.+)$/m);
  const title = headingMatch?.[1]?.trim() || 'Delivery Pipeline — Complete';

  const prMatch   = body.match(/\*\*PR:\*\*\s*\[#(\d+)\]\(([^)]+)\)/);
  const statusMatch = body.match(/\*\*Status:\*\*\s*([^\n|]+)/);
  const prNum  = prMatch?.[1];
  const prUrl  = prMatch?.[2];
  const status = statusMatch?.[1]?.trim() || '';
  const isReady = status.includes('READY');

  const blocks = [
    {
      type: 'section',
      text: {
        type: 'mrkdwn',
        text: `${isReady ? '✅' : '🔄'} *${title}*\n<${issueUrl}|Issue #${issueNum}> · \`wp-media/wp-rocket\``
      },
      accessory: {
        type: 'button',
        text: { type: 'plain_text', text: 'View Comment', emoji: false },
        url: commentUrl,
        style: 'primary'
      }
    }
  ];

  // PR + Status context bar
  const metaParts = [];
  if (prNum) metaParts.push(`*PR:* <${prUrl}|#${prNum}>`);
  if (status) metaParts.push(`*Status:* ${status}`);
  if (metaParts.length) {
    blocks.push({
      type: 'context',
      elements: [{ type: 'mrkdwn', text: metaParts.join('  ·  ') }]
    });
  }

  blocks.push({ type: 'divider' });

  // Stage results as 2-column fields grid
  const tableRows = parseMarkdownTable(body);
  if (tableRows.length) {
    const fields = tableRows.map(([stage = '', result = '', notes = '']) => {
      const noteText = notes && notes !== '—' ? `\n_${notes}_` : '';
      return { type: 'mrkdwn', text: `*${stage}*\n${result}${noteText}` };
    });
    // Slack max: 10 fields per section
    for (let i = 0; i < fields.length; i += 10) {
      blocks.push({ type: 'section', fields: fields.slice(i, i + 10) });
    }
  }

  // Footer: follow-up tickets + GitHub link
  const followupMatch = body.match(/\*\*Follow-up tickets:\*\*\s*(.+)/);
  const followup = followupMatch?.[1]?.trim();
  const footerParts = [];
  if (followup) footerParts.push(`*Follow-up:* ${followup}`);
  footerParts.push(`<${commentUrl}|View on GitHub>`);

  blocks.push({ type: 'divider' });
  blocks.push({
    type: 'context',
    elements: [{ type: 'mrkdwn', text: footerParts.join('  ·  ') }]
  });

  return blocks;
}

function buildGenericBlocks(body, issueNum, issueUrl, commentUrl) {
  const headingMatch = body.match(/^#{1,3}\s+(.+)$/m);
  const title = headingMatch?.[1]?.trim() || 'Comment Posted';

  // Clean preview: strip GitHub callouts, collapse whitespace, truncate
  let preview = body
    .replace(/^>\s*\[!(?:NOTE|WARNING|TIP|IMPORTANT|CAUTION)\]\s*\n(>\s*.+\n)*/gm, '')
    .replace(/^>\s?/gm, '')
    .replace(/\n{3,}/g, '\n\n')
    .trim();
  if (preview.length > 600) preview = preview.slice(0, 600) + '…';

  const blocks = [
    {
      type: 'section',
      text: {
        type: 'mrkdwn',
        text: `🤖 *${title}*\n<${issueUrl}|Issue #${issueNum}> · \`wp-media/wp-rocket\``
      },
      accessory: {
        type: 'button',
        text: { type: 'plain_text', text: 'View Comment', emoji: false },
        url: commentUrl,
        style: 'primary'
      }
    }
  ];

  if (preview) {
    blocks.push({ type: 'section', text: { type: 'mrkdwn', text: preview } });
  }

  blocks.push({
    type: 'context',
    elements: [{ type: 'mrkdwn', text: `<${commentUrl}|View on GitHub>` }]
  });

  return blocks;
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
    // Skip header row
    if (cells[0]?.toLowerCase() === 'stage') continue;
    if (cells.length >= 2) rows.push(cells);
  }
  return rows;
}

// ---------------------------------------------------------------------------
// Slack API
// ---------------------------------------------------------------------------

async function postToSlack(payload) {
  const body = JSON.stringify(payload);
  return new Promise(resolve => {
    const req = https.request(
      {
        hostname: 'slack.com',
        path: '/api/chat.postMessage',
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${SLACK_TOKEN}`,
          'Content-Length': Buffer.byteLength(body)
        }
      },
      res => { res.resume(); res.on('end', resolve); }
    );
    req.on('error', resolve);
    req.setTimeout(7000, () => { req.destroy(); resolve(); });
    req.write(body);
    req.end();
  });
}
