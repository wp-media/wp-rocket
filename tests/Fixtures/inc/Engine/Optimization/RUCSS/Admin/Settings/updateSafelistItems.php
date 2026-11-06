<?php

return [
	'testShouldDoNothingWhenAbove3.11.0.2' => [
		'config' => [
			'version' => '3.11.1',
			'options' => [
				'remove_unused_css_safelist' => [
					'.class',
					'/wp-content/themes/style.css',
					'div',
					'#id',
					'(.*).class2',
					'[attribute]',
				],
			],
		],
		'expected' => false,
	],
	'testShouldDoNothingWhenEmptySafelist' => [
		'config' => [
			'version' => '3.11.0.1',
			'options' => [
				'remove_unused_css_safelist' => [],
			],
		],
		'expected' => false,
	],
	'testShouldUpdateSafelist' => [
		'config' => [
			'version' => '3.11.0.1',
			'options' => [
				'remove_unused_css_safelist' => [
					'.class',
					'/wp-content/themes/style.css',
					'div',
					'#id',
					'(.*).class2',
					'[attribute]',
				],
			],
		],
		'expected' => [
			'remove_unused_css_safelist' => [
				'(.*).class',
				'/wp-content/themes/style.css',
				'(.*)div',
				'(.*)#id',
				'(.*).class2',
				'(.*)[attribute]',
			],
		],
	],
];
