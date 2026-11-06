<?php

return [
	'testShouldTruncateDb' => [
		'config' => [
			'rows' => [
				[
					'status' => 'completed',
					'url' => 'http://example.org',
					'lcp' => json_encode((object)[
						'type' => 'img',
						'src' => 'http://example.org/wp-content/uploads/image.jpg',
					]),
					'viewport' => json_encode([
						0 => (object)[
							'type' => 'img',
							'src' => 'http://example.org/wp-content/uploads/image.jpg',
						],
					]),
				],
				[
					'status' => 'completed',
					'url' => 'http://example.org/page-1/',
					'lcp' => json_encode((object)[
						'type' => 'img',
						'src' => 'http://example.org/wp-content/uploads/image.jpg',
					]),
					'viewport' => json_encode([
						0 => (object)[
							'type' => 'img',
							'src' => 'http://example.org/wp-content/uploads/image.jpg',
						],
					])
				]
			],
			'rocket_manage_options' => true,
		],
		'expected' => 0
	],
	'testShouldNotTruncateDb' => [
		'config' => [
			'rows' => [
				[
					'status' => 'completed',
					'url' => 'http://example.org',
					'lcp' => json_encode((object)[
						'type' => 'img',
						'src' => 'http://example.org/wp-content/uploads/image.jpg',
					]),
					'viewport' => json_encode([
						0 => (object)[
							'type' => 'img',
							'src' => 'http://example.org/wp-content/uploads/image.jpg',
						],
					]),
				],
				[
					'status' => 'completed',
					'url' => 'http://example.org/page-1/',
					'lcp' => json_encode((object)[
						'type' => 'img',
						'src' => 'http://example.org/wp-content/uploads/image.jpg',
					]),
					'viewport' => json_encode([
						0 => (object)[
							'type' => 'img',
							'src' => 'http://example.org/wp-content/uploads/image.jpg',
						],
					])
				]
			],
			'rocket_manage_options' => false,
		],
		'expected' => 2
	],
];
