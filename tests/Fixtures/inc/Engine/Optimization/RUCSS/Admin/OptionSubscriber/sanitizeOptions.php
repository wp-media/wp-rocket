<?php

return [
	'testShouldSetDefaultValueIfNotSet' => [
		'config'    => [
			'input'           => [],
		],
		'expected' => [
			'remove_unused_css'          => 0,
			'remove_unused_css_safelist' => [],
		],
	],
	'testShouldSetCorrectValueIfDifferentType' => [
		'config'   => [
			'input'           => [
				'remove_unused_css'          => true,
				'remove_unused_css_safelist' => "wp-content/themes/twentytwenty/style.css\n<script>\n.test\nbody\nwp-includes/.*.css",
			],
		],
		'expected' => [
			'remove_unused_css'          => 1,
			'remove_unused_css_safelist' => [
				'wp-content/themes/twentytwenty/style.css',
				'.test',
				'body',
				'wp-includes/(.*).css',
			],
		],
	],
	'testShouldPreserveValueIfCorrectType' => [
		'config'   => [
			'input'           => [
				'remove_unused_css'          => 1,
				'remove_unused_css_safelist' => [
					'wp-content/themes/twentytwenty/style.css',
					'.test',
					'<script>',
					'body',
					'wp-includes/.*.css'
				],
			],
		],
		'expected' => [
			'remove_unused_css'          => 1,
			'remove_unused_css_safelist' => [
				'wp-content/themes/twentytwenty/style.css',
				'.test',
				'body',
				'wp-includes/(.*).css',
			],
		],
	],
];
