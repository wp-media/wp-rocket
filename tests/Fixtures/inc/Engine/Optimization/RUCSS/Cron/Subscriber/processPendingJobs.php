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

return [
	'structure' => [],
	'test_data' => [
		'rowShouldShouldReturnCss' => [
			'config' => [
				'row' => [
					'url' => 'https://example.org',
					'job_id' => '123',
					'queue_name' => 'queue',
				],
				'request' => [
					'url' => 'https://saas.wp-rocket.me/rucss-job',
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
					'hash' => '1234',
					'status' => 'completed',
					'id' => 1,
					'css' => '',
				],
				'files' => [
					'rucss' => [
						'exists' => true,
						'content' => file_get_contents(__DIR__ . '/CSS/rucss.css')
					]
				]
			]
		],
	]
];
