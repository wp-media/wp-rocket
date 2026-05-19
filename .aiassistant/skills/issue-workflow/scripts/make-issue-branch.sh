#!/usr/bin/env bash
# Create a branch name from an issue number and title.
# Usage: make-issue-branch.sh <issue-number> "<issue-title>" [prefix]
# prefix: fix (default), enhancement, test
set -euo pipefail

# Required arguments.
ISSUE_NUMBER="${1:?issue number required}"
TITLE="${2:?issue title required}"

# Optional prefix — determines branch type per wp-rocket naming convention.
PREFIX="${3:-fix}"

# Validate prefix.
case "$PREFIX" in
  fix|enhancement|test) ;;
  *) PREFIX="fix" ;;
esac

# Build a short, URL-safe slug from the title (first 4 words max).
SLUG="$(printf '%s' "$TITLE" \
  | tr '[:upper:]' '[:lower:]' \
  | sed -E 's/[^a-z0-9]+/-/g; s/^-+//; s/-+$//' \
  | cut -d- -f1-4)"

# Branch naming convention per wp-rocket: <prefix>/<issue>-<slug>
BRANCH="${PREFIX}/${ISSUE_NUMBER}-${SLUG}"

# Create and switch to the branch.
git checkout -b "$BRANCH"
echo "$BRANCH"
