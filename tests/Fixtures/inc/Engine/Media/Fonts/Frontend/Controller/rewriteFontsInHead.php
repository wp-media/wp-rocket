<?php

$stylesheet = static function ( $href ): array {
	return [
		'open_tag' => '<link',
		'rel'      => 'stylesheet',
		'href'     => $href,
	];
};

$hosted = static function ( string $href, string $parameters ) use ( $stylesheet ): array {
	$hash = md5( $href );
	$item = $stylesheet( 'http://example.org/wp-content/cache/fonts/1/google-fonts/css/' . $hash[0] . '/' . $hash[1] . '/' . $hash[2] . '/' . substr( $hash, 3 ) . '.css' );

	$item['data-wpr-hosted-gf-parameters'] = $parameters;

	return $item;
};

$accepted = static function ( string $href, string $parameters ) use ( $stylesheet, $hosted ): array {
	return [
		'config'   => [
			'is_allowed' => true,
			'items'      => [ $stylesheet( $href ) ],
		],
		'expected' => [
			'fetched' => [ $href ],
			'items'   => [ $hosted( $href, $parameters ) ],
		],
	];
};

$rejected = static function ( array $items ): array {
	return [
		'config'   => [
			'is_allowed' => true,
			'items'      => $items,
		],
		'expected' => [
			'fetched' => [],
			'items'   => $items,
		],
	];
};

return [
	'testShouldReturnItemsUnchangedWhenContextNotAllowed' => [
		'config'   => [
			'is_allowed' => false,
			'items'      => [ $stylesheet( 'https://fonts.googleapis.com/css2?family=Roboto' ) ],
		],
		'expected' => [
			'fetched' => [],
			'items'   => [ $stylesheet( 'https://fonts.googleapis.com/css2?family=Roboto' ) ],
		],
	],
	'testShouldRewriteCssUrl' => $accepted(
		'https://fonts.googleapis.com/css?family=Roboto',
		'family=Roboto'
	),
	'testShouldRewriteCss2UrlWithWeights' => $accepted(
		'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap',
		'family=Roboto:wght@400;700&display=swap'
	),
	'testShouldRewriteEscapedCss2Url' => $accepted(
		'https://fonts.googleapis.com/css2?family=Roboto&#038;display=swap',
		'family=Roboto&display=swap'
	),
	'testShouldRewriteAmpEncodedCss2Url' => $accepted(
		'https://fonts.googleapis.com/css2?family=Roboto&amp;display=swap',
		'family=Roboto&display=swap'
	),
	'testShouldRewriteProtocolRelativeUrl' => $accepted(
		'//fonts.googleapis.com/css2?family=Roboto',
		'family=Roboto'
	),
	'testShouldRewriteUpperCaseSchemeAndHost' => $accepted(
		'HTTPS://FONTS.GOOGLEAPIS.COM/css2?family=Roboto',
		'family=Roboto'
	),
	'testShouldRewriteHttpUrl' => $accepted(
		'http://fonts.googleapis.com/css?family=Open+Sans',
		'family=Open+Sans'
	),
	'testShouldRewriteUrlWithFragment' => $accepted(
		'https://fonts.googleapis.com/css2?family=Roboto#top',
		'family=Roboto'
	),
	'testShouldNotRewriteWhenGoogleUrlIsOnlyInQuery' => $rejected(
		[
			[
				'open_tag' => '<link',
				'rel'      => 'preload',
				'as'       => 'font',
				'href'     => 'https://cdn.example.org/a.css?next=https://fonts.googleapis.com/css2x',
			],
		]
	),
	'testShouldNotRewriteWhenGoogleUrlIsOnlyInFragment' => $rejected(
		[ $stylesheet( 'https://cdn.example.org/a.css#//fonts.googleapis.com/css2?x' ) ]
	),
	'testShouldNotRewriteWhenHostHasUserInfo' => $rejected(
		[
			$stylesheet( 'https://fonts.googleapis.com@cdn.example.org/css2?family=Roboto' ),
			$stylesheet( 'https://user@fonts.googleapis.com/css2?family=Roboto' ),
			$stylesheet( 'https://user:pass@fonts.googleapis.com/css2?family=Roboto' ),
			$stylesheet( 'https://cdn.example.org&#039;@fonts.googleapis.com/css2?family=Roboto' ),
			$stylesheet( 'https://cdn.example.org\@fonts.googleapis.com/css2?family=Roboto' ),
		]
	),
	'testShouldNotRewriteWhenHostIsNotExact' => $rejected(
		[
			$stylesheet( 'https://fonts.googleapis.com.example.org/css2?family=Roboto' ),
			$stylesheet( 'https://fonts.googleapis.com./css2?family=Roboto' ),
			$stylesheet( 'https://cdn.fonts.googleapis.com/css2?family=Roboto' ),
		]
	),
	'testShouldNotRewriteWhenUrlHasPort' => $rejected(
		[ $stylesheet( 'https://fonts.googleapis.com:8443/css2?family=Roboto' ) ]
	),
	'testShouldNotRewriteWhenPathIsNotCssApi' => $rejected(
		[
			$stylesheet( 'https://fonts.googleapis.com/css3?family=Roboto' ),
			$stylesheet( 'https://fonts.googleapis.com/css2/?family=Roboto' ),
			$stylesheet( 'https://fonts.googleapis.com/css/../x?family=Roboto' ),
			$stylesheet( 'https://fonts.googleapis.com/icon?family=Material+Icons' ),
		]
	),
	'testShouldNotRewriteWhenQueryIsEmpty' => $rejected(
		[
			$stylesheet( 'https://fonts.googleapis.com/css2' ),
			$stylesheet( 'https://fonts.googleapis.com/css2?' ),
		]
	),
	'testShouldNotRewriteWhenSchemeIsNotHttp' => $rejected(
		[
			$stylesheet( 'ftp://fonts.googleapis.com/css2?family=Roboto' ),
			$stylesheet( 'https:fonts.googleapis.com/css2?family=Roboto' ),
		]
	),
	'testShouldNotRewritePreconnectHints' => $rejected(
		[
			[
				'open_tag' => '<link',
				'rel'      => 'preconnect',
				'href'     => 'https://fonts.gstatic.com',
			],
			[
				'open_tag' => '<link',
				'rel'      => 'preconnect',
				'href'     => 'https://fonts.googleapis.com',
			],
		]
	),
	'testShouldSkipItemsWithoutStringHref' => $rejected(
		[
			[
				'open_tag' => '<link',
				'rel'      => 'stylesheet',
			],
			$stylesheet( 123 ),
			$stylesheet( [ 'https://fonts.googleapis.com/css2?family=Roboto' ] ),
		]
	),
	'testShouldRewriteOnlyGoogleFontsItems' => [
		'config'   => [
			'is_allowed' => true,
			'items'      => [
				$stylesheet( 'https://cdn.example.org/a.css?next=https://fonts.googleapis.com/css2x' ),
				$stylesheet( 'https://fonts.googleapis.com/css2?family=Lato&#038;display=swap' ),
			],
		],
		'expected' => [
			'fetched' => [ 'https://fonts.googleapis.com/css2?family=Lato&#038;display=swap' ],
			'items'   => [
				$stylesheet( 'https://cdn.example.org/a.css?next=https://fonts.googleapis.com/css2x' ),
				$hosted( 'https://fonts.googleapis.com/css2?family=Lato&#038;display=swap', 'family=Lato&display=swap' ),
			],
		],
	],
];
