<?php

$data = "{\"rucss_inline_atts_exclusions\":[\"rocket-lazyload-inline-css\",\"divi-style-parent-inline-inline-css\",\"gsf-custom-css\",\"extra-style-inline-inline-css\",\"woodmart-inline-css-inline-css\",\"woodmart_shortcodes-custom-css\",\"rs-plugin-settings-inline-css\",\"divi-style-inline-inline-css\"],\"rucss_inline_content_exclusions\":[\".wp-container-\",\".wp-elements-\",\"#wpv-expandable-\"]}";

return [
	'test_data' => [
		'testShouldReturnExpiredLicense' => [
			'expired' => true,
			'exclusions_list_result' => [
				'code' => 206,
				'body' => $data
			],
			'expected' => [
				'success' => false,
				'data'    => '',
				'message' => 'You need an active license to get the latest version of the lists from our server.'
			],
		],
		'testShouldReturnListsAreUpToDate' => [
			'expired' => false,
			'exclusions_list_result' => [
				'code' => 206,
				'body' => $data
			],
			'expected'               => [
				'success' => true,
				'data'    => '',
				'message' => 'Lists are up to date.'
			],
		],
		'testShouldReturnListsAreSuccessfullyUpdated' => [
			'expired' => false,
			'exclusions_list_result' => [
				'not_saved' => false,
				'code' => 200,
				'body' => $data
			],
			'expected'               => [
				'success' => true,
				'data'    => $data,
				'message' => 'Lists are successfully updated'
			],
		],
		'testShouldReturnCouldNotGetLists'            => [
			'expired' => false,
			'exclusions_list_result' => [
				'code' => 500,
			],
			'expected'               => [
				'success' => false,
				'data'    => '',
				'message' => 'Could not get updated lists from server.'
			],
		],
		'testShouldReturnCouldNotUpdateLists'         => [
			'expired' => false,
			'exclusions_list_result' => [
				'not_saved' => true,
				'code'      => 200,
				'body'      => $data,
			],
			'expected'               => [
				'success' => false,
				'data'    => '',
				'message' => 'Could not update lists.'
			],
		],
	]
];
