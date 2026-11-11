<?php

$help = [
	'id'  => 'abc',
	'url' => 'https://docs.example.org/help',
];

return [
	'fields_container_available'   => [
		'config'   => [ 'type' => 'fields_container', 'available' => true ],
		'expected' => [
			'help'               => $help,
			'has_hide_need_help' => false,
			'class'              => 'one two',
		],
	],
	'fields_container_not_available' => [
		'config'   => [ 'type' => 'fields_container', 'available' => false ],
		'expected' => [
			'help'               => '',
			'has_hide_need_help' => false,
		],
	],
	'purge_section_not_available'  => [
		'config'   => [ 'type' => 'purge_cdn_cache_section', 'available' => false ],
		'expected' => [
			'help'               => $help,
			'has_hide_need_help' => true,
		],
	],
	'purge_section_available'      => [
		'config'   => [ 'type' => 'purge_cdn_cache_section', 'available' => true ],
		'expected' => [
			'help'               => $help,
			'has_hide_need_help' => false,
		],
	],
];
