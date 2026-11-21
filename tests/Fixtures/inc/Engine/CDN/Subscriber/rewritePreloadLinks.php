<?php

$page = '<html><head><title>t</title>%s</head><body><img src="http://example.org/wp-content/uploads/a-1024.jpg"></body></html>';
$tag  = '<link rel="preload" data-rocket-preload as="image" href="http://example.org/wp-content/uploads/a-1024.jpg" imagesrcset="http://example.org/wp-content/uploads/a-300.jpg 300w, http://example.org/wp-content/uploads/a-1024.jpg 1024w" imagesizes="100vw" fetchpriority="high">';

$cdn_tag = static function ( $host ) {
	return '<link rel="preload" data-rocket-preload as="image" href="http://' . $host . '/wp-content/uploads/a-1024.jpg" imagesrcset="http://' . $host . '/wp-content/uploads/a-300.jpg 300w, http://' . $host . '/wp-content/uploads/a-1024.jpg 1024w" imagesizes="100vw" fetchpriority="high">';
};

return [
	'shouldRewritePreloadWithSubdomainCname'           => [
		'config'   => [
			'cdn'      => true,
			'cnames'   => [ 'cdn.example.org' ],
			'cdn_zone' => [ 'all' ],
			'excluded' => false,
			'preload'  => $tag,
		],
		'original' => sprintf( $page, '' ),
		'expected' => sprintf( str_replace( 'http://example.org/wp-content/uploads/a-1024.jpg"></body>', 'http://cdn.example.org/wp-content/uploads/a-1024.jpg"></body>', $page ), $cdn_tag( 'cdn.example.org' ) ),
	],
	'shouldRewritePreloadWithThirdPartyCname'          => [
		'config'   => [
			'cdn'      => true,
			'cnames'   => [ 'xyz.exactdn.com' ],
			'cdn_zone' => [ 'all' ],
			'excluded' => false,
			'preload'  => $tag,
		],
		'original' => sprintf( $page, '' ),
		'expected' => sprintf( str_replace( 'http://example.org/wp-content/uploads/a-1024.jpg"></body>', 'http://xyz.exactdn.com/wp-content/uploads/a-1024.jpg"></body>', $page ), $cdn_tag( 'xyz.exactdn.com' ) ),
	],
	'shouldNotRewritePreloadWhenPostIsExcludedFromCdn' => [
		'config'   => [
			'cdn'      => true,
			'cnames'   => [ 'cdn.example.org' ],
			'cdn_zone' => [ 'all' ],
			'excluded' => true,
			'preload'  => $tag,
		],
		'original' => sprintf( $page, '' ),
		'expected' => sprintf( $page, $tag ),
	],
	'shouldNotRewritePreloadWhenCdnIsDisabled'         => [
		'config'   => [
			'cdn'      => false,
			'cnames'   => [ 'cdn.example.org' ],
			'cdn_zone' => [ 'all' ],
			'excluded' => false,
			'preload'  => $tag,
		],
		'original' => sprintf( $page, '' ),
		'expected' => sprintf( $page, $tag ),
	],
];
