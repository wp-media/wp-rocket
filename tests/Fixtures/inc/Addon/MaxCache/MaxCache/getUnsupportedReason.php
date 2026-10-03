<?php
// Each case lists only what it changes; the rest comes from the defaults in the test.
return [
		// Typed into "Never cache the following pages" and not a pattern at all: the module logs the failed
		// compile and goes on serving, so the exclusion would silently stop applying.
	'shouldRefuseAnExclusionThatIsNotACompilablePattern' => [
		'config'   => [ 'reject_uri' => '/checkout(' ],
		'expected' => 'URLs',
	],
		// A space inside the cache directory: MaxCachePath takes one argument, and the module
		// compiles the template without quotes of its own.
	'shouldRefuseAPathThatCannotBeWrittenIntoADirective' => [
		'config'   => [ 'cache_path' => '/var/www/html/wp-content/cache/my rocket/' ],
		'expected' => 'cannot be written into the server configuration',
	],
		// Leaving a name out of the directive only narrows what the module answers: a URL carrying it
		// reaches PHP, where the plugin serves it. Filtered, not refused.
	'shouldNotRefuseAnIgnoredParameterThatCannotBeWritten' => [
		'config'   => [ 'ignored_parameters' => [ 'utm_source' => 1, 'and hsa_ver' => 1 ] ],
		'expected' => '',
	],
		// Same reasoning for the allow list: a name left out of it is a request the module declines.
	'shouldNotRefuseAnAllowedParameterThatCannotBeWritten' => [
		'config'   => [ 'query_strings' => [ 'currency', 'bad name' ] ],
		'expected' => '',
	],
	'shouldRefuseWhenModuleIsNotInstalled'                   => [
		'config'   => [
			'mode' => 'none',
		],
		'expected' => 'not installed',
	],
	'shouldRefuseOnMultisite'                                => [
		'config'   => [
			'multisite' => true,
		],
		'expected' => 'does not serve the cache by request URI',
	],
	'shouldAcceptALocaleWhoseUrlsAreAlwaysEncoded'           => [
		'config'   => [
			'locale' => 'ko_KR',
		],
		'expected' => '',
	],
		// LiteSpeed, or a host where only the Apache package is installed: the file is written for
		// nobody, and the add-on would sit for ever on "the rules have not reached the file yet".
	'shouldRefuseWhereNothingWouldReadTheFile'               => [
		'config'   => [
			'mode'            => 'apache',
			'server_software' => 'LiteSpeed',
		],
		'expected' => 'does not read .htaccess itself',
	],
		// The same host with the NGINX build: there the configuration daemon reads the file.
	'shouldAcceptTheNginxBuildOffApache'                     => [
		'config'   => [
			'mode'            => 'nginx',
			'server_software' => 'nginx/1.24.0',
		],
		'expected' => '',
	],
	'shouldRefuseWhenPluginWritesNoCacheFiles'               => [
		'config'   => [
			'writes_files' => false,
		],
		'expected' => 'Page caching is disabled',
	],
	'shouldRefuseWhenMobileCachingIsDisabled'                => [
		'config'   => [
			'mobile_cache' => false,
		],
		'expected' => 'Caching for mobile devices is disabled',
	],
	'shouldRefuseOnHttpsSiteWithoutSslCaching'               => [
		'config'   => [
			'cache_ssl' => 0,
		],
		'expected' => 'HTTPS',
	],
	'shouldRefuseWhenAnotherIntegrationVetoedServingByUri'   => [
		'config'   => [
			'serving_vetoed' => true,
		],
		'expected' => 'request URI',
	],
	'shouldRefuseWhenHostNameIsWrittenWithoutDots'           => [
		'config'   => [
			'url_no_dots' => true,
		],
		'expected' => 'dots replaced',
	],
		// HTTPS in the environment on a plain port with no header to explain it: a wp-config snippet cannot
		// be told from a server that set it itself, and the cost of guessing wrong is a miss into PHP.
	'shouldAcceptWhatNoForwardedHeaderGivesAway'             => [
		'config'   => [
			'is_ssl'    => true,
			'port'      => '80',
			'https_env' => true,
		],
		'expected' => '',
	],
	'shouldIgnoreTheProxyWhenSslCachingIsOff'                => [
		// With HTTPS caching off the plugin writes no https name at all, so the two cannot disagree.
		'config'   => [
			'home'         => 'http://example.org',
			'cache_ssl'    => 0,
			'behind_proxy' => true,
		],
		'expected' => '',
	],
		// A forwarded header on a plain listener PHP does not believe either: the module ignores the header
		// unless the operator vouched for the proxy, so both name the plain file.
	'shouldAcceptAForwardedHeaderPhpItselfDoesNotBelieve'    => [
		'config'   => [
			'behind_proxy'   => false,
			'forwarded_only' => true,
		],
		'expected' => '',
	],
		// The Cloudflare Flexible shape: the wp-config snippet turns the forwarded header into
		// $_SERVER['HTTPS'], so PHP writes the https name while the server resolves the plain one.
	'shouldRefuseWhereTheSchemeArrivesInAForwardedHeader'    => [
		'config'   => [
			'is_ssl'          => true,
			'port'            => '80',
			'forwarded_https' => true,
			'https_env'       => true,
		],
		'expected' => 'proxy in front',
	],
		// The ordinary cPanel stack: nginx on 443 in front of Apache on another port, which
		// terminates TLS itself and sets HTTPS in the environment the module reads.
	'shouldAcceptTlsTheServerTerminatesOnAnotherPort'        => [
		'config'   => [
			'is_ssl'    => true,
			'port'      => '8443',
			'https_env' => true,
		],
		'expected' => '',
	],
	'shouldRefuseWhenCacheDirectoryIsNotAddressable'         => [
		'config'   => [
			'cache_path' => '/opt/private/cache/wp-rocket/',
		],
		'expected' => 'cache directory is outside',
	],
	'shouldAcceptSupportedConfiguration'                     => [
		'config'   => [],
		'expected' => '',
	],
		// Logged-in visitors go to PHP by the exclusion list; the site keeps the module for everyone else.
	'shouldAcceptAndLeaveLoggedInVisitorsToPhp'              => [
		'config'   => [
			'cache_logged_user' => 1,
		],
		'expected' => '',
	],
	'shouldRefuseDynamicCookiesDeclaredAsKeysInsideOneCookie'=> [
		'config'   => [
			'dynamic_cookies' => [ 'gdpr' => [ 'allowed_cookies', 'consent_types' ] ],
		],
		'expected' => 'parts of one cookie',
	],
];
