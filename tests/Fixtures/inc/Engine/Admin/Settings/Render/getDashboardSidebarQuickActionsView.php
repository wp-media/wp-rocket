<?php

return [
	'testShouldRenderTheActionsAsButtonLinks' => [
		'config'   => [
			'actions' => [
				[
					'id'           => 'first',
					'title'        => 'First action',
					'description'  => 'First <b>description</b>',
					'button_label' => 'Go first',
					'button_url'   => 'http://example.org/wp-admin/admin-post.php?action=first&_wpnonce=abc',
				],
				[
					'id'           => 'second',
					'title'        => 'Second action',
					'description'  => 'Second description',
					'button_label' => 'Go second',
					'button_url'   => 'http://example.org/second',
				],
			],
		],
		'expected' => [
			'contains'     => [
				'Quick Actions',
				'wpr-Dashboard-sidebar-category wpr-Dashboard-sidebar-category--quickActions',
				'class="wpr-quickAction-title">First action</h4>',
				'First &lt;b&gt;description&lt;/b&gt;',
				'class="wpr-quickAction-actions"',
				'class="wpr-quickAction-button" href="http://example.org/wp-admin/admin-post.php?action=first&#038;_wpnonce=abc">Go first</a>',
				'class="wpr-quickAction-button" href="http://example.org/second">Go second</a>',
			],
			'not_contains' => [],
		],
	],
	'testShouldRenderADisabledActionAsADisabledButton' => [
		'config'   => [
			'actions' => [
				[
					'id'           => 'rocket_clean_saas',
					'title'        => 'Used CSS',
					'description'  => 'Clear the used CSS files.',
					'button_label' => 'Clear used CSS',
					'button_url'   => '',
					'disabled'     => true,
				],
			],
		],
		'expected' => [
			'contains'     => [
				'class="wpr-quickAction-title">Used CSS</h4>',
				'<button type="button" class="wpr-quickAction-button" disabled>Clear used CSS</button>',
			],
			'not_contains' => [ 'href=' ],
		],
	],
	'testShouldSkipMalformedActions' => [
		'config'   => [
			'actions' => [
				'not an array',
				[ 'title' => 'No url' ],
				[ 'title' => 'Bad url', 'button_url' => 'javascript:alert(1)' ],
				[ 'title' => 'Valid', 'button_url' => 'http://example.org/valid' ],
			],
		],
		'expected' => [
			'contains'     => [ 'class="wpr-quickAction-title">Valid</h4>' ],
			'not_contains' => [ 'No url', 'Bad url', 'javascript:' ],
		],
	],
];
