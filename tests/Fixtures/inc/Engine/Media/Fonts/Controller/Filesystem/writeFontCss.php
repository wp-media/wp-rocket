<?php
return [
	'test_data' => [
		'shouldWriteFontCss' => [
			'config' => [
				'url'              => 'https://fonts.googleapis.com/css?family=Open+Sans',
				'css_content'      => 'url(https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2);',
				'provider'         => 'google-font',
				'local_url'        => 'http://example.org/wp-content',
				'response' => [
					'headers' => [],
					'body' => json_encode( (object) [
						'success' => true,
						'result' => [],
					] ),
					'response' => [],
				],
			],
			'expected' => [
				'path'    => 'vfs://public/wp-content/cache/font/1/google-font/1/f/a/2/965d41f1515951de523cecb81f85e.css',
			]
		],
	]
];
