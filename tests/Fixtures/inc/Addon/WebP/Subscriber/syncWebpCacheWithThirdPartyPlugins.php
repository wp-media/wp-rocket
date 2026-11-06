<?php

return [
	'shouldNotDisableWebpCacheOptionWhenWebPNotEnabled' => [
		'option' => false,
		'serving' => true,
		'expected' => false,
	],
	'shouldNotDisableWebpCacheOptionWhenNoPluginServingWebP' => [
		'option' => true,
		'serving' => false,
		'expected' => false,
	],
	'shouldDisableWebpCacheOptionWhenWebPEnabledAndPluginServingWebP' => [
		'option' => true,
		'serving' => true,
		'expected' => true,
	],
];
