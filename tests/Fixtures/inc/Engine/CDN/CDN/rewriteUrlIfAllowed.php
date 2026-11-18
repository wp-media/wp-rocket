<?php

return [
	'shouldRewriteAbsoluteUrlInAllowedPath'                => [
		'config'   => [ 'relative_paths' => true ],
		'url'      => 'http://example.org/wp-content/uploads/a.jpg',
		'expected' => 'https://cdn.example.org/wp-content/uploads/a.jpg',
	],
	'shouldRewriteProtocolRelativeUrlInAllowedPath'        => [
		'config'   => [ 'relative_paths' => true ],
		'url'      => '//example.org/wp-content/uploads/a.jpg',
		'expected' => '//cdn.example.org/wp-content/uploads/a.jpg',
	],
	'shouldNotRewriteAbsoluteUrlOutsideAllowedPaths'       => [
		'config'   => [ 'relative_paths' => true ],
		'url'      => 'http://example.org/other/a.jpg',
		'expected' => 'http://example.org/other/a.jpg',
	],
	'shouldRewriteRelativeUrl'                             => [
		'config'   => [ 'relative_paths' => true ],
		'url'      => '/wp-content/x.jpg',
		'expected' => 'https://cdn.example.org/wp-content/x.jpg',
	],
	'shouldNotRewriteRelativeUrlWhenRelativePathsDisabled' => [
		'config'   => [ 'relative_paths' => false ],
		'url'      => '/wp-content/x.jpg',
		'expected' => '/wp-content/x.jpg',
	],
	'shouldNotRewriteExternalHost'                         => [
		'config'   => [ 'relative_paths' => true ],
		'url'      => 'https://other.com/wp-content/uploads/a.jpg',
		'expected' => 'https://other.com/wp-content/uploads/a.jpg',
	],
	'shouldNotRewritePhpFile'                              => [
		'config'   => [ 'relative_paths' => true ],
		'url'      => 'http://example.org/wp-content/uploads/a.php',
		'expected' => 'http://example.org/wp-content/uploads/a.php',
	],
	'shouldNotRewriteUrlAlreadyOnCdn'                      => [
		'config'   => [ 'relative_paths' => true ],
		'url'      => 'https://cdn.example.org/wp-content/uploads/a.jpg',
		'expected' => 'https://cdn.example.org/wp-content/uploads/a.jpg',
	],
	'shouldNotRewritePathPrefixedCdnOnHomeHost'            => [
		'config'   => [ 'relative_paths' => true ],
		'url'      => 'http://example.org/cdn/wp-content/uploads/a.jpg',
		'expected' => 'http://example.org/cdn/wp-content/uploads/a.jpg',
	],
];
