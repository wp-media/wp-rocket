<?php

$data_one = json_encode( [
	'rucss_inline_content_exclusions' => [
		'.wp-container-',
		'.wp-elements-',
	],
] );

$data_two = "{\"rucss_inline_atts_exclusions\":[\"rocket-lazyload-inline-css\",\"divi-style-parent-inline-inline-css\",\"gsf-custom-css\",\"extra-style-inline-inline-css\",\"woodmart-inline-css-inline-css\",\"woodmart_shortcodes-custom-css\",\"rs-plugin-settings-inline-css\",\"divi-style-inline-inline-css\"],\"rucss_inline_content_exclusions\":[\".wp-container-\",\".wp-elements-\",\"#wpv-expandable-\"]}";

return [
	'structure' => [
		'wp-content' => [
			'wp-rocket-config' => [
				'dynamic-lists.json' => $data_one,
			],
			'plugins' => [
				'wp-rocket' => [
					'dynamic-lists.json' => $data_one,
				],
			],
		],
	],
	'test_data' => [
		'shouldReturnListsAreUpToDate'            => [
			'api_response' => [
				'response' => [
					'code' => 206,
				],
				'body' => $data_one
			],
			'expected' => [
				'data' => $data_one,
				'transient' => false,
			],
		],
		'shouldReturnListsAreSuccessfullyUpdated' => [
			'api_response' => [
				'response' => [
					'code' => 200,
				],
				'body' => $data_two
			],
			'expected' => [
				'data' => $data_two,
				'transient' => json_decode( $data_two ),
			],
		],
		'shouldReturnCouldNotGetLists'            => [
			'api_response' => [
				'response' => [
					'code' => 500,
					'message' => 'Server issue',
				],
			],
			'expected' => [
				'data' => $data_one,
				'transient' => false,
			],
		],
	],
];
