<?php
/**
 * Fixtures for Render::render_navigation().
 */

// Builds a navigation item the way Render::render_navigation() normalizes it.
$rocket_item = function ( string $id, string $group = '' ): array {
	return [
		'id'               => $id,
		'title'            => $id,
		'menu_description' => '',
		'class'            => '',
		'group'            => $group,
		'url'              => '',
		'target'           => '',
	];
};

// Builds a navigation item as registered, without the defaults.
$rocket_raw = function ( string $id, string $group = '' ) use ( $rocket_item ): array {
	$data = $rocket_item( $id, $group );

	return array_filter( $data, 'strlen' );
};

return [
	'shouldNestGroupedItemsInDisplayOrder'               => [
		'config'   => [
			'navigation' => [
				'advanced_cache' => $rocket_raw( 'advanced_cache', 'cache' ),
				'dashboard'      => $rocket_raw( 'dashboard' ),
				'preload'        => $rocket_raw( 'preload', 'cache' ),
			],
		],
		'expected' => [
			[ 'type' => 'item' ] + $rocket_item( 'dashboard' ),
			[
				'type'             => 'group',
				'id'               => 'cache',
				'title'            => 'Cache',
				'menu_description' => 'Content caching',
				'children'         => [
					$rocket_item( 'preload', 'cache' ),
					$rocket_item( 'advanced_cache', 'cache' ),
				],
			],
		],
	],
	'shouldKeepKnownEntriesInMenuOrder'                  => [
		'config'   => [
			'navigation' => [
				'account'   => $rocket_raw( 'account' ),
				'media'     => $rocket_raw( 'media', 'page_optimization' ),
				'page_cdn'  => $rocket_raw( 'page_cdn' ),
				'dashboard' => $rocket_raw( 'dashboard' ),
			],
		],
		'expected' => [
			[ 'type' => 'item' ] + $rocket_item( 'dashboard' ),
			[ 'type' => 'item' ] + $rocket_item( 'page_cdn' ),
			[
				'type'             => 'group',
				'id'               => 'page_optimization',
				'title'            => 'Page Optimization',
				'menu_description' => 'Image, JavaScript & CSS',
				'children'         => [
					$rocket_item( 'media', 'page_optimization' ),
				],
			],
			[ 'type' => 'item' ] + $rocket_item( 'account' ),
		],
	],
	'shouldAppendThirdPartyItemWithoutGroupAsStandalone' => [
		'config'   => [
			'navigation' => [
				'my_addon'  => $rocket_raw( 'my_addon' ),
				'dashboard' => $rocket_raw( 'dashboard' ),
			],
		],
		'expected' => [
			[ 'type' => 'item' ] + $rocket_item( 'dashboard' ),
			[ 'type' => 'item' ] + $rocket_item( 'my_addon' ),
		],
	],
	'shouldKeepItemWithUnknownGroupAsStandalone'         => [
		'config'   => [
			'navigation' => [
				'my_addon' => $rocket_raw( 'my_addon', 'unknown_group' ),
			],
		],
		'expected' => [
			[ 'type' => 'item' ] + $rocket_item( 'my_addon', 'unknown_group' ),
		],
	],
	'shouldAppendThirdPartyItemAfterKnownGroupItems'     => [
		'config'   => [
			'navigation' => [
				'my_addon' => $rocket_raw( 'my_addon', 'cache' ),
				'preload'  => $rocket_raw( 'preload', 'cache' ),
			],
		],
		'expected' => [
			[
				'type'             => 'group',
				'id'               => 'cache',
				'title'            => 'Cache',
				'menu_description' => 'Content caching',
				'children'         => [
					$rocket_item( 'preload', 'cache' ),
					$rocket_item( 'my_addon', 'cache' ),
				],
			],
		],
	],
	'shouldNotRenderGroupWithoutItems'                   => [
		'config'   => [
			'navigation' => [
				'dashboard' => $rocket_raw( 'dashboard' ),
			],
		],
		'expected' => [
			[ 'type' => 'item' ] + $rocket_item( 'dashboard' ),
		],
	],
];
