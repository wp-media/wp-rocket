# SaaS-Backed Features

This document lists WP Rocket features in this fork that depend on remote WP Rocket services or service-like external APIs. These are the main candidates to reimplement locally or replace with your own compatible service.

## Shared SaaS Job API

Most optimization jobs use `WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractAPIClient`.

- Default base URL: `https://saas.wp-rocket.me/`
- Override constant: `WP_ROCKET_SAAS_API_URL`
- Credentials added to every request body: `credentials[wpr_email]`, `credentials[wpr_key]`
- Common lifecycle: add job, store job ID/queue, poll status, process result, retry failures.

A compatible replacement should support POST/GET requests at the feature-specific paths below and return HTTP `200` or `201` for accepted work.

## Remove Unused CSS

Code:
- `inc/Engine/Optimization/RUCSS/APIHandler/APIClient.php`
- `inc/Engine/Optimization/RUCSS/Jobs/Manager.php`

Endpoint path:
- `rucss-job`

Add-to-queue request body:

```json
{
  "url": "https://example.com/page/?nowprocket=1&no_optimize=1",
  "config": {
    "is_mobile": false,
    "is_home": false,
    "rucss_safelist": [],
    "skip_attr": [],
    "optimization_list": ["rucss"]
  },
  "credentials": {
    "wpr_email": "",
    "wpr_key": ""
  }
}
```

Expected add-to-queue response:

```json
{
  "code": 200,
  "message": "queued",
  "contents": {
    "jobId": "abc123",
    "queueName": "rucss"
  }
}
```

Status request body:

```json
{
  "id": "abc123",
  "force_queue": "rucss",
  "is_home": false,
  "credentials": {}
}
```

Expected completed response:

```json
{
  "code": 200,
  "status": "completed",
  "contents": {
    "success": true,
    "shakedCSS": "body{...}",
    "shakedCSS_size": 1234,
    "above_the_fold_result": {
      "lcp": [],
      "images_above_fold": []
    }
  }
}
```

Implementation notes:
- Use a browser renderer such as Playwright/Chromium to load the page.
- Collect CSS coverage, parse CSS with PostCSS or css-tree, preserve media queries, keyframes, font faces, CSS variables, and safelisted selectors.
- Return `shakedCSS`; WP Rocket stores it locally and marks the job complete.

## Performance Hints: Above The Fold and Lazy Render Content

Code:
- `inc/Engine/Common/PerformanceHints/WarmUp/APIClient.php`
- `inc/Engine/Common/PerformanceHints/WarmUp/Controller.php`
- Related contexts under `inc/Engine/Media/AboveTheFold/` and `inc/Engine/Optimization/LazyRenderContent/`

Endpoint path:
- Uses the same `rucss-job` path.

Request difference:

```json
{
  "config": {
    "optimization_list": ["performance_hints"],
    "is_home": true,
    "is_mobile": false
  }
}
```

Expected result data is read from:

```json
{
  "contents": {
    "above_the_fold_result": {
      "lcp": [],
      "images_above_fold": []
    }
  }
}
```

Implementation notes:
- The service must identify LCP candidates and images above the fold for desktop/mobile.
- A Playwright service can capture viewport-specific DOM, image positions, and performance entries.

## Critical CSS Generation

Code:
- `inc/Engine/CriticalPath/APIClient.php`
- `inc/Engine/CriticalPath/ProcessorService.php`
- `inc/Engine/CriticalPath/CriticalCSS.php`

Endpoint:
- `https://cpcss.wp-rocket.me/api/job/`

Override constant:
- `WP_ROCKET_CPCSS_API_URL`

Generation request body:

```json
{
  "url": "https://example.com/page/",
  "mobile": 0,
  "nofontface": false
}
```

Expected generation response:

```json
{
  "status": 200,
  "data": {
    "id": "job-id"
  }
}
```

Status endpoint:
- `GET /api/job/{job-id}/`

Expected completed response:

```json
{
  "status": 200,
  "data": {
    "state": "complete",
    "critical_path": "body{...}"
  }
}
```

Implementation notes:
- Use Penthouse or a Playwright/Chrome coverage pipeline to generate viewport critical CSS.
- WP Rocket saves returned `critical_path` into `wp-content/cache/critical-css/{blog_id}/`.
- Fallback critical CSS is already local through the settings textarea, but automatic generation is remote.

## Rocket Insights Performance Monitoring

Code:
- `inc/Engine/Admin/RocketInsights/APIHandler/APIClient.php`
- `inc/Engine/Admin/RocketInsights/Jobs/Manager.php`

Endpoint path:
- `performance/`

Add-to-queue request body is JSON:

