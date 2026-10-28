<?php

return [
	'testShouldReturnDefaultWhenBypass' => [
		'config' => [
			'context_not_called' => true,
			'nowprocket' => 1,
		],
		'html' => '<html><head></head><body></body></html>',
		'expected' => '<html><head></head><body></body></html>',
	],
	'testShouldReturnDefaultWhenDNRO' => [
		'config' => [
			'context_not_called' => true,
			'donotrocketoptimize' => 1,
		],
		'html' => '<html><head></head><body></body></html>',
		'expected' => '<html><head></head><body></body></html>',
	],
	'testShouldReturnDefaultWhenWL' => [
		'config' => [
			'context_not_called' => true,
			'white_label_footprint' => 1,
		],
		'html' => '<html><head></head><body></body></html>',
		'expected' => '<html><head></head><body></body></html>',
	],
	'testShouldReturnDefaultWhenFilterDisabled' => [
		'config' => [
			'context_not_called' => true,
			'disable_meta' => true,
		],
		'html' => '<html><head></head><body></body></html>',
		'expected' => '<html><head></head><body></body></html>',
	],
	'testShouldReturnDefaultWhenLoggedIn' => [
		'config' => [
			'context_not_called' => true,
			'is_user_logged_in' => true,
		],
		'html' => '<html><head></head><body></body></html>',
		'expected' => '<html><head></head><body></body></html>',
	],
	'testShouldReturnDefaultWhenNoFeatures' => [
		'config' => [
			'disable_meta' => false,
			'cdn' => 0,
			'do_caching_mobile_files' => 0,
			'preload_links' => 0,
			'host_fonts_locally' => 0,
		],
		'html' => '<html><head></head><body></body></html>',
		'expected' => '<html><head></head><body></body></html>',
	],
	'testShouldReturnAddMeta' => [
		'config' => [
			'disable_meta' => false,
			'cdn' => 0,
			'do_caching_mobile_files' => 1,
			'preload_links' => 1,
			'is_mobile' => true,
			'host_fonts_locally' => 1
		],
		'html' => '<html><head></head><body></body></html><!-- wpr_remove_unused_css -->',
		'expected' => '<html><head><meta name="generator" content="WP Rocket 3.17" data-wpr-features="wpr_remove_unused_css wpr_preload_links wpr_host_fonts_locally wpr_mobile" /></head><body></body></html>',
	],
	'testShouldAddInsightsOnlyWhenNoOtherFeatures' => [
		'config' => [
			'disable_meta' => false,
			'cdn' => 0,
			'do_caching_mobile_files' => 0,
			'preload_links' => 0,
			'host_fonts_locally' => 0,
			'insights_available' => true,
		],
		'html' => '<html><head></head><body></body></html>',
		'expected' => '<html><head><meta name="generator" content="WP Rocket 3.17" data-wpr-features="wpr_insights" /></head><body></body></html>',
	],
	'testShouldAppendInsightsAfterOtherFeatures' => [
		'config' => [
			'disable_meta' => false,
			'cdn' => 0,
			'do_caching_mobile_files' => 1,
			'preload_links' => 1,
			'is_mobile' => true,
			'host_fonts_locally' => 1,
			'insights_available' => true,
		],
		'html' => '<html><head></head><body></body></html><!-- wpr_remove_unused_css -->',
		'expected' => '<html><head><meta name="generator" content="WP Rocket 3.17" data-wpr-features="wpr_remove_unused_css wpr_preload_links wpr_host_fonts_locally wpr_mobile wpr_insights" /></head><body></body></html>',
	],
	'testShouldAddDesktopAndInsights' => [
		'config' => [
			'disable_meta' => false,
			'cdn' => 0,
			'do_caching_mobile_files' => 1,
			'preload_links' => 0,
			'is_mobile' => false,
			'host_fonts_locally' => 0,
			'insights_available' => true,
		],
		'html' => '<html><head></head><body></body></html>',
		'expected' => '<html><head><meta name="generator" content="WP Rocket 3.17" data-wpr-features="wpr_desktop wpr_insights" /></head><body></body></html>',
	],
	'testShouldAddMobileAndInsights' => [
		'config' => [
			'disable_meta' => false,
			'cdn' => 0,
			'do_caching_mobile_files' => 1,
			'preload_links' => 0,
			'is_mobile' => true,
			'host_fonts_locally' => 0,
			'insights_available' => true,
		],
		'html' => '<html><head></head><body></body></html>',
		'expected' => '<html><head><meta name="generator" content="WP Rocket 3.17" data-wpr-features="wpr_mobile wpr_insights" /></head><body></body></html>',
	],
	'testShouldNotAddInsightsWhenNotAvailable' => [
		'config' => [
			'disable_meta' => false,
			'cdn' => 0,
			'do_caching_mobile_files' => 0,
			'preload_links' => 0,
			'host_fonts_locally' => 0,
			'insights_available' => false,
		],
		'html' => '<html><head></head><body></body></html>',
		'expected' => '<html><head></head><body></body></html>',
	],
	'testShouldNotAddInsightsWhenNotAvailableButKeepOtherFeatures' => [
		'config' => [
			'disable_meta' => false,
			'cdn' => 0,
			'do_caching_mobile_files' => 0,
			'preload_links' => 1,
			'host_fonts_locally' => 0,
			'insights_available' => false,
		],
		'html' => '<html><head></head><body></body></html>',
		'expected' => '<html><head><meta name="generator" content="WP Rocket 3.17" data-wpr-features="wpr_preload_links" /></head><body></body></html>',
	],
	'testShouldReturnUnchangedWhenNoHeadTag' => [
		'config' => [
			'disable_meta' => false,
			'cdn' => 0,
			'do_caching_mobile_files' => 0,
			'preload_links' => 0,
			'host_fonts_locally' => 0,
			'insights_available' => true,
		],
		'html' => '<html><body></body></html><!-- wpr_remove_unused_css -->',
		'expected' => '<html><body></body></html>',
	],
	'testShouldOmitVersionWhenContentVersionFilterFalse' => [
		'config' => [
			'disable_meta' => false,
			'display_version' => false,
			'cdn' => 0,
			'do_caching_mobile_files' => 0,
			'preload_links' => 0,
			'host_fonts_locally' => 0,
			'insights_available' => true,
		],
		'html' => '<html><head></head><body></body></html>',
		'expected' => '<html><head><meta name="generator" content="WP Rocket" data-wpr-features="wpr_insights" /></head><body></body></html>',
	],
	'testShouldStripCommentsWhenLoggedIn' => [
		'config' => [
			'context_not_called' => true,
			'is_user_logged_in' => true,
		],
		'html' => '<html><head></head><body></body></html><!-- wpr_remove_unused_css -->',
		'expected' => '<html><head></head><body></body></html>',
	],
	'testShouldStripCommentsWhenWhiteLabel' => [
		'config' => [
			'context_not_called' => true,
			'white_label_footprint' => 1,
		],
		'html' => '<html><head></head><body></body></html><!-- wpr_remove_unused_css -->',
		'expected' => '<html><head></head><body></body></html>',
	],
];
