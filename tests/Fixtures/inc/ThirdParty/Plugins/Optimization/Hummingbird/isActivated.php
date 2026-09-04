<?php

return [
	'shouldReturnTrueWhenAdminAndPluginActive'       => [
		'config'   => [
			'is_admin'      => true,
			'plugin_active' => true,
		],
		'expected' => true,
	],
	'shouldReturnFalseWhenAdminButPluginInactive'    => [
		'config'   => [
			'is_admin'      => true,
			'plugin_active' => false,
		],
		'expected' => false,
	],
	'shouldReturnFalseWhenPluginActiveButNotAdmin'   => [
		'config'   => [
			'is_admin'      => false,
			'plugin_active' => true,
		],
		'expected' => false,
	],
	'shouldReturnFalseWhenNotAdminAndPluginInactive' => [
		'config'   => [
			'is_admin'      => false,
			'plugin_active' => false,
		],
		'expected' => false,
	],
];
