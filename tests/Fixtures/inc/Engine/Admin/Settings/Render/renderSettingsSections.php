<?php

$contains = [ 'data-wap-prompt', 'wpr-infoAction--help', 'Need Help?' ];

return [
	'fields_container_keeps_need_help' => [
		'config'   => [ 'type' => 'fields_container' ],
		'expected' => [ 'contains' => $contains, 'not_contains' => [] ],
	],
	'addons_container_keeps_need_help' => [
		'config'   => [ 'type' => 'addons_container' ],
		'expected' => [ 'contains' => $contains, 'not_contains' => [] ],
	],
	'nocontainer_with_title_keeps_need_help' => [
		'config'   => [ 'type' => 'nocontainer_with_title' ],
		'expected' => [ 'contains' => $contains, 'not_contains' => [] ],
	],
	'purge_cdn_cache_section_keeps_need_help' => [
		'config'   => [ 'type' => 'purge_cdn_cache_section' ],
		'expected' => [ 'contains' => array_merge( $contains, [ 'wpr-cdn-purge__learn-more', 'Learn more about Purge' ] ), 'not_contains' => [] ],
	],
	'your_own_cdn_keeps_need_help' => [
		'config'   => [ 'type' => 'your_own_cdn' ],
		'expected' => [ 'contains' => $contains, 'not_contains' => [] ],
	],
	'rocketcdn_free_keeps_need_help' => [
		'config'   => [ 'type' => 'rocketcdn_free' ],
		'expected' => [ 'contains' => $contains, 'not_contains' => [] ],
	],
	'rocketcdn_paid_keeps_need_help' => [
		'config'   => [ 'type' => 'rocketcdn_paid' ],
		'expected' => [ 'contains' => $contains, 'not_contains' => [] ],
	],
];
