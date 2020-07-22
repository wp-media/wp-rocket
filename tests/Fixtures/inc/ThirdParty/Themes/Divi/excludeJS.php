<?php

return [
	'shouldBailOutWhenBuilderConstantNotSet' => [
		'config'   => [
			'builder-constant' => false,
			'excluded-paths'   => [
				'/path/to/some/other/excluded/files/sample.min.js',
			],
		],
		'expected' => [
			'/path/to/some/other/excluded/files/sample.min.js',
		],
	],

	'shouldAddBuilderJSToExcludedArrayWhenBuilderConstSet' => [
		'config' => [
			'builder-constant' => 'https://example.com/path/to/et-builder-uri',
			'excluded-paths' => [
				'/path/to/some/other/excluded/files/sample.min.js',
			]
		],
		'expected' => [
			'/path/to/some/other/excluded/files/sample.min.js',
			'/path/to/et-builder-uri/scripts/salvattore.min.js',
		],
	],
];
