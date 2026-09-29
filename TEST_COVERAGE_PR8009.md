# Test Coverage for PR #8009: Data Storage Handling

This document describes the comprehensive test coverage added for the Data Storage Handling changes introduced in PR #8009.

## Overview

PR #8009 introduced a new `metric_data` column to the Rocket Insights database table to store detailed performance metrics (LCP, TBT, CLS, TTFB) from API responses. This test suite provides comprehensive coverage for all aspects of this feature.

## Test Structure

### Unit Tests

Unit tests verify business logic in isolation without requiring WordPress or database:

#### 1. Row::parse_metric_data() - `parseMetricDataTest.php`
**Location:** `tests/Unit/inc/Engine/Admin/RocketInsights/Database/Rows/parseMetricDataTest.php`

**Purpose:** Tests the private `parse_metric_data()` method in the RocketInsights Row class that handles parsing metric data from various input formats.

**Test Cases:**
- `shouldReturnNullForNullInput` - Verifies null input returns null
- `shouldReturnNullForEmptyString` - Verifies empty string returns null
- `shouldReturnArrayWhenInputIsArray` - Verifies array input is preserved (from test fixtures)
- `shouldDecodeJsonString` - Verifies JSON string from database is decoded correctly
- `shouldHandlePartialMetrics` - Verifies partial metric data is handled properly
- `shouldHandleZeroValues` - Verifies zero values are preserved (not treated as empty)
- `shouldHandleNullValuesInArray` - Verifies null values within metric arrays are preserved

**Why Important:** Ensures the Row class correctly handles metric data regardless of whether it comes from:
- Database (JSON string)
- Test fixtures (array)
- Missing/incomplete data

#### 2. Manager::parse_test_results() - `parseTestResultsTest.php`
**Location:** `tests/Unit/inc/Engine/Admin/RocketInsights/Jobs/Manager/parseTestResultsTest.php`

**Purpose:** Tests the private `parse_test_results()` method that processes API responses and extracts test data including metrics.

**Test Cases:**
- `shouldReturnDefaultsWhenDataMissing` - Empty API response returns defaults
- `shouldReturnDefaultsWhenDataDataMissing` - Missing data.data returns defaults
- `shouldParseBasicTestResults` - Parses basic report_url and performance_score
- `shouldParseTestResultsWithMetrics` - Parses complete metric data (LCP, TBT, CLS, TTFB)
- `shouldHandlePartialMetrics` - Handles API responses with only some metrics
- `shouldHandleZeroValues` - Preserves zero values in metrics (important for TBT=0, CLS=0.0)

**Why Important:** Ensures the Manager correctly:
- Extracts all metric data from API responses
- Stores complete data for future extensibility
- Handles edge cases gracefully
- Preserves zero values (which are valid metrics)

### Integration Tests

Integration tests verify functionality with WordPress and database operations:

#### 3. Table Migration - `AddMetricDataColumnTest.php`
**Location:** `tests/Integration/inc/Engine/Admin/RocketInsights/Database/Tables/AddMetricDataColumnTest.php`

**Purpose:** Tests the database migration that adds the `metric_data` column to existing installations.

**Test Cases:**
- `testShouldAddMetricDataColumn` - Verifies column is added when missing
  - Simulates old database by dropping column
  - Runs migration
  - Verifies column exists with correct type (longtext, nullable)
- `testShouldNotFailWhenColumnAlreadyExists` - Idempotent migration
  - Ensures running migration twice doesn't fail
  - Important for upgrade scenarios

**Why Important:** Ensures:
- Existing WP Rocket installations can upgrade smoothly
- Migration is idempotent (safe to run multiple times)
- Column has correct schema (longtext, nullable)

#### 4. Manager::get_query() - `GetQueryTest.php`
**Location:** `tests/Integration/inc/Engine/Admin/RocketInsights/Jobs/Manager/GetQueryTest.php`

**Purpose:** Tests the `get_query()` helper method that provides access to the Query instance.

**Test Cases:**
- `testShouldReturnQueryInstance` - Returns correct instance type
- `testShouldReturnSameQueryInstance` - Returns same instance (not new instances)
- `testShouldAllowQueryOperations` - Query instance is functional
  - Creates a test row
  - Retrieves it via the query instance
  - Verifies data integrity

**Why Important:** This method is used by the upgrade callback to access `update_completed_tests_to_pending()`. Tests ensure:
- Proper dependency injection
- Instance reuse (performance)
- Functional query operations

#### 5. Query::update_completed_tests_to_pending() - `UpdateCompletedTestsToPendingTest.php`
**Location:** `tests/Integration/inc/Engine/Admin/RocketInsights/Database/Queries/UpdateCompletedTestsToPendingTest.php`

**Purpose:** Tests the database method that updates completed tests to pending status during upgrade.

**Test Cases:**
- `shouldUpdateAllCompletedTests` - Updates all completed rows
  - Creates 2 completed tests
  - Verifies both updated to pending
  - Returns correct count
- `shouldOnlyUpdateCompletedTests` - Selective update
  - Creates mix: completed, failed, pending
  - Only completed changes to pending
  - Failed and pending remain unchanged
- `testShouldReturnZeroWhenNoCompletedTests` - Edge case
  - Only non-completed tests exist
  - Returns 0 (no updates needed)

