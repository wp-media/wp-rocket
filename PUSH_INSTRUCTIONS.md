# Instructions to Push the Merge Resolution

## Summary
The merge conflicts for PR #7749 have been successfully resolved locally on the `chore/rename-rocket-insights` branch. The merge commit (hash: `10cfca513d51741fa8e62d6ca41d57b9e0d08239`) is ready to be pushed to the remote repository.

## Current State
- **Branch**: `chore/rename-rocket-insights`
- **Merge Commit**: `10cfca513` - "Merge branch 'feature/3.20' into chore/rename-rocket-insights"
- **Status**: Local only (not yet pushed to remote)

## To Push the Resolution

### Option 1: Direct Push (Requires Write Access)
If you have write access to the repository, you can push the resolution directly:

```bash
git checkout chore/rename-rocket-insights
git push origin chore/rename-rocket-insights
```

### Option 2: Using the Resolution Script
If the branch has diverged or needs to be recreated, use the provided script:

```bash
./apply-merge-resolution.sh
```

This script will:
1. Checkout the PR branch
2. Merge feature/3.20
3. Resolve conflicts by rebuilding assets
4. Create the merge commit

## Verification
After pushing, verify the PR status on GitHub:
- PR #7749 should no longer show merge conflicts
- The PR should be marked as mergeable
- All CI/CD checks should run against the merged code

## Files Included in This Resolution
- `RESOLUTION_SUMMARY.md` - High-level summary of the resolution
- `MERGE_CONFLICT_RESOLUTION.md` - Detailed step-by-step documentation
- `apply-merge-resolution.sh` - Automated script to recreate the resolution
- `PUSH_INSTRUCTIONS.md` - This file

## Technical Details
- Conflicts were in built JavaScript files only
- Source files merged cleanly without conflicts
- Resolution required rebuilding JavaScript assets from merged source files
- No manual code changes were necessary
