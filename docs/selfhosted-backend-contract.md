# Self-Hosted Optimization Backend Contract

This document is the implementation contract for a separate Node.js backend that replaces WP Rocket remote services for this fork.

The WordPress plugin remains the client/orchestrator. The backend owns queues, browser rendering, CSS extraction, critical CSS generation, performance audits, static compatibility lists, and optional account/update/CDN replacement APIs.

## Recommended Backend Repository

Recommended separate repository name:

```text
wp-rocket-optimization-backend/
```

Recommended structure:

```text
wp-rocket-optimization-backend/
  src/
    app.ts
    server.ts
    config/
      env.ts
      logger.ts
    http/
      plugins/
        form-body.ts
        auth.ts
      routes/
        health.ts
        rucss.ts
        cpcss.ts
        performance.ts
        recommendations.ts
        dynamic-lists.ts
        product-api.ts
        rocketcdn.ts
    contracts/
      rucss.ts
      cpcss.ts
      performance.ts
      recommendations.ts
      dynamic-lists.ts
    queues/
      connection.ts
      names.ts
      producers.ts
    workers/
      index.ts
      rucss-worker.ts
      cpcss-worker.ts
      performance-worker.ts
    services/
      browser/
        playwright.ts
        page-loader.ts
      css/
        critical-css.ts
        used-css.ts
      hints/
        above-the-fold.ts
      lighthouse/
        audit.ts
      recommendations/
        rules.ts
      dynamic-lists/
        lists.ts
    storage/
      job-store.ts
      redis-job-store.ts
      memory-job-store.ts
    fixtures/
      dynamic-lists.json
      dynamic-lists-delayjs.json
      dynamic-lists-incompatible-plugins.json
  tests/
    contract/
      rucss.test.ts
      cpcss.test.ts
      performance.test.ts
      recommendations.test.ts
      dynamic-lists.test.ts
  Dockerfile
  docker-compose.yml
  package.json
  README.md
  .env.example
```

Recommended stack:

- Node.js with TypeScript.
- Fastify.
- `@fastify/formbody` because most plugin requests are form-style WordPress HTTP API bodies.
- Zod or TypeBox for validation.
- BullMQ plus Redis for queued jobs.
- Playwright/Chromium for rendering.
- PostCSS or css-tree for CSS parsing.
- Lighthouse only for Rocket Insights if you want performance monitoring.

## Plugin Configuration

The plugin uses constants to redirect remote calls:

```php
define( 'WP_ROCKET_SAAS_API_URL', 'http://localhost:8080/' );
define( 'WP_ROCKET_CPCSS_API_URL', 'http://localhost:8080/api/job/' );
define( 'WP_ROCKET_EXCLUSIONS_API_URL', 'http://localhost:8080/api/v2/' );

// Optional product/account/CDN replacement endpoints:
define( 'WP_ROCKET_PRICING_API_URL', 'http://localhost:8080/stat/1.0/wp-rocket/pricing-2023.php' );
define( 'WP_ROCKET_REMOTE_SETTINGS_API_URL', 'http://localhost:8080/api/wp-rocket/plugin-settings.php' );
define( 'WP_ROCKET_UPDATE_API_URL', 'http://localhost:8080/check_update.php' );
define( 'WP_ROCKET_PLUGIN_INFORMATION_API_URL', 'http://localhost:8080/plugin_information.php' );
define( 'WP_ROCKET_PACKAGE_API_URL', 'http://localhost:8080/packages/' );
define( 'WP_ROCKET_ROCKETCDN_API_URL', 'http://localhost:8080/rocketcdn/api/' );
define( 'WP_ROCKET_CDN_IFRAME_URL', 'http://localhost:8080/cdn/iframe' );
```

Important:

