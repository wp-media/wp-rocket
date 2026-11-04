<?php

$available_contains     = [ 'data-wap-prompt', 'wpr-infoAction--help' ];
$not_available_contains = [ 'Section title' ];
$not_available_not_cont = [ 'data-wap-prompt', 'wpr-infoAction--help', 'Need Help?' ];

return [
	'fields_container_available'              => [
		'config'   => [ 'type' => 'fields_container', 'available' => true ],
		'expected' => [ 'contains' => $available_contains, 'not_contains' => [] ],
	],
	'fields_container_not_available'          => [
		'config'   => [ 'type' => 'fields_container', 'available' => false ],
		'expected' => [ 'contains' => $not_available_contains, 'not_contains' => $not_available_not_cont ],
	],
	'addons_container_available'              => [
		'config'   => [ 'type' => 'addons_container', 'available' => true ],
		'expected' => [ 'contains' => $available_contains, 'not_contains' => [] ],
	],
	'addons_container_not_available'          => [
		'config'   => [ 'type' => 'addons_container', 'available' => false ],
		'expected' => [ 'contains' => $not_available_contains, 'not_contains' => $not_available_not_cont ],
	],
	'nocontainer_with_title_available'        => [
		'config'   => [ 'type' => 'nocontainer_with_title', 'available' => true ],
		'expected' => [ 'contains' => $available_contains, 'not_contains' => [] ],
	],
	'nocontainer_with_title_not_available'    => [
		'config'   => [ 'type' => 'nocontainer_with_title', 'available' => false ],
		'expected' => [ 'contains' => $not_available_contains, 'not_contains' => $not_available_not_cont ],
	],
	'purge_section_available'                 => [
		'config'   => [ 'type' => 'purge_cdn_cache_section', 'available' => true ],
		'expected' => [
			'contains'     => array_merge( $available_contains, [ 'Learn more about Purge', 'Need Help?' ] ),
			'not_contains' => [],
		],
	],
	'purge_section_not_available'             => [
		'config'   => [ 'type' => 'purge_cdn_cache_section', 'available' => false ],
		'expected' => [
			'contains'     => array_merge( $not_available_contains, [ 'wpr-cdn-purge__learn-more', 'Learn more about Purge', 'href="https://docs.example.org/help"' ] ),
			'not_contains' => $not_available_not_cont,
		],
	],
	'your_own_cdn_available'                  => [
		'config'   => [ 'type' => 'your_own_cdn', 'available' => true ],
		'expected' => [ 'contains' => $available_contains, 'not_contains' => [] ],
	],
	'your_own_cdn_not_available'              => [
		'config'   => [ 'type' => 'your_own_cdn', 'available' => false ],
		'expected' => [ 'contains' => $not_available_contains, 'not_contains' => $not_available_not_cont ],
	],
	'rocketcdn_free_available'                => [
		'config'   => [ 'type' => 'rocketcdn_free', 'available' => true ],
		'expected' => [ 'contains' => $available_contains, 'not_contains' => [] ],
	],
	'rocketcdn_free_not_available'            => [
		'config'   => [ 'type' => 'rocketcdn_free', 'available' => false ],
		'expected' => [ 'contains' => $not_available_contains, 'not_contains' => $not_available_not_cont ],
	],
	'rocketcdn_paid_available'                => [
		'config'   => [ 'type' => 'rocketcdn_paid', 'available' => true ],
		'expected' => [ 'contains' => $available_contains, 'not_contains' => [] ],
	],
	'rocketcdn_paid_not_available'            => [
		'config'   => [ 'type' => 'rocketcdn_paid', 'available' => false ],
		'expected' => [ 'contains' => $not_available_contains, 'not_contains' => $not_available_not_cont ],
	],
];
