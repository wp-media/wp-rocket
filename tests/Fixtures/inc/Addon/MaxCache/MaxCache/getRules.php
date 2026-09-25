<?php
// Each case lists only what it changes; the rest comes from the defaults in the test.
return [
		// The lines that tell the server a .html_gzip file is gzip-encoded HTML. The plugin writes its
		// own copy inside the rewrite section this add-on takes away, so whoever serves has to declare
		// the encoding — and Apache reads this file whether or not the write came from a request.
	'shouldDeclareTheGzipEncodingWhereTheServerReadsThisFile' => [
		'config'   => [ 'gzip' => true ],
		'expected' => [ 'contains' => [ 'AddEncoding gzip .html_gzip', 'SetEnvIfNoCase' ], 'not_contains' => [] ],
	],
		// The same write from WP-CLI or cron: there is no request to read the server from, and the
		// file is read by the same Apache as before.
	'shouldDeclareItFromAWriteWithNoRequestToJudgeBy' => [
		'config'   => [ 'gzip' => true, 'apache' => false ],
		'expected' => [ 'contains' => [ 'AddEncoding gzip .html_gzip' ], 'not_contains' => [] ],
	],
		// Behind nginx nothing reads them: the module names the encoding on the response itself.
	'shouldLeaveThemOutWhereTheServerNeverReadsThisFile' => [
		'config'   => [ 'gzip' => true, 'mode' => 'nginx' ],
		'expected' => [ 'contains' => [], 'not_contains' => [ 'AddEncoding gzip .html_gzip' ] ],
	],
	'shouldWriteNothingWhenSwitchedOff'                           => [
		'config'   => [
			'maxcache' => 0,
		],
		'expected' => [
			'contains'     => [],
			'not_contains' => [ 'MaxCache' ],
		],
	],
	'shouldDeclareIgnoredParametersWithoutAnAllowList'            => [
		'config'   => [
			'ignored_parameters' => [
				'utm_source' => 1,
				'gclid'      => 1,
			],
		],
		'expected' => [
			'contains'     => [
				'MaxCacheQSIgnoredParams utm_source gclid',
				'MaxCacheQSAllowedParams lang s permalink_name lp-variation-id',
			],
			'not_contains' => [],
		],
	],
	'shouldAddUserListedParametersToTheAlwaysCacheableOnes'       => [
		'config'   => [
			'query_strings' => [ 'currency' ],
		],
		'expected' => [
			'contains'     => [ 'MaxCacheQSAllowedParams lang s permalink_name lp-variation-id currency' ],
			'not_contains' => [ 'MaxCacheQSIgnoredParams' ],
		],
	],
		// The plugin's own list and nothing else. An entry for the agent it classifies from CloudFront
		// headers cannot match: that name exists only inside PHP, where the header is absent, and the
		// module matches the header itself. Closing that gap is the module's to do (plan §6.9).
	'shouldWriteTheAgentListWithSeparateMobileFiles'             => [
		'config'   => [
			'mobile_files' => true,
			'reject_ua'    => 'facebookexternalhit|WhatsApp',
		],
		'expected' => [
			'contains'     => [
				'MaxCacheExcludeUA "(?i)^(facebookexternalhit|WhatsApp).*"',
				'MaxCacheOptions -SkipCacheOnMobile -TabletAsMobile',
			],
			'not_contains' => [ 'CloudFront' ],
		],
	],
	'shouldKeepTheAgentListAloneWithoutSeparateMobileFiles'       => [
		'config'   => [
			'reject_ua' => 'facebookexternalhit|WhatsApp',
		],
		'expected' => [
			'contains'     => [
				'MaxCacheExcludeUA "(?i)^(facebookexternalhit|WhatsApp).*"',
				'MaxCacheOptions -SkipCacheOnMobile',
			],
			'not_contains' => [ 'CloudFront', '+SkipCacheOnMobile' ],
		],
	],
	'shouldDeclareTheSecretForTheSharedLoggedInBucket'            => [
		'config'   => [
			'cache_logged_user' => 1,
			'logged_shared'     => true,
		],
		'expected' => [
			'contains'     => [ "\tMaxCacheLoggedHash abc123" ],
			'not_contains' => [],
		],
	],
		// Per-user buckets: the module is not given those visitors, so nothing names a bucket and the
		// login cookie stays where the plugin put it — their requests reach PHP, everyone else is
		// served.
	'shouldLeaveThePerUserBucketToPhpAndKeepServingEveryoneElse'  => [
		'config'   => [
			'cache_logged_user' => 1,
			'reject_cookies'    => 'wordpress_logged_in_.+|wp-postpass_',
		],
		'expected' => [
			'contains'     => [ "\tMaxCacheExcludeCookie \"(?i)(wordpress_logged_in_.+|wp-postpass_)\"", "\tMaxCache On" ],
			'not_contains' => [ 'MaxCacheLoggedHash', 'USER_SHARED_SUFFIX' ],
		],
	],
		// The plugin writes !^(...)$; unanchored, "/cart" would also exclude "/blog/cart-tips".
	'shouldAnchorTheUriExclusionAsThePluginDoes'                  => [
		'config'   => [
			'reject_uri' => '/cart|/checkout',
		],
		'expected' => [
			'contains'     => [ "\tMaxCacheExcludeURI \"(?i)^(/cart|/checkout|/wp\\-content/(.*)|/wp\\-includes/(.*))$\"" ],
			'not_contains' => [],
		],
	],
		// The plugin's own list always carries the login cookie and it takes it back out when it
		// caches logged-in visitors; handing the raw list over would exclude exactly them.
	'shouldNotExcludeTheVisitorsWhoseBucketItJustDescribed'       => [
		'config'   => [
			'cache_logged_user' => 1,
			'logged_shared'     => true,
			// Spelled as get_rocket_cache_reject_cookies() spells it: the name with ".+" where the
			// hash sits.
			'reject_cookies'    => 'wordpress_logged_in_.+|wp-postpass_|comment_author_',
		],
		'expected' => [
			'contains'     => [ "\tMaxCacheExcludeCookie \"(?i)(wp-postpass_|comment_author_)\"" ],
			'not_contains' => [ 'wordpress_logged_in_' ],
		],
	],
		// The plugin appends its own login entry after whatever the user listed, so two of them can
		// sit side by side sharing one separator.
		// The directories files live in are not pages anyone excluded: without them the server
		// stats a cache path for every asset on the page before handing the request back.
	'shouldKeepTheServerAwayFromAssetDirectories'                 => [
		'config'   => [
			'reject_uri' => '/cart/(.*)',
		],
		'expected' => [
			'contains'     => [ 'MaxCacheExcludeURI "(?i)^(/cart/(.*)|/wp\\-content/(.*)|/wp\\-includes/(.*))$"' ],
			'not_contains' => [],
		],
	],
	'shouldTakeEveryLoginCookieOutOfTheList'                      => [
		'config'   => [
			'cache_logged_user' => 1,
			'logged_shared'     => true,
			'reject_cookies'    => 'wordpress_logged_in_.+|wordpress_logged_in_.+|wp-postpass_',
		],
		'expected' => [
			'contains'     => [ "\tMaxCacheExcludeCookie \"(?i)(wp-postpass_)\"" ],
			'not_contains' => [ 'wordpress_logged_in_' ],
		],
	],
		// The site keeps its own exclusion that starts the same way: it is not the entry the plugin
		// appends, and taking it away would let the module serve requests the plugin keeps out.
	'shouldKeepAnExclusionThatMerelySharesThePrefix'              => [
		'config'   => [
			'cache_logged_user' => 1,
			'logged_shared'     => true,
			// The plugin appends its login entry in its regex form; the first one is the site's own.
			'reject_cookies'    => 'wordpress_logged_in_impersonate|wordpress_logged_in_.+|wp-postpass_',
		],
		'expected' => [
			'contains'     => [ 'MaxCacheExcludeCookie "(?i)(wordpress_logged_in_impersonate|wp-postpass_)"' ],
			'not_contains' => [ 'wordpress_logged_in_.+' ],
		],
	],
	'shouldCountTabletsAsMobileWhenThePluginDoes'                 => [
		'config'   => [
			'mobile_files' => true,
			'tablet'       => 'mobile',
		],
		'expected' => [
			'contains'     => [ '-SkipCacheOnMobile +TabletAsMobile' ],
			'not_contains' => [],
		],
	],
	'shouldNameNoVariantWhenNoSeparateFileIsEverWritten'          => [
		// The tablet setting names no device, so the plugin writes one file for everyone.
		'config'   => [
			'mobile_files' => true,
			'tablet'       => '',
		],
		'expected' => [
			'contains'     => [ '-SkipCacheOnMobile' ],
			'not_contains' => [ 'TabletAsMobile' ],
		],
	],
];
