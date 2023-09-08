<?php
return [
    '' => [
        'config' => [
			'boxes' => [],
			'user_id' => 10,
			'can' => true,
			'transient' => true,
			'notice' => true,
        ],
		'expected' => [
			'notice' => [
				'status'         => 'warning',
				'dismissible'    => '',
				'dismiss_button' => 'maybe_clear_cache_change_notice',
				'message'        => '<strong>WP Rocket:</strong> Your Elementor template was updated. Clear the Used CSS if the layout, design or CSS styles were changed.',
				'action'         => 'clear_cache',
			]
		]
    ],

];
