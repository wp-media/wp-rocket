<?php

return [
	'vfs_dir' => 'wp-content/themes/',

	'test_data' => [
		'bailoutWhenRUCSSDisabled' => [
			'config'   => [
				'rucss_option' => false,
			],
			'expected' => [
				'notice_show' => false,
			],
		],

		'bailoutWhenUserDoesnotHaveCapability' => [
			'config'   => [
				'rucss_option' => true,
				'capability'   => false,
			],
			'expected' => [
				'notice_show' => false,
			],
		],

		'bailoutWhenTransientIsNotThere' => [
			'config'   => [
				'rucss_option'     => true,
				'capability'       => true,
				'transient_return' => false,
			],
			'expected' => [
				'notice_show' => false,
			],
		],

		'success' => [
			'config'   => [
				'rucss_option'     => true,
				'capability'       => true,
				'transient_return' => true,
			],
			'expected' => [
				'notice_show' => true,
				'notice_details' => [
					'status'         => 'info',
					'dismiss_button' => 'rocket_divi_notice',
					'message'        =>
						'<strong>WP Rocket:</strong> Your Divi template was updated. Clear the Used CSS if the layout, design or CSS styles were changed.'
				],
				'notice_html' => '',
			],
		],
	],

];