- `WP_ROCKET_SAAS_API_URL` must include a trailing slash because the plugin concatenates it with paths like `rucss-job`.
- `WP_ROCKET_EXCLUSIONS_API_URL` must include a trailing slash because the plugin concatenates it with paths like `exclusions/list`.
- `WP_ROCKET_CPCSS_API_URL` must point directly to the job collection URL and include a trailing slash, for example `/api/job/`.
- The backend should accept `application/x-www-form-urlencoded`, JSON, and GET bodies where noted. WordPress' HTTP API often sends a `body` array even for GET requests.

## Shared Credentials

Most optimization API calls receive credentials automatically added by the plugin:

```json
{
  "credentials": {
    "wpr_email": "customer@example.com",
    "wpr_key": "license-or-local-key"
  }
}
```

PHP form encoding may arrive as:

```text
credentials[wpr_email]=customer%40example.com
credentials[wpr_key]=license-or-local-key
```

The self-hosted backend can ignore credentials at first, but it should parse them so hosted deployments can authenticate later.

## Health Endpoint

This endpoint is not currently called by the plugin, but it should exist for Docker and future admin UI connection tests.

```http
GET /health
```

Response:

```json
{
  "ok": true,
  "version": "0.1.0",
  "workers": {
    "rucss": true,
    "cpcss": true,
    "performance": true
  }
}
```

## Remove Unused CSS

Plugin code:

- `inc/Engine/Optimization/RUCSS/APIHandler/APIClient.php`
- `inc/Engine/Optimization/RUCSS/Jobs/Manager.php`
- `inc/Engine/Common/JobManager/JobProcessor.php`

Base URL constant:

```php
WP_ROCKET_SAAS_API_URL
```

Endpoint path:

```text
rucss-job
```

### Add RUCSS Job

```http
POST /rucss-job
Content-Type: application/x-www-form-urlencoded
```

The plugin sends the URL with `nowprocket=1&no_optimize=1` appended.

Request body:

