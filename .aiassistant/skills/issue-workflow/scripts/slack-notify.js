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

  // Extract issue number (first arg after "gh issue comment")
  const issueMatch = command.match(/gh issue comment\s+([^\s"'\\]+)/);
  if (!issueMatch) return;
  const issueRef = issueMatch[1];
  const issueNum = issueRef.match(/\d+/)?.[0] || issueRef;
  const issueUrl = `https://github.com/wp-media/wp-rocket/issues/${issueNum}`;

  // Require a valid comment URL in tool output — no URL means the command failed
  const output = getOutput(data);
  const commentUrlMatch = output.match(/https:\/\/github\.com\/[^\s]+#issuecomment-\d+/);
  if (!commentUrlMatch) return;
  const commentUrl = commentUrlMatch[0];

  const body = extractBody(command);
  await postToSlack(issueNum, issueUrl, commentUrl, body);
}

function getOutput(data) {
  const content = data.tool_response?.content;
  if (Array.isArray(content)) return content.map(c => c.text || '').join('');
  return data.tool_response?.output || data.tool_result?.output || '';
}

function extractBody(command) {
  // HEREDOC: --body "$(cat <<'DELIMITER'\n...\nDELIMITER\n)"  (any word delimiter)
  let m = command.match(/(?:--body|-b)\s+"?\$\(cat\s+<<['"]?(\w+)['"]?\n([\s\S]*?)\n\1/);
  if (m) return m[2].trim();

  // Double-quoted (skip if value starts with $( — that means heredoc failed to match above)
  m = command.match(/(?:--body|-b)\s+"((?:[^"\\]|\\.)*)"/s);
  if (m && !m[1].trimStart().startsWith('$(')) {
    return m[1].replace(/\\n/g, '\n').replace(/\\"/g, '"').trim();
  }

  // Single-quoted
  m = command.match(/(?:--body|-b)\s+'((?:[^'\\]|\\.)*)'/s);
  if (m) return m[1].trim();

  return '';
}

function buildPreview(bodyText) {
  if (!bodyText) return null;

  // Strip GitHub callout blocks: > [!NOTE]\n> ...
  let text = bodyText.replace(/^>\s*\[!(?:NOTE|WARNING|INFO|TIP|IMPORTANT)\]\s*\n(>\s*.+\n)*/gm, '');
  // Collapse leading "> " from remaining blockquotes
  text = text.replace(/^>\s?/gm, '');
  // Collapse 3+ blank lines to 2
  text = text.replace(/\n{3,}/g, '\n\n').trim();

  const MAX = 600;
  return text.length > MAX ? text.slice(0, MAX) + '…' : text;
}

async function postToSlack(issueNum, issueUrl, commentUrl, bodyText) {
  // Extract first ## heading as the "step title"
  const headingMatch = bodyText.match(/^#{1,3}\s+(.+)$/m);
  const stepTitle = headingMatch ? headingMatch[1].trim() : 'Comment Posted';

  const preview = buildPreview(bodyText);

  const viewButton = {
    type: 'button',
    text: { type: 'plain_text', text: 'View Comment', emoji: false },
    url: commentUrl
  };

  const blocks = [
    {
      type: 'section',
      text: {
        type: 'mrkdwn',
        text: `🤖 *${stepTitle}*\n<${issueUrl}|Issue #${issueNum}> · \`wp-media/wp-rocket\``
      },
      accessory: viewButton
    }
  ];

  if (preview) {
    blocks.push({
      type: 'section',
      text: { type: 'mrkdwn', text: preview }
    });
  }

  blocks.push({
    type: 'context',
    elements: [{ type: 'mrkdwn', text: `<${commentUrl}|View on GitHub>` }]
  });

  const payload = JSON.stringify({
    channel: CHANNEL_ID,
    text: `AI Pipeline · ${stepTitle} on Issue #${issueNum}`,
    blocks
  });

  return new Promise(resolve => {
    const req = https.request(
      {
        hostname: 'slack.com',
        path: '/api/chat.postMessage',
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${SLACK_TOKEN}`,
          'Content-Length': Buffer.byteLength(payload)
        }
      },
      res => { res.resume(); res.on('end', resolve); }
    );
    req.on('error', resolve);
    req.setTimeout(7000, () => { req.destroy(); resolve(); });
    req.write(payload);
    req.end();
  });
}
