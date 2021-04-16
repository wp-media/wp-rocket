<?php

return [
	'test_data' => [

		'shouldNotTruncateUnusedCSSWithNotExistsNonce' => [
			'input' => [
				'remove_unused_css' => false,
				'nonce' => null,
			],
			'expected' => [
				'truncated' => false,
				'reason' => 'nonce'
			],
		],

		'shouldNotTruncateUnusedCSSWithInvalidNonce' => [
			'input' => [
				'remove_unused_css' => false,
				'nonce' => 'invalid',
			],
			'expected' => [
				'truncated' => false,
				'reason' => 'nonce'
			],
		],

		'shouldNotTruncateUnusedCSSWhenCurrentUserCant' => [
			'input' => [
				'remove_unused_css' => false,
				'nonce' => 'rocket_clear_usedcss',
				'cap'     => false,
			],
			'expected' => [
				'truncated' => false,
				'reason' => 'cap'
			],
		],

		'shouldNotTruncateUnusedCSSWhenOptionDisabled' => [
			'input' => [
				'remove_unused_css' => false,
				'nonce' => 'rocket_clear_usedcss',
				'cap'     => true,
				'option_enabled' => false,
			],
			'expected' => [
				'truncated' => false,
				'reason' => 'option',
				'norice_details' => [
					'status'  => 'error',
					'message' => 'Used CSS option is not enabled!',
				],
			],
		],

		'shouldTruncateUnusedCSS' => [
			'input' => [
				'remove_unused_css' => false,
				'nonce' => 'rocket_clear_usedcss',
				'cap'     => true,
				'option_enabled' => true,
			],
			'expected' => [
				'truncated' => true,
				'norice_details' => [
					'status'  => 'success',
					'message' => 'Used CSS cache cleared!',
				],
			],
		],

	],
];
