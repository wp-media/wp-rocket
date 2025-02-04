<?php

return [
	'testShouldBailoutWhenNotAllowed' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'results' => json_encode(
				[
					'preload_fonts' =>  [
						"Roboto" => (object) [
							 'variations' => [
								[
									'weight' => "300",
									"style"  => "normal",
									"url"    => "https://fonts.googleapis.com/css?family=Roboto",
								],
								[
									'weight' => "400",
									"style"  => "normal",
									"url"    => "https://fonts.googleapis.com/css?family=Roboto",
								],
							],
						]
					]
				],
			),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'fonts' => json_encode( [
						"Roboto" =>  [
							'variations' => [
								[
									'weight' => "300",
									"style"  => "normal",
									"url"    => "https://fonts.googleapis.com/css?family=Roboto",
								],
								[
									'weight' => "400",
									"style"  => "normal",
									"url"    => "https://fonts.googleapis.com/css?family=Roboto",
								],
							]
						],
						"OpenSans" =>  [
							'variations' => [
								[
									'weight' => "300",
									"style"  => "normal",
									"url"    => "https://fonts.googleapis.com/css?family=Open+Sans",
								],
								[
									'weight' => "400",
									"style"  => "normal",
									"url"    => "https://fonts.googleapis.com/css?family=Open+Sans",
								],
							]
						],
				] ),
				'last_accessed'  => '2024-01-01 00:00:00',
				'created_at'     => '2024-01-01 00:00:00',
				'error_message'  => ''
			],
			'result'  => true,
			'message' => 'not allowed',
		],
	],
];
