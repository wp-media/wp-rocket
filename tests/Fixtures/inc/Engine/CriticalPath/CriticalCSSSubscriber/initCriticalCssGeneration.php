<?php

return [
	'test_data' => [
		'testShouldWPNonceAysWhenNonceIsMissing' => [
			'config' => [
				'nonce'   => null,
				'cap'     => null,
				'mobile'  => false,
				'referer' => false,
			],
			'expected' => false,
		],

		'testShouldWPNonceAysWhenNonceInvalid' => [
			'config' => [
				'nonce'   => 'invalid',
				'cap'     => null,
				'mobile'  => false,
				'referer' => false,
			],
			'expected' => false,
		],

		'testShouldWPDieWhenCurrentUserCant' => [
			'config' => [
				'nonce'   => 'rocket_generate_critical_css',
				'cap'     => false,
				'mobile'  => false,
				'referer' => false,
			],
			'expected' => false,
		],

		'testShouldGenerateWhenCurrentUserCanAndNoMobileAndRocketPage' => [
			'config' => [
				'nonce'   => 'rocket_generate_critical_css',
				'cap'     => 'rocket_regenerate_critical_css',
				'mobile'  => false,
				'referer' => 'http://example.com/wp-admin/options-general.php?page=wprocket',
			],
			'expected' => true,
		],

		'testShouldGenerateWhenCurrentUserCanAndMobileAndRocketPage' => [
			'config' => [
				'nonce'   => 'rocket_generate_critical_css',
				'cap'     => 'rocket_regenerate_critical_css',
				'mobile'  => true,
				'referer' => 'http://example.com/wp-admin/options-general.php?page=wprocket',
			],
			'expected' => true,
		],

		'testShouldGenerateWhenCurrentUserCanAndNoMobileAndNoRocketPage' => [
			'config' => [
				'nonce'   => 'rocket_generate_critical_css',
				'cap'     => 'rocket_regenerate_critical_css',
				'mobile'  => false,
				'referer' => 'http://example.com/wp-admin/options-general.php',
			],
			'expected' => true,
		],

		'testShouldGenerateWhenCurrentUserCanAndMobileAndNoRocketPage' => [
			'config' => [
				'nonce'   => 'rocket_generate_critical_css',
				'cap'     => 'rocket_regenerate_critical_css',
				'mobile'  => true,
				'referer' => 'http://example.com/wp-admin/options-general.php',
			],
			'expected' => true,
		],
	],
];
