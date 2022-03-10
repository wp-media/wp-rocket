<?php

return [
	'test_data' => [

		'shouldBailoutWhenCurrentUsercant' => [
			'input' => [
				'cap' => false,
				'enabled' => false,
			],
			'expected' => [
				'show_notice' => false,
				'notice_html' => '<div class="notice notice-success is-dismissible"><p>Used CSS cache cleared!</p></div>',
			],
		],

		'shouldBailoutWhenOptionDisabled' => [
			'input' => [
				'cap' => false,
				'enabled' => false,
			],
			'expected' => [
				'show_notice' => false,
				'notice_html' => '<div class="notice notice-success is-dismissible"><p>Used CSS cache cleared!</p></div>',
			],
		],

		'shouldBailoutWhenEmptyTransient' => [
			'input' => [
				'cap' => true,
				'enabled' => true,
				'transient' => false,
			],
			'expected' => [
				'show_notice' => false,
				'notice_html' => '<div class="notice notice-success is-dismissible"><p>Used CSS cache cleared!</p></div>',
			],
		],

		'shouldShowNotice' => [
			'input' => [
				'cap' => true,
				'enabled' => true,
				'transient' => [
					'status'  => 'success',
					'message' => 'Used CSS cache cleared!', 'rocket',
				],
			],
			'expected' => [
				'show_notice' => true,
				'notice_html' => '<div class="notice notice-success is-dismissible"><p>Used CSS cache cleared!</p></div>',
			],
		],

	],
];
