#!/usr/bin/env bash
# Initialize a PR draft file for a given issue number.
# Usage: init-pr-draft.sh <issue-number>
set -euo pipefail

die() {
  echo "init-pr-draft: $*" >&2
  exit 1
}

# Required argument.
ISSUE_NUMBER="${1:?issue number required}"

# Resolve repository root (works regardless of the current working directory).
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR=""
if command -v git >/dev/null 2>&1; then
  ROOT_DIR="$(git -C "$SCRIPT_DIR" rev-parse --show-toplevel 2>/dev/null || true)"
fi
if [ -z "$ROOT_DIR" ]; then
  ROOT_DIR="$(cd "$SCRIPT_DIR/../../../../" && pwd)"
fi
if [ ! -d "$ROOT_DIR" ]; then
  die "Unable to resolve repository root from ${SCRIPT_DIR}."
fi

# Template path and output location (per-issue subfolder under .ai/issues/<N>/).
TEMPLATE="${ROOT_DIR}/.claude/skills/orchestrator/references/pr-template.md"
OUT_DIR="${ROOT_DIR}/.ai/issues/${ISSUE_NUMBER}"
OUT_FILE="${OUT_DIR}/pull.md"

if [ ! -f "$TEMPLATE" ]; then
  die "Template not found: ${TEMPLATE}"
fi

# Idempotent — skip if pull.md already exists (avoids overwriting in-progress drafts).
if [ -f "$OUT_FILE" ]; then
  echo "$OUT_FILE"
  exit 0
fi

# Ensure output directory exists and copy the template.
mkdir -p "$OUT_DIR"
cp "$TEMPLATE" "$OUT_FILE"

# Replace placeholder with the issue number (macOS + Linux compatible).
sed -i '' "s/(issue number)/${ISSUE_NUMBER}/g" "$OUT_FILE" 2>/dev/null \
  || sed -i "s/(issue number)/${ISSUE_NUMBER}/g" "$OUT_FILE"

# Print the path for downstream tooling.
echo "$OUT_FILE"