```json
{
  "url": "https://example.com/page/?nowprocket=1&no_optimize=1",
  "config": {
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

The backend may also receive `config[optimization_list][0]=rucss` style form fields, depending on the HTTP parser. Normalize nested form values before validation.

Accepted HTTP status:

- `200`
- `201`

Required response body:

```json
{
  "code": 200,
  "message": "queued",
  "contents": {
    "jobId": "rucss_abc123",
    "queueName": "rucss"
  }
}
```

Plugin validation requires:

- `contents`
- `contents.jobId`
- `contents.queueName`

Recommended TypeScript schema:

```ts
type RucssAddResponse = {
  code: number;
  message?: string;
  contents: {
    jobId: string;
    queueName: string;
  };
};
```

### Get RUCSS Job Status

```http
GET /rucss-job
Content-Type: application/x-www-form-urlencoded
```

Request body:

```json
{
  "id": "rucss_abc123",
  "force_queue": "rucss",
  "is_home": false,
  "credentials": {
    "wpr_email": "",
    "wpr_key": ""
  }
}
```

Accepted HTTP status:

- `200`
- `201`

Important: the plugin expects the real job payload under `returnvalue`. If `returnvalue` is missing, the plugin defaults the job to failed.

Completed response:

```json
{
  "code": 200,
  "returnvalue": {
    "code": 200,
    "status": "completed",
    "message": "completed",
    "contents": {
      "success": true,
      "shakedCSS": "body{color:#111}",
      "shakedCSS_size": 16,
      "above_the_fold_result": {
        "lcp": [],
        "images_above_fold": []
      }
    }
  }
}
```

Pending response:

```json
{
  "code": 200,
  "returnvalue": {
    "code": 202,
    "status": "pending",
    "message": "Job is still running",
    "contents": {
      "success": false,
      "shakedCSS": "",
      "shakedCSS_size": 0,
      "above_the_fold_result": {
        "lcp": [],
        "images_above_fold": []
      }
    }
  }
}
```

Failed response:

```json
{
  "code": 200,
  "returnvalue": {
    "code": 500,
    "status": "failed",
    "message": "Unable to render page",
    "contents": {
      "success": false,
      "shakedCSS": "",
      "shakedCSS_size": 0,
      "above_the_fold_result": {
        "lcp": [],
        "images_above_fold": []
      }
    }
  }
}
```

Required completed fields:

- `returnvalue.code` must be `200` for the plugin to process the result.
- `returnvalue.contents.shakedCSS` must be a string.
- `returnvalue.contents.shakedCSS_size` should be an integer. The plugin can fail jobs where this is lower than the `rocket_min_rucss_size` filter value, default `150`.
- `returnvalue.contents.above_the_fold_result` should always exist because Performance Hints reuses the same endpoint.

Recommended TypeScript schema:

```ts
type RucssStatusResponse = {
  code: number;
  returnvalue: {
    code: 200 | 202 | 500 | number;
    status: "completed" | "pending" | "failed" | string;
    message?: string;
    contents: {
      success: boolean;
      shakedCSS: string;
      shakedCSS_size?: number;
      above_the_fold_result: {
        lcp: unknown[];
        images_above_fold: unknown[];
      };
    };
  };
};
```

## Performance Hints: Above The Fold and Lazy Render Content

Plugin code:

- `inc/Engine/Common/PerformanceHints/WarmUp/APIClient.php`
- `inc/Engine/Common/PerformanceHints/WarmUp/Controller.php`
- `inc/Engine/Media/AboveTheFold/`
- `inc/Engine/Optimization/LazyRenderContent/`

This feature uses the same `/rucss-job` endpoint as RUCSS.

### Add Performance Hints Job

```http
POST /rucss-job
Content-Type: application/x-www-form-urlencoded
```

Request body:

```json
{
  "url": "https://example.com/page/?nowprocket=1&no_optimize=1",
  "config": {
    "optimization_list": ["performance_hints"],
    "is_home": true,
    "is_mobile": false
  },
  "credentials": {
    "wpr_email": "",
    "wpr_key": ""
  }
}
```

Response shape is identical to Add RUCSS Job:

```json
{
  "code": 200,
  "message": "queued",
  "contents": {
    "jobId": "hints_abc123",
    "queueName": "performance_hints"
  }
}
```

### Get Performance Hints Job Status

```http
GET /rucss-job
```

Request body:

```json
{
  "id": "hints_abc123",
  "force_queue": "performance_hints",
  "is_home": true,
  "credentials": {
    "wpr_email": "",
    "wpr_key": ""
  }
}
```

Completed response:

```json
{
  "code": 200,
  "returnvalue": {
    "code": 200,
    "status": "completed",
    "message": "completed",
    "contents": {
      "success": true,
      "shakedCSS": "",
      "shakedCSS_size": 0,
      "above_the_fold_result": {
        "lcp": [
          {
            "selector": "img.hero",
            "src": "https://example.com/wp-content/uploads/hero.jpg",
            "tag": "img"
          }
        ],
        "images_above_fold": [
          {
            "selector": "img.logo",
            "src": "https://example.com/wp-content/uploads/logo.png"
          }
        ]
      }
    }
  }
}
```

The plugin reads `contents.above_the_fold_result.lcp` and `contents.above_the_fold_result.images_above_fold`. Keep the arrays present even if empty.

## Critical CSS

Plugin code:

- `inc/Engine/CriticalPath/APIClient.php`
- `inc/Engine/CriticalPath/ProcessorService.php`
- `inc/Engine/CriticalPath/CriticalCSS.php`

Base URL constant:

```php
WP_ROCKET_CPCSS_API_URL
```

Recommended backend path:

```text
/api/job/
```

### Add Critical CSS Job

```http
POST /api/job/
Content-Type: application/x-www-form-urlencoded
```

Request body:

```json
{
  "url": "https://example.com/page/",
  "mobile": 0,
  "nofontface": false
}
```

`mobile` is sent as integer-like `0` or `1`.

Accepted HTTP status:

- The plugin calls `wp_remote_post()` and treats the response as successful only when the HTTP response code is `200` and the decoded JSON has `status: 200` plus `data.id`.

Required response:

```json
{
  "status": 200,
  "data": {
    "id": "cpcss_abc123"
  }
}
```

Recommended TypeScript schema:

```ts
type CpcssAddResponse = {
  status: 200;
  data: {
    id: string;
  };
};
```

### Get Critical CSS Job Status

```http
GET /api/job/:jobId/
```

The plugin sends no request body.

Completed response:

```json
{
  "status": 200,
  "data": {
    "state": "complete",
    "critical_path": "body{color:#111}"
  }
}
```

Pending response:

```json
{
  "status": 200,
  "data": {
    "state": "pending"
  }
}
```

Failed response:

```json
{
  "status": 400,
  "message": "Unable to generate critical CSS",
  "data": {
    "state": "failed"
  }
}
```

Important behavior:

- `status` is a JSON field, not only the HTTP status.
- The plugin's `ProcessorService` considers `data.state !== "complete"` as pending when JSON `status` is `200`.
- The plugin writes `data.critical_path` to the critical CSS cache only when `data.state` is `"complete"` and `data.critical_path` exists.
- Keep HTTP status `200` for valid pending and completed payloads. Use JSON `status` to express job state.

Recommended TypeScript schema:

```ts
type CpcssStatusResponse = {
  status: 200 | 400 | 500 | number;
  message?: string;
  data: {
    state: "pending" | "complete" | "failed" | string;
    critical_path?: string;
  };
};
```

## Rocket Insights Performance Monitoring

Plugin code:

- `inc/Engine/Admin/RocketInsights/APIHandler/APIClient.php`
- `inc/Engine/Admin/RocketInsights/Jobs/Manager.php`

Base URL constant:

```php
WP_ROCKET_SAAS_API_URL
```

Endpoint path:

```text
performance/
```

### Add Performance Test

```http
POST /performance/
Content-Type: application/json
```

Request body:

```json
{
  "email": "customer@example.com",
  "key": "license-or-local-key",
  "url": "https://example.com/page/",
  "is_priority": false,
  "credentials": {
    "wpr_email": "customer@example.com",
    "wpr_key": "license-or-local-key"
  }
}
```

Accepted HTTP status:

- `200`
- `201`

Required response:

```json
{
  "uuid": "perf_abc123",
  "status": "pending"
}
```

Plugin validation requires `uuid`.

The plugin adds an internal `code` field after decoding based on HTTP status, so the backend does not need to include `code`.

Recommended TypeScript schema:

```ts
type PerformanceAddResponse = {
  uuid: string;
  status?: "pending" | "completed" | "failed" | string;
};
```

### Get Performance Test Status

```http
GET /performance/
Content-Type: application/x-www-form-urlencoded
```

Request body:

```json
{
  "uuid": "perf_abc123",
  "credentials": {
    "wpr_email": "customer@example.com",
    "wpr_key": "license-or-local-key"
  }
}
```

Accepted HTTP status:

- `200`
- `201`

Pending response:

```json
{
  "uuid": "perf_abc123",
  "status": "pending"
}
```

Failed response:

```json
{
  "uuid": "perf_abc123",
  "status": "failed",
  "message": "Audit failed"
}
```

Completed response:

```json
{
  "uuid": "perf_abc123",
  "status": "completed",
  "data": {
    "data": {
      "report_url": "https://backend.example.com/reports/perf_abc123",
      "performance_score": 87,
      "largest_contentful_paint": {
        "value": 2200
      },
      "total_blocking_time": {
        "value": 120
      },
      "cumulative_layout_shift": {
        "value": 0.03
      },
      "time_to_first_byte": {
        "value": 350
      }
    }
  }
}
```

Important behavior:

- The plugin maps body `status: "pending"` to internal code `425`.
- The plugin maps body `status: "failed"` to internal code `500`.
- The plugin maps any other status to internal code `200`.
- The plugin stores completed metric data from `response.data.data`.
- Include `data.data.report_url` and `data.data.performance_score` if possible. Missing values default to empty string and `0`.

Recommended TypeScript schema:

```ts
type PerformanceStatusResponse = {
  uuid?: string;
  status: "pending" | "completed" | "failed" | string;
  message?: string;
  data?: {
    data?: {
      report_url?: string;
      performance_score?: number;
      largest_contentful_paint?: { value: number; [key: string]: unknown };
      total_blocking_time?: { value: number; [key: string]: unknown };
      cumulative_layout_shift?: { value: number; [key: string]: unknown };
      time_to_first_byte?: { value: number; [key: string]: unknown };
      [key: string]: unknown;
    };
  };
};
```

## Rocket Insights Recommendations

Plugin code:

- `inc/Engine/Admin/RocketInsights/Recommendations/APIClient.php`
- `inc/Engine/Admin/RocketInsights/Recommendations/DataManager.php`
- `inc/Engine/Admin/RocketInsights/Recommendations/Render.php`

Base URL constant:

```php
WP_ROCKET_SAAS_API_URL
```

Endpoint path:

```text
recommendations/
```

### Get Recommendations

```http
GET /recommendations/
Content-Type: application/x-www-form-urlencoded
```

Request body:

```json
{
  "email": "customer@example.com",
  "lcp": 3.2,
  "ttfb": 0.8,
  "cls": 0.15,
  "tbt": 350,
  "global_score": 65,
  "enabled_options": ["delay_js", "lazyload"],
  "language": "en",
  "limit": 20,
  "version": "3.20.5",
  "credentials": {
    "wpr_email": "customer@example.com",
    "wpr_key": "license-or-local-key"
  }
}
```

Only `email` is required by the plugin before it sends the request. The backend should accept any subset of the other fields.

Accepted HTTP status:

- `200`
- `201`

Required response:

```json
{
  "recommendations": [
    {
      "option_slug": "delay_js",
      "priority": 10,
      "title": "Enable Delay JavaScript Execution",
      "description": "Delay non-critical JavaScript to improve Total Blocking Time.",
      "learn_more_url": "https://docs.example.com/delay-js",
      "icon_slug": "delay_js",
      "lcp_impact": "medium",
      "ttfb_impact": null,
      "cls_impact": null,
      "tbt_impact": "high"
    }
  ],
  "metadata": {
    "language": "en",
    "total_recommendations": 1
  }
}
```

Plugin validation requires:

- `recommendations` as an array.
- `metadata` as an object/array.

The renderer expects each recommendation to include:

- `option_slug`
- `title`

Optional but supported:

- `description`
- `learn_more_url`
- `icon_slug`
- `priority`
- `lcp_impact`
- `ttfb_impact`
- `cls_impact`
- `tbt_impact`

Recommended TypeScript schema:

```ts
type Recommendation = {
  option_slug: string;
  title: string;
  description?: string;
  learn_more_url?: string;
  icon_slug?: string;
  priority?: number | string;
  lcp_impact?: string | number | null;
  ttfb_impact?: string | number | null;
  cls_impact?: string | number | null;
  tbt_impact?: string | number | null;
};

