<?php

return [
	'shouldExcludeCSSFilesRucssDisabled' => [
		'config'   => [
			'rucss_enabled'    => false,
			'excluded-paths'   => [],
		],
		'expected' => [
			'/wp-content/et-cache/(.*).css',
		],
	],
	'shouldExcludeCSSFilesRucssEnabled' => [
		'config'   => [
			'rucss_enabled'    => true,
			'excluded-paths'   => [],
		],
		'expected' => [],
	],
];
