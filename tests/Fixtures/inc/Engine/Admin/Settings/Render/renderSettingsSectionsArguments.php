<?php

$help = [
	'id'  => 'abc',
	'url' => 'https://docs.example.org/help',
];

return [
	'fields_container_keeps_help' => [
		'config'   => [ 'type' => 'fields_container' ],
		'expected' => [
			'help'  => $help,
			'class' => 'one two',
		],
	],
	'purge_section_keeps_help'    => [
		'config'   => [ 'type' => 'purge_cdn_cache_section' ],
		'expected' => [
			'help' => $help,
		],
	],
];
