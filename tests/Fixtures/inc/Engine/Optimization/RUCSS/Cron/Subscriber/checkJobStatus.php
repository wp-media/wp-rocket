<?php

$success_response = json_encode( [
	"name" => "rucss_jobs",
	"data" => [
		"url" => "https://example.org/",
		"use_cache" => false,
		"is_mobile" => false,
		"continent_code" => "EU",
		"queue_name" => "rucssJob_EU_Home",
		"is_home" => true,
		"pluginSafeList" => [
		],
		"skipAttrs" => [
		],
		"start_at" => 1691650637819
	],
	"opts" => [
		"attempts" => 0,
		"delay" => 0,
		"removeOnFail" => [
			"age" => 1800
		],
		"removeOnComplete" => [
			"age" => 1800
		]
	],
	"id" => "6026944",
	"progress" => 0,
	"returnvalue" => [
		"code" => 200,
		"status" => "completed",
		"elapsed_time" => "467ms",
		"coverage_time" => "450ms",
		"purge_time" => "17ms",
		"message" => "Treeshaked successfully!",
		"contents" => [
			"success" => true,
			"shakedCSS" => "body{background-color:#f0f0f2;margin:0;padding:0;font-family:-apple-system,system-ui,BlinkMacSystemFont,\"Segoe UI\",\"Open Sans\", \"Helvetica Neue\",Helvetica,Arial,sans-serif}div{width:600px;margin:5em auto;padding:2em;background-color:#fdfdff;border-radius:.5em;box-shadow:2px 3px 7px 2px rgba(0,0,0,.02)}a:link,a:visited{color:#38488f;text-decoration:none}@media (max-width:700px){div{margin:0 auto;width:auto}}",
			"shakedCSS_size" => 409,
			"orginalCSS_size" => 650
		],
		"server_ip" => "51.178.134.82"
	],
	"stacktrace" => [
	],
	"attemptsMade" => 1,
	"delay" => 0,
	"timestamp" => 1691650637819,
	"finishedOn" => 1691650638313,
	"processedOn" => 1691650637834
]);

$error_response = json_encode([
	"name" => "rucss_jobs",
	"data" => [
		"url" => "https://testrocket.cloudaccess.host/fr/",
		"use_cache" => false,
		"is_mobile" => false,
		"continent_code" => "EU",
		"queue_name" => "rucssJob_EU_Home",
		"is_home" => true,
		"pluginSafeList" => [
		],
		"skipAttrs" => [
		],
		"start_at" => 1691650390780
	],
	"opts" => [
		"attempts" => 0,
		"delay" => 0,
		"removeOnFail" => [
			"age" => 1800
		],
		"removeOnComplete" => [
			"age" => 1800
		]
	],
	"id" => "6026821",
	"progress" => 0,
	"returnvalue" => [
		"code" => 500,
		"status" => "failed",
		"error" => "Error",
		"message" => "net::ERR_NAME_NOT_RESOLVED at https://testrocket.cloudaccess.host/fr/"
	],
	"stacktrace" => [
	],
	"attemptsMade" => 1,
	"delay" => 0,
	"timestamp" => 1691650390780,
	"finishedOn" => 1691650390888,
	"processedOn" => 1691650390781
]);

$success_response_create = json_encode( [
	"code" => 200,
	"message" => "Added to Queue successfully.",
	"contents" => [
		"jobId" => "OVH_EU--6026944",
		"queueName" => "EU",
		"isHome" => true,
		"queueFullName" => "rucssJob_EU_Home"
	]
]);

