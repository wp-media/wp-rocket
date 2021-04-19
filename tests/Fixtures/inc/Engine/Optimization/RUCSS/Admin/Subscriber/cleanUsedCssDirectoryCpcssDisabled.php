<?php

return [

	'test_data' => [
		'BailoutWhenCurrentUserCant' => [
			'input' => [
				'cap' => false,
				'old_value' => [
					'async_css' => true,
				],
				'new_value' => [
					'async_css' => true,
				],
			],
			'expected' => [
				'cleaned' => false,
				'reason' => 'cap'
			],
		],

		'BailoutWhenRUCSSDisabled' => [
			'input' => [
				'cap' => true,
				'remove_unused_css' => false,
				'old_value' => [
					'async_css' => true,
				],
				'new_value' => [
					'async_css' => true,
				],
			],
			'expected' => [
				'cleaned' => false,
				'reason' => 'option'
			],
		],

		'BailoutWhenCPCSSNotInsideOldValue' => [
			'input' => [
				'cap' => true,
				'remove_unused_css' => true,
				'old_value' => [],
				'new_value' => [
					'async_css' => true,
				],
			],
			'expected' => [
				'cleaned' => false,
				'reason' => 'cpcss'
			],
		],

		'BailoutWhenCPCSSNotInsideNewValue' => [
			'input' => [
				'cap' => true,
				'remove_unused_css' => true,
				'old_value' => [
					'async_css' => true,
				],
				'new_value' => [],
			],
			'expected' => [
				'cleaned' => false,
				'reason' => 'cpcss'
			],
		],

		'BailoutWhenCPCSSChangedFromDisabledToEnabled' => [
			'input' => [
				'cap' => true,
				'remove_unused_css' => true,
				'old_value' => [
					'async_css' => false,
				],
				'new_value' => [
					'async_css' => true,
				],
			],
			'expected' => [
				'cleaned' => false,
				'reason' => 'cpcss'
			],
		],

		'CleanWhenCPCSSChangedFromEnabledToDisabled' => [
			'input' => [
				'cap' => true,
				'remove_unused_css' => true,
				'old_value' => [
					'async_css' => true,
				],
				'new_value' => [
					'async_css' => false,
				],
			],
			'expected' => [
				'cleaned' => true,
			],
		],

	],

];