**Why Important:** Critical for upgrade path:
- Ensures existing completed tests get refreshed with metric_data
- Only updates completed tests (not failed/pending)
- Accurate reporting of update counts

## Tests from PR #8009

These tests were included in the original PR and complement the new tests:

### 6. Queries::make_status_completed() with Metrics - `MakeStatusCompletedWithMetricsTest.php`
**Location:** `tests/Integration/inc/Engine/Admin/RocketInsights/Database/Queries/MakeStatusCompletedWithMetricsTest.php`

**Purpose:** Integration test verifying metric_data is saved when completing tests.

**Test Cases:**
- `shouldSaveMetricDataWhenProvided` - Saves metric data as JSON
- `shouldSaveNullWhenMetricDataNotProvided` - Handles missing metric_data
- `shouldSavePartialMetricData` - Handles incomplete metrics
- `shouldSaveMetricDataWithZeroValues` - Preserves zero values

### 7. Subscriber::on_update_refresh_metric_data() - `OnUpdateRefreshMetricDataTest.php`
**Location:** `tests/Integration/inc/Engine/Admin/RocketInsights/Subscriber/OnUpdateRefreshMetricDataTest.php`

**Purpose:** Tests the upgrade callback that refreshes existing tests.

**Test Cases:**
- `shouldUpdateCompletedTestsToPending` - Updates on version upgrade
- `shouldNotUpdateWhenOldVersionIsHigher` - Version check prevents unnecessary updates
- `shouldOnlyUpdateCompletedTests` - Selective update (completed only)

## Test Coverage Summary

### Methods Tested:
- ✅ `RocketInsights\Database\Rows\RocketInsights::parse_metric_data()` - Unit test
- ✅ `RocketInsights\Jobs\Manager::parse_test_results()` - Unit test
- ✅ `RocketInsights\Database\Tables\RocketInsights::add_metric_data_column()` - Integration test
- ✅ `RocketInsights\Jobs\Manager::get_query()` - Integration test
- ✅ `RocketInsights\Database\Queries\RocketInsights::update_completed_tests_to_pending()` - Integration test
- ✅ `RocketInsights\Database\Queries\RocketInsights::make_status_completed()` - Integration test (from PR)
- ✅ `RocketInsights\Subscriber::on_update_refresh_metric_data()` - Integration test (from PR)

### Scenarios Covered:
- ✅ Null/empty metric_data handling
- ✅ JSON decoding from database
- ✅ Array preservation from test fixtures
- ✅ Partial/incomplete metrics
- ✅ Zero value preservation (TBT=0, CLS=0.0)
- ✅ Null values within metric data
- ✅ Missing API response data
- ✅ Database migration (fresh install vs upgrade)
- ✅ Idempotent migrations
- ✅ Version-gated upgrade callbacks
- ✅ Selective row updates (completed only)

### Test Types:
- **Unit Tests:** 2 test classes, 12+ test cases
- **Integration Tests:** 5 test classes, 12+ test cases
- **Total:** 7 new test classes, 24+ test cases

## Running the Tests

### Run All RocketInsights Tests
```bash
composer test-integration -- --group RocketInsights
```

### Run AdminOnly Tests
```bash
composer test-integration-adminonly
```

### Run Specific Test Files
```bash
# Unit tests
composer test-unit -- tests/Unit/inc/Engine/Admin/RocketInsights/Database/Rows/parseMetricDataTest.php
composer test-unit -- tests/Unit/inc/Engine/Admin/RocketInsights/Jobs/Manager/parseTestResultsTest.php

# Integration tests
composer test-integration -- tests/Integration/inc/Engine/Admin/RocketInsights/Database/Tables/AddMetricDataColumnTest.php
composer test-integration -- tests/Integration/inc/Engine/Admin/RocketInsights/Jobs/Manager/GetQueryTest.php
composer test-integration -- tests/Integration/inc/Engine/Admin/RocketInsights/Database/Queries/UpdateCompletedTestsToPendingTest.php
```

## Test Data Fixtures

All tests use the fixture pattern established in WP Rocket:
- Test data is defined in `tests/Fixtures/` directory
- Fixtures match test file names
- Data is loaded via `configTestData()` method
- Promotes test readability and maintainability

## Acceptance Criteria Met

All requirements from the original issue are satisfied:

- ✅ **Unit tests created for all new storage-related functions**
  - parse_metric_data()
  - parse_test_results()

- ✅ **Integration tests for data storage workflows**
  - make_status_completed() with metrics
  - Database migration (add_metric_data_column)
  - Update workflow (update_completed_tests_to_pending)
  - Upgrade callback (on_update_refresh_metric_data)

- ✅ **Edge cases and error scenarios are tested**
  - Null/empty data
  - Partial metrics
  - Zero values
  - Missing API responses
  - Idempotent migrations

- ✅ **Tests achieve adequate code coverage for PR #8009 changes**
  - All new methods tested
  - All modified methods tested
  - Database schema changes tested

- ✅ **All tests follow WP Rocket conventions**
  - Uses TestCase base classes
  - Uses DBTrait for database operations
  - Uses fixture pattern for test data
  - Follows naming conventions
  - Uses @group annotations

## Notes

- Tests are designed to run in isolation (set_up/tear_down properly implemented)
- Database is properly cleaned between tests
- Tests use the container for dependency injection
- All tests include proper PHPDoc blocks
- Tests are grouped with `@group RocketInsights` and `@group AdminOnly`
