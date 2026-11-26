<?php

$action = [
	'id'           => 'purge_cache',
	'title'        => 'Cache files',
	'description'  => 'Clear and preload all the cache files.',
	'button_label' => 'Clear and preload',
	'button_url'   => 'http://example.org/wp-admin/admin-post.php?action=purge_cache',
];
$item   = array_merge( $action, [ 'disabled' => false ] );

return [
	'testShouldRenderNothingWithoutAction' => [
		'config'   => [ 'actions' => [] ],
		'expected' => [ 'items' => [], 'html' => '' ],
	],
	'testShouldRenderTheQuickActionsCategory' => [
		'config'   => [ 'actions' => [ $action ] ],
		'expected' => [ 'items' => [ $item ], 'html' => '<div>Quick Actions</div>' ],
	],
	'testShouldDefaultTheButtonLabelToTheTitle' => [
		'config'   => [ 'actions' => [ array_merge( $action, [ 'button_label' => '' ] ) ] ],
		'expected' => [
			'items' => [ array_merge( $item, [ 'button_label' => 'Cache files' ] ) ],
			'html'  => '<div>Quick Actions</div>',
		],
	],
	'testShouldSkipMalformedActions' => [
		'config'   => [
			'actions' => [
				'not an array',
				[ 'title' => 'No url' ],
				[ 'button_url' => 'http://example.org/', 'title' => '' ],
				[ 'title' => 'Bad url', 'button_url' => 'javascript:alert(1)' ],
				[ 'id' => 'only', 'title' => 'Only', 'button_url' => 'http://example.org/' ],
			],
		],
		'expected' => [
			'items' => [
				[
					'id'           => 'only',
					'title'        => 'Only',
					'description'  => '',
					'button_label' => 'Only',
					'button_url'   => 'http://example.org/',
					'disabled'     => false,
				],
			],
			'html'  => '<div>Quick Actions</div>',
		],
	],
	'testShouldRenderADisabledActionWithoutUrl' => [
		'config'   => [
			'actions' => [
				[
					'id'           => 'rocket_clean_saas',
					'title'        => 'Used CSS',
					'button_label' => 'Clear used CSS',
					'button_url'   => 'javascript:alert(1)',
					'disabled'     => true,
				],
			],
		],
		'expected' => [
			'items' => [
				[
					'id'           => 'rocket_clean_saas',
					'title'        => 'Used CSS',
					'description'  => '',
					'button_label' => 'Clear used CSS',
					'button_url'   => '',
					'disabled'     => true,
				],
			],
			'html'  => '<div>Quick Actions</div>',
		],
	],
	'testShouldRenderNothingWhenEveryActionIsMalformed' => [
		'config'   => [ 'actions' => [ 'nope', [ 'title' => 'No url' ] ] ],
		'expected' => [ 'items' => [], 'html' => '' ],
	],
];
