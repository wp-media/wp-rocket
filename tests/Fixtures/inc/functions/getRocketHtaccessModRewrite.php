<?php
declare(strict_types=1);

return [
	'vfs_dir'   => '',
	'structure' => [],
	'test_data' => [
		// Linux: cache path inside ABSPATH.
		[
			'config'   => [
				'abspath'       => '/var/www/wp/',
				'cache_path'    => '/var/www/wp/wp-content/cache/wp-rocket/',
				'document_root' => '/var/www/wp/',
			],
			'expected' => [
				'RewriteBase /',
				'RewriteCond "%{DOCUMENT_ROOT}/wp-content/cache/wp-rocket/%{HTTP_HOST}%{REQUEST_URI}/index%{ENV:WPR_SSL}%{ENV:WPR_WEBP}.html" -f',
				'RewriteRule .* "/wp-content/cache/wp-rocket/%{HTTP_HOST}%{REQUEST_URI}/index%{ENV:WPR_SSL}%{ENV:WPR_WEBP}.html" [L]',
			],
		],
		// Windows: cache path inside ABSPATH.
		[
			'config'   => [
				'abspath'       => 'C:\\xampp\\htdocs\\wp\\',
				'cache_path'    => 'C:/xampp/htdocs/wp/wp-content/cache/wp-rocket/',
				'document_root' => 'C:\\xampp\\htdocs\\wp',
			],
			'expected' => [
				'RewriteBase /',
				'RewriteCond "%{DOCUMENT_ROOT}/wp-content/cache/wp-rocket/%{HTTP_HOST}%{REQUEST_URI}/index%{ENV:WPR_SSL}%{ENV:WPR_WEBP}.html" -f',
				'RewriteRule .* "/wp-content/cache/wp-rocket/%{HTTP_HOST}%{REQUEST_URI}/index%{ENV:WPR_SSL}%{ENV:WPR_WEBP}.html" [L]',
			],
		],
		// Linux: cache path outside ABSPATH but under DOCUMENT_ROOT.
		[
			'config'   => [
				'abspath'       => '/var/www/wp/',
				'cache_path'    => '/var/www/wp-content/cache/wp-rocket/',
				'document_root' => '/var/www/',
			],
			'expected' => [
				'RewriteBase /',
				'RewriteCond "%{DOCUMENT_ROOT}/wp-content/cache/wp-rocket/%{HTTP_HOST}%{REQUEST_URI}/index%{ENV:WPR_SSL}%{ENV:WPR_WEBP}.html" -f',
				'RewriteRule .* "/wp-content/cache/wp-rocket/%{HTTP_HOST}%{REQUEST_URI}/index%{ENV:WPR_SSL}%{ENV:WPR_WEBP}.html" [L]',
			],
		],
		// Windows: cache path outside ABSPATH but under DOCUMENT_ROOT.
		[
			'config'   => [
				'abspath'       => 'C:\\xampp\\htdocs\\wp\\',
				'cache_path'    => 'C:/xampp/htdocs/wp-content/cache/wp-rocket/',
				'document_root' => 'C:\\xampp\\htdocs',
			],
			'expected' => [
				'RewriteBase /',
				'RewriteCond "%{DOCUMENT_ROOT}/wp-content/cache/wp-rocket/%{HTTP_HOST}%{REQUEST_URI}/index%{ENV:WPR_SSL}%{ENV:WPR_WEBP}.html" -f',
				'RewriteRule .* "/wp-content/cache/wp-rocket/%{HTTP_HOST}%{REQUEST_URI}/index%{ENV:WPR_SSL}%{ENV:WPR_WEBP}.html" [L]',
			],
		],
		// 1&1 hosting: full path is forced.
		[
			'config'   => [
				'abspath'       => '/kunden/12345/webseiten/wordpress/',
				'cache_path'    => '/kunden/12345/webseiten/wordpress/wp-content/cache/wp-rocket/',
				'document_root' => '/kunden/12345/webseiten/wordpress/',
			],
			'expected' => [
				'RewriteBase /',
				'RewriteCond "/12345/webseiten/wordpress/wp-content/cache/wp-rocket/%{HTTP_HOST}%{REQUEST_URI}/index%{ENV:WPR_SSL}%{ENV:WPR_WEBP}.html" -f',
				'RewriteRule .* "/wp-content/cache/wp-rocket/%{HTTP_HOST}%{REQUEST_URI}/index%{ENV:WPR_SSL}%{ENV:WPR_WEBP}.html" [L]',
			],
		],
		// Windows without DOCUMENT_ROOT: falls back to ABSPATH.
		[
			'config'   => [
				'abspath'       => 'C:\\xampp\\htdocs\\wp\\',
				'cache_path'    => 'C:/xampp/htdocs/wp/wp-content/cache/wp-rocket/',
				'document_root' => null,
			],
			'expected' => [
				'RewriteBase /',
				'RewriteCond "%{DOCUMENT_ROOT}/wp-content/cache/wp-rocket/%{HTTP_HOST}%{REQUEST_URI}/index%{ENV:WPR_SSL}%{ENV:WPR_WEBP}.html" -f',
				'RewriteRule .* "/wp-content/cache/wp-rocket/%{HTTP_HOST}%{REQUEST_URI}/index%{ENV:WPR_SSL}%{ENV:WPR_WEBP}.html" [L]',
			],
		],
	],
];
