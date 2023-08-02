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
					'status'         => 'warning',
					'dismissible'    => '',
					'dismiss_button' => 'rocket_divi_notice',
					'message'        =>
						'<strong>WP Rocket:</strong> Your Divi template was updated. Clear the Used CSS if the layout, design or CSS styles were changed.'
				],
				'notice_html' => <<<HTML
<div class="notice notice-info is-dismissible">
	<p>
		<strong>WP Rocket:</strong>
		Your Divi template was updated. Clear the Used CSS if the layout, design or CSS styles were changed.
	</p>
	<p>
		<a class="rocket-dismiss " href="http://example.org/wp-admin/admin-post.php?action=rocket_ignore&amp;box=rocket_divi_notice&amp;_wpnonce=12345">
			Dismiss this notice
		</a>
	</p>
</div>
HTML
			],
		],
	],

];
