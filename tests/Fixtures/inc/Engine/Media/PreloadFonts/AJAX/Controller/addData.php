<?php

return [
	'testShouldBailoutWhenNotAllowed' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'results' => json_encode([
				'preload_fonts' => [
					'Raleway' => [
						'variations' => [
							[
								'weight' => '300',
								'style' => 'normal',
								'url' => 'https://fonts.gstatic.com/s/raleway/v34/1Ptug8zYS_SKggPNyC0IT4ttDfA.woff2',
							],
							[
								'weight' => '400',
								"style"  => 'normal',
								"url"    => 'https://fonts.googleapis.com/css?family=Roboto',
							],
						],
					],
				]
			]),
		],
		'expected' => [
			'item'    => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'fonts' => json_encode(
					[
						"Raleway" => (object) [
							'variations' => [
								[
									'weight' => "300",
									"style"  => "normal",
									'url'    => 'https://fonts.gstatic.com/s/raleway/v34/1Ptug8zYS_SKggPNyC0IT4ttDfA.woff2',
								],
								[
									'weight' => "400",
									"style"  => "normal",
									"url"    => "https://fonts.googleapis.com/css?family=Roboto",
								],
							],
						]
					],
				),
				'last_accessed'  => '2025-02-02 00:00:00',
				'created_at'     => '2025-02-02 00:00:00',
				'error_message'  => ''
			],
			'result'  => true,
			'message' => [
				'url'            => 'http://example.org',
				'is_mobile'      => false,
				'status'         => 'completed',
				'error_message'  => '',
				'fonts' => json_encode(
					[
						"Raleway" => (object) [
							'variations' => [
								[
									'weight' => "300",
									"style"  => "normal",
									'url'    => 'https://fonts.gstatic.com/s/raleway/v34/1Ptug8zYS_SKggPNyC0IT4ttDfA.woff2',
								],
								[
									'weight' => "400",
									"style"  => "normal",
									"url"    => "https://fonts.googleapis.com/css?family=Roboto",
								],
							],
						]
					],
				),
				'created_at'     => '2025-02-02 00:00:00',
				'last_accessed'  => '2025-02-02 00:00:00',
			],
		],
	],
];