type RecommendationsResponse = {
  recommendations: Recommendation[];
  metadata: {
    language?: string;
    total_recommendations?: number;
    [key: string]: unknown;
  };
};
```

## Dynamic Compatibility Lists

Plugin code:

- `inc/Engine/Optimization/DynamicLists/AbstractAPIClient.php`
- `inc/Engine/Optimization/DynamicLists/DynamicLists.php`
- `inc/Engine/Optimization/DynamicLists/DefaultLists/APIClient.php`
- `inc/Engine/Optimization/DynamicLists/DelayJSLists/APIClient.php`
- `inc/Engine/Optimization/DynamicLists/IncompatiblePluginsLists/APIClient.php`

Base URL constant:

```php
WP_ROCKET_EXCLUSIONS_API_URL
```

Recommended backend base path:

```text
/api/v2/
```

The plugin uses `GET` with a body. The backend should also accept `hash` from the query string to make manual testing easier.

Accepted HTTP status:

- `200`: full list body returned and saved.
- `206`: lists are up to date; body still should not be empty because the plugin treats an empty body as failure before checking `206`.

Shared request body:

```json
{
  "hash": "md5-of-current-local-json",
  "credentials": {
    "wpr_email": "customer@example.com",
    "wpr_key": "license-or-local-key"
  }
}
```

### Default Lists

```http
GET /api/v2/exclusions/list
```

If the hash differs, return HTTP `200` with the full JSON content of `dynamic-lists.json`.

Response body example:

```json
{
  "rucss_inline_atts_exclusions": [],
  "rucss_inline_content_exclusions": [],
  "defer_js_inline_exclusions": [],
  "defer_js_external_exclusions": [],
  "delay_js_exclusions": [],
  "js_minify_external": [],
  "cache_ignored_parameters": [],
  "preload_exclusions": [],
  "exclude_js_files": [],
  "staging_domains": [],
  "lazy_rendering_exclusions": [],
  "host_fonts": [],
  "preload_fonts_exclusions": [],
  "preconnect_external_domains_exclusions": [],
  "mixpanel_tracked_settings": [],
  "rocket_insights_add_homepage_expiry_interval": 266
}
```

### Delay JS Lists

```http
GET /api/v2/delay-js-exclusions/list
```

If the hash differs, return HTTP `200` with the full JSON content of `dynamic-lists-delayjs.json`.

Response body shape:

```json
{
  "plugins": {
    "uuid": {
      "title": "Plugin Name",
      "condition": "plugin-folder/plugin.php",
      "exclusions": ["pattern"],
      "icon_url": "",
      "summary": "",
      "type": "plugin",
      "id": "plugin:hash",
      "is_default": 0,
      "created_at": 1724424735
    }
  },
  "scripts": {
    "analytics": {
      "uuid": {
        "title": "Google Analytics",
        "exclusions": ["google-analytics.com/analytics.js"],
        "type": "script",
        "category": "analytics",
        "id": "script:hash",
        "is_default": 0,
        "created_at": 1734096382
      }
    }
  }
}
```

### Incompatible Plugins Lists

```http
GET /api/v2/incompatible-plugins/list
```

If the hash differs, return HTTP `200` with the full JSON content of `dynamic-lists-incompatible-plugins.json`.

Response body shape:

```json
{
  "": [
    {
      "slug": "wp-super-cache",
      "file": "wp-super-cache/wp-cache.php"
    }
  ],
  "minify_css||minify_js": [
    {
      "slug": "fast-velocity-minify",
      "file": "fast-velocity-minify/fvm.php"
    }
  ]
}
```

### Unchanged Dynamic List Response

When the submitted `hash` matches the backend's current JSON:

```http
HTTP/1.1 206 Partial Content
Content-Type: application/json
```

Body:

```json
{
  "message": "Lists are up to date"
}
```

Do not return an empty body for `206`.

## Product and Account APIs

These are not browser optimization services. In this fork, user/license data is already local in `UserClient`, but the classes still exist for pricing, remote settings, updates, plugin information, packages, and CDN.

You can omit these from the first optimization backend milestone if the plugin fork disables or stubs the related UI. If you implement them, use the contracts below.

## Pricing API

Plugin code:

- `inc/Engine/License/API/PricingClient.php`

Constant:

```php
WP_ROCKET_PRICING_API_URL
```

Endpoint:

```http
GET /stat/1.0/wp-rocket/pricing-2023.php
```

Accepted HTTP status:

- `200`
- `202`

Response body:

```json
{
  "single": {
    "price": 59,
    "currency": "USD"
  },
  "plus": {
    "price": 119,
    "currency": "USD"
  },
  "infinite": {
    "price": 299,
    "currency": "USD"
  }
}
```

The plugin only requires valid non-empty JSON. UI code may expect specific pricing keys depending on the rendered screen.

## Remote Settings API

Plugin code:

- `inc/Engine/License/API/RemoteSettingsClient.php`

Constant:

```php
WP_ROCKET_REMOTE_SETTINGS_API_URL
```

Endpoint:

```http
POST /api/wp-rocket/plugin-settings.php
Content-Type: application/x-www-form-urlencoded
```

Request body:

```json
{
  "key": "license-or-local-key",
  "email": "customer@example.com",
  "domain": "example.com",
  "wp_rocket_version": "3.20.5"
}
```

Accepted HTTP status:

- `200`
- `202`

Required response:

```json
{
  "success": true,
  "data": {
    "settings": {},
    "features": {}
  }
}
```

Important: the plugin returns false if either `success` or `data` is empty.

## Plugin Update API

Plugin code:

- `inc/Engine/Plugin/UpdaterSubscriber.php`

Constant:

```php
WP_ROCKET_UPDATE_API_URL
```

Endpoint:

```http
GET /check_update.php
```

Accepted HTTP status:

- `200`

Response is plain text, not JSON:

```text
3.21.0|https://backend.example.com/packages/local-key/wp-rocket_3.21.0.zip|3.21.0
```

Format:

```text
{stable_version}|{package_zip_url_or_empty}|{user_version}
```

The plugin parses this with:

```text
stable_version: digits/dots plus optional suffix
package: optional http URL ending in .zip
user_version: digits/dots plus optional suffix
```

Examples:

```text
3.21.0||3.21.0
3.21.0|https://backend.example.com/packages/key/wp-rocket_3.21.0.zip|3.21.0
```

## Plugin Information API

Plugin code:

- `inc/Engine/Plugin/InformationSubscriber.php`

Constant:

```php
WP_ROCKET_PLUGIN_INFORMATION_API_URL
```

Endpoint:

```http
GET /plugin_information.php
```

Accepted HTTP status:

- `200`

The plugin calls `maybe_unserialize()` on the response body and accepts either an object or an array. For best compatibility with the current code, return PHP serialized data.

Example serialized response:

```text
O:8:"stdClass":6:{s:4:"name";s:9:"WP Rocket";s:4:"slug";s:9:"wp-rocket";s:7:"version";s:6:"3.21.0";s:6:"tested";s:3:"6.8";s:8:"homepage";s:21:"https://wp-rocket.me";s:8:"sections";a:1:{s:11:"description";s:9:"WP Rocket";}}
```

Equivalent PHP object:

```php
(object) [
	'name'     => 'WP Rocket',
	'slug'     => 'wp-rocket',
	'version'  => '3.21.0',
	'tested'   => '6.8',
	'homepage' => 'https://wp-rocket.me',
	'sections' => [
		'description' => 'WP Rocket',
	],
]
```

## Package Downloads and Rollback

Plugin code:

- `inc/Engine/Plugin/UpdaterSubscriber.php`

Constant:

```php
WP_ROCKET_PACKAGE_API_URL
```

URL format generated by the plugin:

```text
{WP_ROCKET_PACKAGE_API_URL}/{consumer_key}/wp-rocket_{version}.zip
```

Example:

```http
GET /packages/local-key/wp-rocket_3.21.0.zip
```

Response:

- HTTP `200`
- `Content-Type: application/zip`
- Body: installable WP Rocket zip.

## RocketCDN API

Plugin code:

- `inc/Engine/CDN/RocketCDN/APIClient.php`
- `inc/Engine/CDN/RocketCDN/AdminPageSubscriber.php`

Constants:

```php
WP_ROCKET_ROCKETCDN_API_URL
WP_ROCKET_CDN_IFRAME_URL
```

RocketCDN can be disabled or replaced independently from optimization features.

### Get Subscription

```http
GET /rocketcdn/api/website/search/?url=https://example.com
Authorization: Token {rocketcdn_user_token}
```

Accepted HTTP status:

- `200`

Response body:

```json
{
  "id": 123,
  "is_active": true,
  "cdn_url": "https://cdn.example.com",
  "subscription_next_date_update": 1767225600,
  "subscription_status": "active"
}
```

The plugin merges this with defaults:

```json
{
  "id": 0,
  "is_active": false,
  "cdn_url": "",
  "subscription_next_date_update": 0,
  "subscription_status": "cancelled"
}
```

### Get Pricing

```http
GET /rocketcdn/api/pricing
```

Accepted HTTP status:

- `200`

Response body:

```json
{
  "price": 7.99,
  "currency": "USD",
  "interval": "month"
}
```

The plugin stores the decoded JSON as-is.

### Activate Subscription

```http
PATCH /rocketcdn/api/website/:websiteId/
Authorization: Token {rocketcdn_user_token}
Content-Type: application/json
```

Request body:

```json
{
  "is_active": true
}
```

Success response:

- HTTP `200`

Body can be empty or JSON. The plugin only checks the HTTP response code.

### Purge CDN Cache

```http
DELETE /rocketcdn/api/website/:websiteId/purge/
Authorization: Token {rocketcdn_user_token}
```

Success response:

```json
{
  "success": true
}
```

Failure response:

```json
{
  "success": false,
  "message": "Purge failed"
}
```

The plugin requires a non-empty body with a `success` field.

### CDN Iframe

```http
GET /cdn/iframe?website=https://example.com&callback=https://example.com/wp-json/wp-rocket/v1/rocketcdn/&source=plugin
```

Response:

- HTML page suitable for iframe display.

The iframe flow is optional for the self-hosted backend unless you are replacing the RocketCDN onboarding UI.

## Implementation Milestones

1. Build the backend repository with Fastify, TypeScript, form-body parsing, health check, and in-memory job storage.
2. Implement stubbed contract-compatible responses for `/rucss-job`, `/api/job/`, `/performance/`, `/recommendations/`, and `/api/v2/*`.
3. Point the plugin constants at the local backend and verify that jobs queue and complete with fake data.
4. Replace Critical CSS stubs with a real Playwright/Penthouse worker.
5. Replace RUCSS stubs with a real Playwright CSS coverage worker.
6. Reuse the browser worker for Performance Hints.
7. Add Lighthouse-backed Rocket Insights if performance monitoring is required.
8. Add product/account/CDN endpoints only if this fork keeps those UI surfaces enabled.

## Minimum Contract Test Matrix

The backend repository should include contract tests for:

- `POST /rucss-job` accepts form body and returns `contents.jobId` plus `contents.queueName`.
- `GET /rucss-job` returns a `returnvalue` wrapper.
- RUCSS completed response includes `contents.shakedCSS`.
- Performance Hints completed response includes `contents.above_the_fold_result.lcp` and `images_above_fold`.
- `POST /api/job/` returns JSON `status: 200` and `data.id`.
- `GET /api/job/:id/` pending response has JSON `status: 200` and `data.state !== "complete"`.
- `GET /api/job/:id/` completed response has `data.state: "complete"` and `data.critical_path`.
- `POST /performance/` accepts JSON and returns `uuid`.
- `GET /performance/` pending response has `status: "pending"`.
- `GET /performance/` completed response has `data.data`.
- `GET /recommendations/` returns `recommendations` array and `metadata` object.
- Dynamic list endpoints return non-empty body for both HTTP `200` and `206`.

