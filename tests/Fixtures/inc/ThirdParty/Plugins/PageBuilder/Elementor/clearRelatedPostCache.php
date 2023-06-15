<?php

return [
	'shouldBailOutIfNoRelatedPost' => [
		'config' => [
            'template_id' => 1,
            'results' => [],
        ],
	],
    'shouldNotClearCacheOfRelatedPostIfPrivate' => [
		'config' => [
            'template_id' => 1,
            'post_status' => 'private',
            'results' => [
                (object) [
                    'post_id' => 2,
                ],
                (object) [
                    'post_id' => 3,
                ],
            ],
        ],
	],
    'shouldNotClearCacheOfRelatedPostIfPrivate' => [
		'config' => [
            'template_id' => 1,
            'post_status' => 'publish',
            'results' => [
                (object) [
                    'post_id' => 2,
                ],
                (object) [
                    'post_id' => 3,	
                ],
            ],
        ],
	],
];
