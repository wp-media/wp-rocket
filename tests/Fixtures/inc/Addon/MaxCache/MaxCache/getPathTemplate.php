<?php
// Each case lists only what it changes; the rest comes from the defaults in the test.
return [
		// Without the detection library the plugin writes one file for every device
		// (Buffer\Cache::maybe_mobile_filename() returns early), so the module must not be told to
		// look for a mobile one.
	'shouldLeaveTheMobileSuffixOutWithoutTheDetectionLibrary' => [
		'config'   => [
			'mobile_files' => true,
			'tablet'       => 'desktop',
			'detection'    => false,
		],
		'expected' => '/wp-content/cache/wp-rocket/{HTTP_HOST}{REQUEST_URI}{QS_SUFFIX}/index{SSL_SUFFIX}.html',
	],
		// The cache root is the plugin's own answer, not one of this add-on's making: it names the same
		// directory get_rocket_htaccess_mod_rewrite() names, whatever the content URL points at.
	'shouldNameTheDirectoryFromTheInstallNotFromTheContentUrl' => [
		'config'   => [
			'cache_ssl'   => 0,
			'content_url' => 'https://static.example.com/assets',
		],
		'expected' => '/wp-content/cache/wp-rocket/{HTTP_HOST}{REQUEST_URI}{QS_SUFFIX}/index{SSL_SUFFIX}.html',
	],
		// A site in a subdirectory: the plugin puts that directory in front, and so does this.
	'shouldKeepTheSubdirectoryASiteLivesIn' => [
		'config'   => [
			'cache_ssl' => 0,
			'site_url'  => 'https://example.org/blog',
		],
		'expected' => '/blog/wp-content/cache/wp-rocket/{HTTP_HOST}{REQUEST_URI}{QS_SUFFIX}/index{SSL_SUFFIX}.html',
	],
		// A cache directory moved out of the install: there the plugin names it from the document root.
	'shouldNameADirectoryOutsideTheInstallFromTheDocumentRoot' => [
		'config'   => [
			'cache_ssl'  => 0,
			'cache_path' => '/var/www/html/cache-elsewhere/',
			'abspath'    => '/var/www/html/wp/',
		],
		'expected' => '/cache-elsewhere/{HTTP_HOST}{REQUEST_URI}{QS_SUFFIX}/index{SSL_SUFFIX}.html',
	],
	'shouldReturnBaseTemplateWhenNoVariantsEnabled' => [
		'config'   => [
			'cache_ssl' => 0,
		],
		'expected' => '/wp-content/cache/wp-rocket/{HTTP_HOST}{REQUEST_URI}{QS_SUFFIX}/index{SSL_SUFFIX}.html',
	],
	'shouldAddWebpSuffixWhenWebpCachingEnabled'     => [
		'config'   => [
			'cache_ssl'  => 0,
			'cache_webp' => 1,
		],
		'expected' => '/wp-content/cache/wp-rocket/{HTTP_HOST}{REQUEST_URI}{QS_SUFFIX}/index{SSL_SUFFIX}{WEBP_SUFFIX}.html',
	],
	'shouldAddDynamicCookieSuffixWhenCookiesSet'    => [
		'config'   => [
			'cache_ssl' => 0,
			'cookies'   => [ 'currency' ],
		],
		'expected' => '/wp-content/cache/wp-rocket/{HTTP_HOST}{REQUEST_URI}{QS_SUFFIX}/index{SSL_SUFFIX}{DYNAMIC_COOKIE_SUFFIX}.html',
	],
	'shouldAddGzipSuffixWhenGzipVariantWritten'     => [
		'config'   => [
			'cache_ssl' => 0,
			'gzip'      => true,
		],
		'expected' => '/wp-content/cache/wp-rocket/{HTTP_HOST}{REQUEST_URI}{QS_SUFFIX}/index{SSL_SUFFIX}.html{GZIP_SUFFIX}',
	],
		// The plugin names no file for an https request while caching for HTTPS is off, so the module
		// has to look for one and miss, rather than answer such a visitor with the http page.
	'shouldNameTheSchemeEvenWhereHttpsIsNotCached'  => [
		'config'   => [
			'cache_ssl' => 0,
		],
		'expected' => '/wp-content/cache/wp-rocket/{HTTP_HOST}{REQUEST_URI}{QS_SUFFIX}/index{SSL_SUFFIX}.html',
	],
		// Separate mobile files: the server names the variant the same way the plugin did, and a
		// device the two classify differently gets a miss rather than the other one's page.
	'shouldNameTheMobileVariantWhereThePluginWritesIt' => [
		'config'   => [
			'cache_ssl'    => 0,
			'mobile_files' => true,
			'tablet'       => 'desktop',
		],
		'expected' => '/wp-content/cache/wp-rocket/{HTTP_HOST}{REQUEST_URI}{QS_SUFFIX}/index{MOBILE_SUFFIX}{SSL_SUFFIX}.html',
	],
	'shouldKeepSuffixOrderOfTheCacheFileName'       => [
		'config'   => [
			'cache_webp' => 1,
			'cookies'    => [ 'currency' ],
			'gzip'       => true,
		],
		'expected' => '/wp-content/cache/wp-rocket/{HTTP_HOST}{REQUEST_URI}{QS_SUFFIX}/index{SSL_SUFFIX}{WEBP_SUFFIX}{DYNAMIC_COOKIE_SUFFIX}.html{GZIP_SUFFIX}',
	],
	'shouldAddSharedLoggedInBucketAfterTheHost'     => [
		'config'   => [
			'cache_logged_user' => 1,
			'logged_shared'     => true,
		],
		'expected' => '/wp-content/cache/wp-rocket/{HTTP_HOST}{USER_SHARED_SUFFIX}{REQUEST_URI}{QS_SUFFIX}/index{SSL_SUFFIX}.html',
	],
	'shouldOmitWebpSuffixWhenTheVariantIsNotWritten'=> [
		'config'   => [
			'cache_webp'    => 1,
			'webp_disabled' => true,
		],
		'expected' => '/wp-content/cache/wp-rocket/{HTTP_HOST}{REQUEST_URI}{QS_SUFFIX}/index{SSL_SUFFIX}.html',
	],
];
