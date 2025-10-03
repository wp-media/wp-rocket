# Merge Conflict Resolution for PR #7749

## Summary
This document describes the resolution of merge conflicts between the `chore/rename-rocket-insights` branch and the `feature/3.20` branch for PR #7749.

## Problem
PR #7749 attempts to merge `chore/rename-rocket-insights` into `feature/3.20`, but there were merge conflicts because:
- The `chore/rename-rocket-insights` branch renamed files from `PerformanceMonitoring` to `RocketInsights`
- The `feature/3.20` branch made changes to the old `PerformanceMonitoring` files

## Files with Conflicts
1. `inc/Engine/Admin/PerformanceMonitoring/ServiceProvider.php` (deleted in chore/rename-rocket-insights, modified in feature/3.20)
2. `inc/Engine/Admin/PerformanceMonitoring/URLLimit/Subscriber.php` (deleted in chore/rename-rocket-insights, modified in feature/3.20)

## Changes from feature/3.20 that needed to be applied
The following changes from commits `29f385c91` and `727b39315` in feature/3.20 needed to be applied to the renamed RocketInsights files:

### 1. RocketInsights/Database/Queries/RocketInsights.php
**Added:** `unblur_rows()` method to change blurred rows into unblurred rows.

### 2. RocketInsights/URLLimit/Subscriber.php
**Added:**
- `Context $context` property and constructor parameter
- `unblur_rows()` method that checks context permissions before unblurring rows
- Event subscription for `unblur_rows` on `rocket_rocket_insights_upgrade` action

### 3. RocketInsights/ServiceProvider.php
**Added:** `'ri_context'` argument to the URLLimitSubscriber dependency injection

## Resolution Steps
1. Applied the `unblur_rows()` method to `RocketInsights/Database/Queries/RocketInsights.php`
2. Added Context dependency to `RocketInsights/URLLimit/Subscriber.php`
3. Added `unblur_rows()` method to `RocketInsights/URLLimit/Subscriber.php`
4. Updated `RocketInsights/ServiceProvider.php` to inject the context into URLLimitSubscriber
5. Merged feature/3.20 into the copilot branch
6. Removed the old PerformanceMonitoring files that were causing conflicts

## Result
All merge conflicts have been resolved. The changes from feature/3.20 have been properly applied to the renamed RocketInsights files, and the merge is now complete.

## Next Steps
To apply these changes to the original PR #7749:
1. Merge this copilot branch back into `chore/rename-rocket-insights`
2. The PR #7749 should then be able to merge cleanly into `feature/3.20`