return [
	'structure' => [],
	'test_data' => [
		'rowShouldShouldReturnCss' => [
			'config' => [
				'hash' => 'hash',
				'row' => [
					'url' => 'https://example.org',
					'job_id' => '123',
					'queue_name' => 'queue',
				],
				'request' => [
					'url' => 'http://localhostrucss-job',
					'method' => 'GET',
					'response' => [
						'response' => [
							'code' => 200,
						],
						'body' => $success_response
					]
				]
			],
			'expected' => [
				'rows' => [
					[
						'status' => 'completed',
						'job_id' => '123',
						'queue_name' => 'queue',
						'hash' => 'hash',
					]
				],
				'files' => [
					'/wp-content/cache/used-css/1/h/a/s/h.css.gz' => [
						'exists' => true,
					]
				]
			]
		],
		'408ShouldRecreateJob' => [
			'config' => [
				'hash' => 'hash',
				'row' => [
					'url' => 'https://example.org',
					'job_id' => '123',
					'queue_name' => 'queue',
				],
				'request' => [
					'url' => 'http://localhostrucss-job',
					'method' => 'GET',
					'response' => [
						'response' => [
							'code' => 408,
							'message' => 'bad json'
						],
						'body' => $error_response
					]
				],
				'create' => [
					'url' => 'http://localhostrucss-job',
					'method' => 'POST',
					'response' => [
						'response' => [
							'code' => 200,
						],
						'body' => $success_response_create
					]
				]
			],
			'expected' => [
				'rows' => [
					[
						'status' => 'to-submit',
						'job_id' => '',
						'retries' => 0,
						'queue_name' => 'queue',
						'hash' => '',
					]
				],
				'files' => [
					'/wp-content/cache/used-css/1/h/a/s/h.css.gz' => [
						'exists' => false,
					]
				]
			]
		],
		'400ShouldRetry' =>[
			'config' => [
				'hash' => 'hash',
				'row' => [
					'url' => 'https://example.org',
					'job_id' => '123',
					'queue_name' => 'queue',
				],
				'request' => [
					'url' => 'http://localhostrucss-job',
					'method' => 'GET',
					'response' => [
						'response' => [
							'code' => 400,
							'message' => 'bad json'
						],
						'body' => $error_response
					]
				],
				'create' => [
					'url' => 'http://localhostrucss-job',
					'method' => 'POST',
					'response' => [
						'response' => [
							'code' => 200,
						],
						'body' => $success_response_create
					]
				]
			],
			'expected' => [
				'rows' => [
					[
						'status' => 'pending',
						'job_id' => '123',
						'queue_name' => 'queue',
						'hash' => '',
						'retries' => 1
					]
				],
				'files' => [
					'/wp-content/cache/used-css/1/h/a/s/h.css.gz' => [
						'exists' => false,
					]
				]
			]
		],
		'404ShouldFail' =>[
			'config' => [
				'hash' => 'hash',
				'row' => [
					'url' => 'https://example.org',
					'job_id' => '123',
					'queue_name' => 'queue',
				],
				'request' => [
					'url' => 'http://localhostrucss-job',
					'method' => 'GET',
					'response' => [
						'response' => [
							'code' => 404,
							'message' => 'Job not found'
						],
						'body' => $error_response
					]
				],
				'create' => [
					'url' => 'http://localhostrucss-job',
					'method' => 'POST',
					'response' => [
						'response' => [
							'code' => 200,
						],
						'body' => $success_response_create
					]
				]
			],
			'expected' => [
				'rows' => [
					[
						'status' => 'failed',
						'job_id' => '123',
						'queue_name' => 'queue',
						'hash' => '',
					]
				],
				'files' => [
					'/wp-content/cache/used-css/1/h/a/s/h.css.gz' => [
						'exists' => false,
					]
				]
			]
		],
		'500ShouldFail' =>[
			'config' => [
				'hash' => 'hash',
				'row' => [
					'url' => 'https://example.org',
					'job_id' => '123',
					'queue_name' => 'queue',
				],
				'request' => [
					'url' => 'http://localhostrucss-job',
					'method' => 'GET',
					'response' => [
						'response' => [
							'code' => 500,
							'message' => 'error'
						],
						'body' => $error_response
					]
				],
				'create' => [
					'url' => 'http://localhostrucss-job',
					'method' => 'POST',
					'response' => [
						'response' => [
							'code' => 200,
						],
						'body' => $success_response_create
					]
				]
			],
			'expected' => [
				'rows' => [
					[
						'status' => 'failed',
						'job_id' => '123',
						'queue_name' => 'queue',
						'hash' => '',
					]
				],
				'files' => [
					'/wp-content/cache/used-css/1/h/a/s/h.css.gz' => [
						'exists' => false,
					]
				]
			]
		],
		'422ShouldFail' =>[
			'config' => [
				'hash' => 'hash',
				'row' => [
					'url' => 'https://example.org',
					'job_id' => '123',
					'queue_name' => 'queue',
				],
				'request' => [
					'url' => 'http://localhostrucss-job',
					'method' => 'GET',
					'response' => [
						'response' => [
							'code' => 422,
							'message' => 'error'
						],
						'body' => $error_response
					]
				],
				'create' => [
					'url' => 'http://localhostrucss-job',
					'method' => 'POST',
					'response' => [
						'response' => [
							'code' => 200,
						],
						'body' => $success_response_create
					]
				]
			],
			'expected' => [
				'rows' => [
					[
						'status' => 'failed',
						'job_id' => '123',
						'queue_name' => 'queue',
						'hash' => '',
					]
				],
				'files' => [
					'/wp-content/cache/used-css/1/h/a/s/h.css.gz' => [
						'exists' => false,
					]
				]
			]
		],
	]
];
