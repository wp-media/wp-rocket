<?php

// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet

$original = [
	'do_beta'                     => 0,
	'cache_logged_user'           => 0,
	'cache_ssl'                   => 0,
	'cache_mobile'                => 0,
	'do_caching_mobile_files'     => 0,
	'minify_google_fonts'         => 0,
	'minify_html'                 => 0,
	'minify_css'                  => 0,
	'minify_js'                   => 0,
	'minify_concatenate_css'      => 0,
	'minify_concatenate_js'       => 0,
	'defer_all_js'                => 1,
	'defer_all_js_safe'           => 1,
	'embeds'                      => 0,
	'emoji'                       => 0,
	'lazyload'                    => 0,
	'lazyload_iframes'            => 0,
	'lazyload_youtube'            => 0,
	'purge_cron_interval'         => 0,
	'purge_cron_unit'             => 'HOUR_IN_SECONDS',
	'remove_query_strings'        => 0,
	'dns_prefetch'                => [],
	'cache_purge_pages'           => [],
	'cache_reject_uri'            => [],
	'cache_reject_cookies'        => [],
	'cache_query_strings'         => [],
	'cache_reject_ua'             => [],
	'exclude_css'                 => [],
	'exclude_js'                  => [],
	'exclude_inline_js'           => [],
	'async_css'                   => 0,
	'critical_css'                => 'body>a{ background-color: lightblue; } h1 { color: white; text-align: center; } p { font-family: verdana; font-size: 20px; } b',
	'database_revisions'          => 0,
	'database_auto_drafts'        => 0,
	'database_trashed_posts'      => 0,
	'database_spam_comments'      => 0,
	'database_trashed_comments'   => 0,
	'database_expired_transients' => 0,
	'database_all_transients'     => 0,
	'database_optimize_tables'    => 0,
	'schedule_automatic_cleanup'  => 0,
	'automatic_cleanup_frequency' => 'weekly',
	'manual_preload'              => 0,
	'sitemap_preload'             => 0,
	'sitemaps'                    => [],
	'do_cloudflare'               => 0,
	'cloudflare_email'            => '',
	'cloudflare_api_key'          => '',
	'cloudflare_zone_id'          => '',
	'cloudflare_devmode'          => 0,
	'cloudflare_auto_settings'    => 0,
	'cloudflare_protocol_rewrite' => 0,
	'sucury_waf_cache_sync'       => 0,
	'sucury_waf_api_key'          => '',
	'control_heartbeat'           => 0,
	'heartbeat_site_behavior'     => '',
	'heartbeat_admin_behavior'    => '',
	'heartbeat_editor_behavior'   => '',
	'cdn'                         => 0,
	'cdn_cnames'                  => [],
	'cdn_zone'                    => [],
	'cdn_reject_files'            => [],
	'varnish_auto_purge'          => 0,
];

$sanitized = $original;
$with_xss                 = $original;
$with_xss['critical_css'] = '<script>alert("a");</script>' . $with_xss['critical_css'];

return [
	// Test Critical CSS with >
	[
		$original,
		$sanitized,
	],
	// Test Critical CSS with > and XSS
	[
		$with_xss,
		$sanitized,
	],
];
