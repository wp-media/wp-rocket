<?php

return [
	'shouldAddMenuToAdminBar' => [
		'config'   => [
			'display_option'   => true,
			'environment'      => 'production',
			'admin'            => false,
			'menu_id'          => 'random-menu-id',
			'action'           => 'menu-action',
			'title'            => 'Menu title',
			'context'          => true,
		],
		'expected' => [
			'id'    => 'random-menu-id',
			'title' => 'Menu title',
		],
	],
	'testShouldReturnNullWhenAdminIsTrue' => [
		'config'   => [
			'display_option'   => true,
			'environment'      => 'production',
			'admin'            => true,
			'menu_id'          => 'random-menu-id',
			'action'           => 'menu-action',
			'title'            => 'Menu title',
			'context'          => true,
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenDisplayIsFalse' => [
		'config'   => [
			'display_option'   => false,
			'environment'      => 'production',
			'admin'            => true,
			'menu_id'          => 'random-menu-id',
			'action'           => 'menu-action',
			'title'            => 'Menu title',
			'context'          => true,
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenContextIsFalse' => [
		'config'   => [
			'display_option'   => true,
			'environment'      => 'production',
			'admin'            => true,
			'menu_id'          => 'random-menu-id',
			'action'           => 'menu-action',
			'title'            => 'Menu title',
			'context'          => false,
		],
		'expected' => null,
	],
];
