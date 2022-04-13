<?php

return [
	'test_data' => [
		'shouldCleanMinifyAllWithMinifyCss'             => [
            'option'    =>  [
                'minify_js' => 0,
                'minify_css' => 1,
            ],
		],
        'shouldCleanMinifyAllWithMinifyJs'             => [
            'option'    =>  [
                'minify_js' => 1,
                'minify_css' => 0,
            ],
		],
        'shouldNotCleanMinifyAll'             => [
            'option'    =>  [
                'minify_js' => 0,
                'minify_css' => 0,
            ],
		],
	],
];
