<?php
// Each case lists only what it changes; the rest comes from the defaults in the test.
return [
		// The lines that tell the server a .html_gzip file is gzip-encoded HTML: the plugin writes its own
		// copy inside the rewrite section this add-on takes away, so whoever serves has to declare it.
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
		// The directive carries bare, space-separated names, so a value that is not one is dropped
		// rather than written: the module then declines URLs carrying it instead of folding it away.
	'shouldLeaveOutAnIgnoredParameterThatIsNotADirectiveArgument' => [
		'config'   => [
			'ignored_parameters' => [
				'utm_source'  => 1,
				'and hsa_ver' => 1,
				'gclid'       => 1,
			],
		],
		'expected' => [
			'contains'     => [ 'MaxCacheQSIgnoredParams utm_source gclid' ],
			'not_contains' => [ 'and hsa_ver' ],
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
		// The plugin's own list and nothing else: the name it classifies from CloudFront headers exists
		// only inside PHP, and the module matches the header itself.
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
		// No bucket is named for them and the login cookie stays where the plugin put it.
	'shouldLeaveLoggedInVisitorsToPhpAndKeepServingEveryoneElse'  => [
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
		// The login entry stays where the plugin put it, so those visitors reach PHP.
	'shouldLeaveTheLoginEntryInTheExclusions'                    => [
		'config'   => [
			'cache_logged_user' => 1,
			// Spelled as get_rocket_cache_reject_cookies() spells it: ".+" where the hash sits.
			'reject_cookies'    => 'wordpress_logged_in_.+|wp-postpass_|comment_author_',
		],
		'expected' => [
			'contains'     => [ "\tMaxCacheExcludeCookie \"(?i)(wordpress_logged_in_.+|wp-postpass_|comment_author_)\"" ],
			'not_contains' => [ 'MaxCacheLoggedHash' ],
		],
	],
		// The same with the switch off: the plugin bypasses the cache for those visitors either way.
	'shouldPutTheLoginEntryBackWithTheSwitchOff'                 => [
		'config'   => [
			'cache_logged_user' => 0,
			'reject_cookies'    => 'wp-postpass_',
		],
		'expected' => [
			'contains'     => [ "\tMaxCacheExcludeCookie \"(?i)(wp-postpass_|wordpress_logged_in_.+)\"" ],
			'not_contains' => [],
		],
	],
		// Only the plugin's own spelling is recognised; any other leaves a harmless second alternative.
	'shouldAddItsOwnEntryBesideADifferentlySpelledOne'           => [
		'config'   => [
			'cache_logged_user' => 1,
			'reject_cookies'    => 'wordpress_logged_in_[^|]*|wp-postpass_',
		],
		'expected' => [
			'contains'     => [ "\tMaxCacheExcludeCookie \"(?i)(wordpress_logged_in_[^|]*|wp-postpass_|wordpress_logged_in_.+)\"" ],
			'not_contains' => [],
		],
	],
		// A filter dropped the plugin's own login entry; without it the module answers with the anonymous file.
	'shouldPutTheLoginEntryBackWhereAFilterDroppedIt'            => [
		'config'   => [
			'cache_logged_user' => 1,
			'reject_cookies'    => 'wp-postpass_|comment_author_',
		],
		'expected' => [
			'contains'     => [ "\tMaxCacheExcludeCookie \"(?i)(wp-postpass_|comment_author_|wordpress_logged_in_.+)\"" ],
			'not_contains' => [],
		],
	],
		// A filter emptied the list: the directive carries the login entry and nothing else.
	'shouldWriteTheLoginEntryAloneOnAnEmptyList'                 => [
		'config'   => [
			'cache_logged_user' => 1,
			'reject_cookies'    => '',
		],
		'expected' => [
			'contains'     => [ "\tMaxCacheExcludeCookie \"(?i)(wordpress_logged_in_.+)\"" ],
			'not_contains' => [],
		],
	],
		// A renamed login cookie carries no hash to stand in for, so the whole name is the entry.
	'shouldExcludeARenamedLoginCookieByItsWholeName'             => [
		'config'   => [
			'cache_logged_user' => 1,
			'logged_in_cookie'  => 'my_login_cookie',
			'reject_cookies'    => 'wp-postpass_',
		],
		'expected' => [
			'contains'     => [ "\tMaxCacheExcludeCookie \"(?i)(wp-postpass_|my_login_cookie)\"" ],
			'not_contains' => [ 'wordpress_logged_in' ],
		],
	],
		// No login cookie to name: the plugin's list is handed over as it stands.
	'shouldHandTheListOverWithoutALoginCookieToName'             => [
		'config'   => [
			'cache_logged_user' => 1,
			'logged_in_cookie'  => '',
			'reject_cookies'    => 'wp-postpass_',
		],
		'expected' => [
			'contains'     => [ "\tMaxCacheExcludeCookie \"(?i)(wp-postpass_)\"" ],
			'not_contains' => [ 'wordpress_logged_in' ],
		],
	],
		// Asset directories are not pages anyone excluded: without them the server stats a cache path
		// for every asset on the page before handing the request back.
	'shouldKeepTheServerAwayFromAssetDirectories'                 => [
		'config'   => [
			'reject_uri' => '/cart/(.*)',
		],
		'expected' => [
			'contains'     => [ 'MaxCacheExcludeURI "(?i)^(/cart/(.*)|/wp\\-content/(.*)|/wp\\-includes/(.*))$"' ],
			'not_contains' => [],
		],
	],
		// The site's own look-alike entry and the plugin's both stay.
	'shouldKeepEveryLoginExclusionTheListCarries'                => [
		'config'   => [
			'cache_logged_user' => 1,
			// The plugin appends its login entry in its regex form; the first one is the site's own.
			'reject_cookies'    => 'wordpress_logged_in_impersonate|wordpress_logged_in_.+|wp-postpass_',
		],
		'expected' => [
			'contains'     => [ 'MaxCacheExcludeCookie "(?i)(wordpress_logged_in_impersonate|wordpress_logged_in_.+|wp-postpass_)"' ],
			'not_contains' => [ 'MaxCacheLoggedHash' ],
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
