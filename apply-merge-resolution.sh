#!/bin/bash

# Script to apply the merge conflict resolution to PR #7749
# This script replicates the merge resolution that was performed locally

set -e

echo "=== Applying Merge Conflict Resolution for PR #7749 ==="
echo ""

# Ensure we're in the repository root
cd "$(git rev-parse --show-toplevel)"

# Checkout the PR branch
echo "Checking out branch chore/rename-rocket-insights..."
git checkout chore/rename-rocket-insights

# Fetch the latest changes from feature/3.20
echo "Fetching latest changes from feature/3.20..."
git fetch origin feature/3.20

# Check if already merged
if git merge-base --is-ancestor origin/feature/3.20 HEAD; then
    echo "✓ feature/3.20 is already merged into this branch"
    exit 0
fi

# Attempt the merge
echo "Merging feature/3.20 into chore/rename-rocket-insights..."
if git merge origin/feature/3.20; then
    echo "✓ Merge completed without conflicts"
    exit 0
fi

echo "Conflicts detected. Resolving..."

# The conflicts are in built files, so we resolve by rebuilding
echo "Taking current branch version of built files..."
git checkout --ours assets/js/wpr-admin.js assets/js/wpr-admin.min.js assets/js/wpr-admin.min.js.map
git add assets/js/wpr-admin.js assets/js/wpr-admin.min.js assets/js/wpr-admin.min.js.map

# Check if npm dependencies are installed
if [ ! -d "node_modules" ]; then
    echo "Installing npm dependencies..."
    npm install
fi

# Rebuild JavaScript assets
echo "Rebuilding JavaScript assets..."
npm run build:js

# Stage the rebuilt assets
echo "Staging rebuilt assets..."
git add assets/js/

# Complete the merge
echo "Completing merge commit..."
git commit -m "Merge branch 'feature/3.20' into chore/rename-rocket-insights"

echo ""
echo "✓ Merge conflict resolution completed successfully!"
echo ""
echo "The merge commit has been created. To push it, run:"
echo "  git push origin chore/rename-rocket-insights"
