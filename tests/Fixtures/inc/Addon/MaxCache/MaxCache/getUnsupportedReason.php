<?php
// Each case lists only what it changes; the rest comes from the defaults in the test.
return [
		// Typed into "Never cache the following pages" and not a pattern at all. The module logs the
		// failed compile and goes on serving, so the exclusion silently stops applying — which is why
		// this is refused rather than written.
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
		// HTTPS in the environment on a plain port, and no header to explain it: from PHP there is
		// nothing to tell a snippet in wp-config apart from a server that set it itself. Accepted, and
		// where it was the snippet the plugin writes an https name the module never looks for — a miss
		// into PHP, not a page served to the wrong visitor.
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
		// A forwarded header on a plain listener, and PHP does not call the request secure either:
		// the module ignores the header unless the operator has vouched for the proxy, so both name
		// the plain file and there is nothing to disagree about.
	'shouldAcceptAForwardedHeaderPhpItselfDoesNotBelieve'    => [
		'config'   => [
			'behind_proxy'   => false,
			'forwarded_only' => true,
		],
		'expected' => '',
	],
		// The Cloudflare Flexible shape: the snippet in wp-config.php turns the forwarded header into
		// $_SERVER['HTTPS'], so PHP writes the https name while the server, which does not read the
		// header, resolves the plain one.
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
		// The per-user bucket is named from a value the visitor sends, and the module names it from a
		// hash of the whole cookie instead, so those visitors are not given to it. Not a refusal: the
		// login cookie stays in the exclusion list, their requests reach PHP, and anonymous pages —
		// named the same either way — go on being served by the module.
	'shouldAcceptAndLeaveThePerUserBucketToPhp'              => [
		'config'   => [
			'cache_logged_user' => 1,
		],
		'expected' => '',
	],
	'shouldAcceptTheSharedLoggedInBucket'                    => [
		'config'   => [
			'cache_logged_user' => 1,
			'logged_shared'     => true,
		],
		'expected' => '',
	],
		// Same shape: the module knows a logged-in visitor by the standard cookie name, so with the
		// cookie renamed it is not given them — and the exclusion is built from the name the plugin
		// actually uses, so those requests reach PHP.
	'shouldAcceptAndLeaveARenamedLoginCookieToPhp'           => [
		'config'   => [
			'cache_logged_user' => 1,
			'logged_shared'     => true,
			'logged_in_cookie'  => 'my_login_cookie',
		],
		'expected' => '',
	],
	'shouldRefuseDynamicCookiesDeclaredAsKeysInsideOneCookie'=> [
		'config'   => [
			'dynamic_cookies' => [ 'gdpr' => [ 'allowed_cookies', 'consent_types' ] ],
		],
		'expected' => 'parts of one cookie',
	],
		// Without the key there is no bucket name to give the module, and a directive without an
		// argument is a configuration error the web server answers with 500. Logged-in visitors go
		// to PHP; nothing else changes.
	'shouldAcceptAndLeaveTheSharedBucketToPhpWithoutItsKey'  => [
		'config'   => [
			'cache_logged_user' => 1,
			'secret_cache_key'  => '',
			'logged_shared'     => true,
		],
		'expected' => '',
	],
		// Reachable through a settings import or a hand-edited row: the plugin writes sanitize_key()
		// of this into the config its bucket name is built from, so what is left of it is nothing —
		// and the directive that would carry it is left out for the same reason.
	'shouldAcceptAndLeaveTheSharedBucketToPhpOnAnUnusableKey' => [
		'config'   => [
			'cache_logged_user' => 1,
			'secret_cache_key'  => '!!!',
			'logged_shared'     => true,
		],
		'expected' => '',
	],
];
