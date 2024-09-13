<?php

return [
	'test_data' => [
		'shouldNotAddHashesWhenNotAllowed' => [
			'config' => [
				'filter' => false,
				'row' => [
					'url' => 'http://example.org/',
					'is_mobile' => 0,
					'below_the_fold' => json_encode(
						[
							'93548b90aa8f4989f7198144479055dc',
							'7b16eca0652d4703f83ba63e304f2030',
							'737184bbad8e65d0172a89cc68a46107',
							'8a4ef50742cf3456f9db6425e16930dc',
						]
					),
					'status' => 'completed',
				],
				'html' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/LazyRenderContent/Frontend/Subscriber/html/original.php' ),
			],
			'expected' => [
				'html' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/LazyRenderContent/Frontend/Subscriber/html/original.php' ),
			],
		],
		'shouldAddHashes' => [
			'config' => [
				'filter' => true,
				'row' => [
					'url' => 'http://example.org/',
					'is_mobile' => 0,
					'below_the_fold' => json_encode(
						[
							'93548b90aa8f4989f7198144479055dc',
							'7b16eca0652d4703f83ba63e304f2030',
							'737184bbad8e65d0172a89cc68a46107',
							'8a4ef50742cf3456f9db6425e16930dc',
						]
					),
					'status' => 'completed',
				],
				'html' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/LazyRenderContent/Frontend/Subscriber/html/original.php' ),
			],
			'expected' => [
				'html' => file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/Engine/Optimization/LazyRenderContent/Frontend/Subscriber/html/expected.php' ),
			],
		],
	],
];
