<?php

$css2_url = esc_url( 'https://fonts.googleapis.com/css2?family=Roboto&display=swap' );
$css2_md5 = md5( $css2_url );

return [
	'test_data' => [
		'testShouldRewriteEscapedCss2Url' => [
			'config'   => [
				'host_fonts_locally' => true,
				'items'              => [
					[
						'open_tag' => '<link',
						'rel'      => 'stylesheet',
						'href'     => $css2_url,
					],
				],
				'http'               => [
					'https://fonts.googleapis.com/css2?family=Roboto&display=swap' => [
						'body'     => 'body { font-family: "Roboto"; }',
						'response' => [ 'code' => 200 ],
					],
				],
			],
			'expected' => [
				'requests' => [ 'https://fonts.googleapis.com/css2?family=Roboto&display=swap' ],
				'items'    => [
					[
						'open_tag'                      => '<link',
						'rel'                           => 'stylesheet',
						'href'                          => 'http://example.org/wp-content/cache/fonts/1/google-fonts/css/' . $css2_md5[0] . '/' . $css2_md5[1] . '/' . $css2_md5[2] . '/' . substr( $css2_md5, 3 ) . '.css',
						'data-wpr-hosted-gf-parameters' => 'family=Roboto&display=swap',
					],
				],
			],
		],
		'testShouldNotRewriteWhenGoogleUrlIsOnlyInQuery' => [
			'config'   => [
				'host_fonts_locally' => true,
				'items'              => [
					[
						'open_tag' => '<link',
						'rel'      => 'preload',
						'as'       => 'font',
						'href'     => esc_url( 'https://cdn.example.org/a.css?next=https://fonts.googleapis.com/css2x' ),
					],
				],
			],
			'expected' => [
				'requests' => [],
				'items'    => [
					[
						'open_tag' => '<link',
						'rel'      => 'preload',
						'as'       => 'font',
						'href'     => esc_url( 'https://cdn.example.org/a.css?next=https://fonts.googleapis.com/css2x' ),
					],
				],
			],
		],
		'testShouldNotRewriteWhenHostHasUserInfo' => [
			'config'   => [
				'host_fonts_locally' => true,
				'items'              => [
					[
						'open_tag' => '<link',
						'rel'      => 'stylesheet',
						'href'     => esc_url( 'https://fonts.googleapis.com@cdn.example.org/css2?family=Roboto' ),
					],
					[
						'open_tag' => '<link',
						'rel'      => 'stylesheet',
						'href'     => esc_url( 'https://user@fonts.googleapis.com/css2?family=Roboto' ),
					],
				],
			],
			'expected' => [
				'requests' => [],
				'items'    => [
					[
						'open_tag' => '<link',
						'rel'      => 'stylesheet',
						'href'     => esc_url( 'https://fonts.googleapis.com@cdn.example.org/css2?family=Roboto' ),
					],
					[
						'open_tag' => '<link',
						'rel'      => 'stylesheet',
						'href'     => esc_url( 'https://user@fonts.googleapis.com/css2?family=Roboto' ),
					],
				],
			],
		],
	],
];
