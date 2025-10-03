# Merge Conflict Resolution for PR #7749

## Summary
This document describes the resolution of merge conflicts between the `chore/rename-rocket-insights` branch and the `feature/3.20` branch.

## Conflicts Identified
The merge conflicts occurred in the following built JavaScript files:
- `assets/js/wpr-admin.js`
- `assets/js/wpr-admin.min.js`
- `assets/js/wpr-admin.min.js.map`

## Source File Changes
The source files were successfully auto-merged without conflicts:
- `inc/Engine/Admin/RocketInsights/Subscriber.php` - Added global score data initialization
- `src/js/global/ajax.js` - Added initialization of globalScoreData from localized script data

## Resolution Steps

### 1. Merge Attempt
```bash
git checkout chore/rename-rocket-insights
git merge feature/3.20
```

### 2. Resolve Built File Conflicts
The conflicts in the built JavaScript files were resolved by:
1. Taking the current branch version (`--ours`) as the starting point
2. Rebuilding the JavaScript assets from the merged source files

```bash
git checkout --ours assets/js/wpr-admin.js assets/js/wpr-admin.min.js assets/js/wpr-admin.min.js.map
git add assets/js/wpr-admin.js assets/js/wpr-admin.min.js assets/js/wpr-admin.min.js.map
```

### 3. Rebuild Assets
```bash
npm install
npm run build:js
```

### 4. Stage Rebuilt Assets
```bash
git add assets/js/
```

### 5. Complete Merge
```bash
git commit -m "Merge branch 'feature/3.20' into chore/rename-rocket-insights"
```

## Result
The merge was successfully completed with commit `10cfca513d51741fa8e62d6ca41d57b9e0d08239`.

**This commit has been created locally on the `chore/rename-rocket-insights` branch and is ready to be pushed.**

To verify the commit exists:
```bash
git log chore/rename-rocket-insights --oneline -1
# Should show: 10cfca513 Merge branch 'feature/3.20' into chore/rename-rocket-insights
```

### Changes in Merge Commit
- `assets/js/lazyload-css.min.js` - Rebuilt
- `assets/js/wpr-admin.js` - Rebuilt with merged changes
- `assets/js/wpr-admin.min.js` - Rebuilt with merged changes  
- `assets/js/wpr-admin.min.js.map` - Rebuilt
- `assets/js/wpr-beacon.js` - Rebuilt
- `assets/js/wpr-beacon.min.js` - Rebuilt
- `assets/js/wpr-beacon.min.js.map` - Rebuilt
- `inc/Engine/Admin/RocketInsights/Subscriber.php` - Auto-merged (added global score data)
- `src/js/global/ajax.js` - Auto-merged (added globalScoreData initialization)

## Verification
The merge resolution was verified by:
1. Ensuring no conflict markers remain in any files
2. Successfully building all JavaScript assets
3. Confirming all changes are properly staged

## To Apply This Resolution

If you need to recreate this merge resolution on the `chore/rename-rocket-insights` branch:

```bash
# Ensure you're on the correct branch
git checkout chore/rename-rocket-insights

# Fetch the latest changes
git fetch origin feature/3.20

# Attempt the merge
git merge origin/feature/3.20

# Resolve conflicts by rebuilding assets
git checkout --ours assets/js/wpr-admin.js assets/js/wpr-admin.min.js assets/js/wpr-admin.min.js.map
git add assets/js/wpr-admin.js assets/js/wpr-admin.min.js assets/js/wpr-admin.min.js.map

# Rebuild JavaScript
npm install
npm run build:js

# Stage all rebuilt assets
git add assets/js/

# Complete the merge
git commit

# Push the merge
git push origin chore/rename-rocket-insights
```

## Notes
- The conflicts occurred only in built/generated files, not in source code
- The source file changes from both branches were compatible and merged cleanly
- The resolution simply required rebuilding the assets from the merged source files
- No manual code changes were required to resolve the conflicts