```json
{
  "email": "",
  "key": "",
  "url": "https://example.com/page/",
  "is_priority": false,
  "credentials": {}
}
```

Expected add response must include:

```json
{
  "uuid": "test-id",
  "status": "pending"
}
```

Status request body:

```json
{
  "uuid": "test-id",
  "credentials": {}
}
```

Status values are mapped as:
- `pending` -> code `425`
- `failed` -> code `500`
- any other status -> code `200`

Implementation notes:
- This service should run a Lighthouse/PageSpeed-like test and return metrics used by Rocket Insights.
- Recommendations use those stored metrics.

## Rocket Insights Recommendations

Code:
- `inc/Engine/Admin/RocketInsights/Recommendations/APIClient.php`
- `inc/Engine/Admin/RocketInsights/Recommendations/DataManager.php`

Endpoint path:
- `recommendations/`

Request parameters:
- `email`
- `lcp`
- `ttfb`
- `cls`
- `tbt`
- `global_score`
- `enabled_options`
- `language`
- `limit`
- `version`

Expected response shape:

```json
{
  "recommendations": [],
  "metadata": {
    "language": "en"
  }
}
```

Implementation notes:
- This can be implemented as a rules engine that maps metrics and enabled plugin options to recommendation cards.
- It does not need browser rendering if Rocket Insights metrics are already available.

## Dynamic Compatibility Lists

Code:
- `inc/Engine/Optimization/DynamicLists/AbstractAPIClient.php`
- `inc/Engine/Optimization/DynamicLists/DefaultLists/APIClient.php`
- `inc/Engine/Optimization/DynamicLists/DelayJSLists/APIClient.php`
- `inc/Engine/Optimization/DynamicLists/IncompatiblePluginsLists/APIClient.php`

Default base URL:
- `https://b.rucss.wp-rocket.me/api/v2/`

Override constant:
- `WP_ROCKET_EXCLUSIONS_API_URL`

Endpoint paths:
- `exclusions/list`
- `delay-js-exclusions/list`
- `incompatible-plugins/list`

Request body:

```json
{
  "hash": "current-list-hash",
  "credentials": {}
}
```

Expected HTTP status:
- `200` for full list
- `206` for partial/delta response

Implementation notes:
- This is not page-specific optimization, but it supplies compatibility data for RUCSS, Delay JS, and plugin conflicts.
- A replacement can serve static JSON generated from the committed `dynamic-lists*.json` files.

## Product and Account Remote APIs

These APIs are remote dependencies but are not optimization engines. In this fork, license validation and user data have been bypassed locally, but some classes still exist.

- User data: `inc/Engine/License/API/UserClient.php`, original endpoint `https://api.wp-rocket.me/stat/1.0/wp-rocket/user.php`
- Pricing: `inc/Engine/License/API/PricingClient.php`, endpoint `https://api.wp-rocket.me/stat/1.0/wp-rocket/pricing-2023.php`, override `WP_ROCKET_PRICING_API_URL`
- Remote settings: `inc/Engine/License/API/RemoteSettingsClient.php`, endpoint `https://api.wp-rocket.me/api/wp-rocket/plugin-settings.php`, override `WP_ROCKET_REMOTE_SETTINGS_API_URL`
- Plugin updates: `inc/Engine/Plugin/UpdaterSubscriber.php`, endpoint `https://api.wp-rocket.me/check_update.php`, override `WP_ROCKET_UPDATE_API_URL`
- Plugin information: `inc/Engine/Plugin/InformationSubscriber.php`, endpoint `https://api.wp-rocket.me/plugin_information.php`, override `WP_ROCKET_PLUGIN_INFORMATION_API_URL`
- Plugin package downloads and rollback: base URL `https://api.wp-rocket.me/`, override `WP_ROCKET_PACKAGE_API_URL`
- RocketCDN API: `inc/Engine/CDN/RocketCDN/APIClient.php`, endpoint `https://rocketcdn.me/api/`, override `WP_ROCKET_ROCKETCDN_API_URL`
- RocketCDN iframe: `inc/Engine/CDN/RocketCDN/AdminPageSubscriber.php`, endpoint `https://api.wp-rocket.me/cdn/iframe`, override `WP_ROCKET_CDN_IFRAME_URL`

These can be disabled, stubbed, or redirected depending on whether the fork should provide its own update/CDN/account experience.

## Suggested Development Order

1. Implement a local/hosted Chromium service for Critical CSS first; the contract is smaller.
2. Implement RUCSS next; it has the highest performance impact and more complex CSS extraction.
3. Add Performance Hints using the same browser worker.
4. Replace Rocket Insights with a Lighthouse-compatible metrics worker if performance monitoring is needed.
5. Serve Dynamic Compatibility Lists from static JSON while the optimization services are being built.
