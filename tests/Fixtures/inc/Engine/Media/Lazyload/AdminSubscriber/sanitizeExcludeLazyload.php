<?php

return [
	'shouldReturnInputWithEmptyExcludeLazyload' => [
		'input' => [
            'minify_css' => 0,
        ],
		'expected' => [
            'minify_css'        => 0,
			'exclude_lazyload'  => [],
		]
	],
	'shouldReturnInputWithSanitizedExcludeLazyload' => [
		'input' => [
            'minify_css'       => 0,
			'exclude_lazyload' => "lazy\nexample.org\n/wp-content/plugins/test/test.jpg\ndata-image",
		],
		'expected' => [
            'minify_css'        => 0,
			'exclude_lazyload'  => [
                'lazy',
                'example.org',
                '/wp-content/plugins/test/test.jpg',
                'data-image',
            ],
		]
	],
];
