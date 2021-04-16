<?php

return [
	'test_data' => [

		'shouldBailoutWhenCurrentUsercant' => [
			'input' => [
				'cap' => false,
			],
			'expected' => [
				'show_notice' => false,
			],
		],

		'shouldBailoutWhenEmptyTransient' => [
			'input' => [
				'cap' => true,
				'transient' => false,
			],
			'expected' => [
				'show_notice' => false,
			],
		],

		'shouldShowNotice' => [
			'input' => [
				'cap' => true,
				'transient' => 'any value',
			],
			'expected' => [
				'show_notice' => true,
			],
		],

	],
];
