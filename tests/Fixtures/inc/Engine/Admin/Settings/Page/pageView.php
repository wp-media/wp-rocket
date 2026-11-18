<?php

return [
	'testShouldRenderSidebarWithValidKey'       => [
		'config'   => [
			'valid_key' => true,
		],
		'expected' => [
			'contains'     => [ '<aside class="wpr-Sidebar">', 'id="sidebar-hook"' ],
			'not_contains' => [ 'wpr-Content--noSidebar' ],
		],
	],
	'testShouldNotRenderSidebarWithInvalidKey' => [
		'config'   => [
			'valid_key' => false,
		],
		'expected' => [
			'contains'     => [ 'wpr-Content--noSidebar' ],
			'not_contains' => [ 'wpr-Sidebar', 'id="sidebar-hook"' ],
		],
	],
];
