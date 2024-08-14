<?php

return [
	'shouldAddMenuToAdminBar' => [
		'config'   => [
			'rocket_valid_key' => true,
			'environment'      => 'production',
			'admin'            => true,
			'menu_id'          => 'random-menu-id',
			'action'           => 'menu-action',
			'title'            => 'Menu title'
		],
		'expected' => [
			'id'    => 'random-menu-id',
			'title' => 'Menu title',
		],
	],
	'testShouldReturnNullWhenAdminIsFalse' => [
		'config'   => [
			'rocket_valid_key' => true,
			'environment'      => 'production',
			'admin'            => false,
			'menu_id'          => 'random-menu-id',
			'action'           => 'menu-action',
			'title'            => 'Menu title'
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenInvalidKey' => [
		'config'   => [
			'rocket_valid_key' => false,
			'environment'      => 'production',
			'admin'            => true,
			'menu_id'          => 'random-menu-id',
			'action'           => 'menu-action',
			'title'            => 'Menu title'
		],
		'expected' => null,
	],
];
