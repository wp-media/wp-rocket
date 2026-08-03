<?php

$page_rule_response = [
	'headers'  => [],
	'body'     => wp_json_encode(
		(object) [
			'success' => true,
			'result'  => (object) [
				'actions' => [
					'id' => 'cache_everything',
				],
			],
		]
	),
	'response' => '',
	'cookies'  => [],
];

$no_page_rule_response = [
	'headers'  => [],
	'body'     => wp_json_encode(
		(object) [
			'success' => true,
			'result'  => (object) [
				'actions' => [
					'id' => 'browser_check',
				],
			],
		]
	),
	'response' => '',
	'cookies'  => [],
];

$purge_response = [
	'headers'  => [],
	'body'     => wp_json_encode(
		(object) [
			'success' => true,
			'result'  => '',
		]
	),
	'response' => '',
	'cookies'  => [],
];

return [
	'testShouldDoNothingWhenNoCap'                      => [
		'config'   => [
			'cap'                => false,
			'hook'               => 'after_rocket_clean_files',
			'args'               => [
				[ 'https://example.org/hello-world/' ],
			],
			'page_rule_response' => $page_rule_response,
			'purge_response'     => $purge_response,
		],
		'expected' => null,
	],
	'testShouldDoNothingWhenNoPageRule'                 => [
		'config'   => [
			'cap'                => true,
			'hook'               => 'after_rocket_clean_files',
			'args'               => [
				[ 'https://example.org/hello-world/' ],
			],
			'page_rule_response' => $no_page_rule_response,
			'purge_response'     => $purge_response,
		],
		'expected' => null,
	],
	'testShouldPurgeHomeUrlWhenTriggeredByCleanHome'    => [
		'config'   => [
			'cap'                => true,
			'hook'               => 'after_rocket_clean_home',
			'args'               => [
				'/tmp/wp-rocket-cache/example.org',
				'',
			],
			'page_rule_response' => $page_rule_response,
			'purge_response'     => $purge_response,
		],
		'expected' => [ home_url() ],
	],
	'testShouldPurgeFilesUrlsWhenTriggeredByCleanFiles' => [
		'config'   => [
			'cap'                => true,
			'hook'               => 'after_rocket_clean_files',
			'args'               => [
				[
					'https://example.org/hello-world/',
					'https://example.org/2022/11/15/sed-laboriosam-quibusdam-aliquam-et-eius',
				],
			],
			'page_rule_response' => $page_rule_response,
			'purge_response'     => $purge_response,
		],
		'expected' => [
			'https://example.org/hello-world/',
            'https://example.org/2022/11/15/sed-laboriosam-quibusdam-aliquam-et-eius',
		],
	],
];
