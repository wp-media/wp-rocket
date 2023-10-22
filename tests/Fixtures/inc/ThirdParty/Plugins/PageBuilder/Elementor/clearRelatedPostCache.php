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
                    'url' => 'http://example.org/hello-world-2',
                ],
                (object) [
                    'post_id' => 3,	
                    'url' => 'http://example.org/hello-world-3',
                ],
            ],
        ],
	],
    'shouldClearCacheOfRelatedPostIfPublishButNotUsedCssWhenRucssIsDisabled' => [
		'config' => [
            'template_id' => 1,
            'post_status' => 'publish',
            'remove_unused_css' => 0,
            'results' => [
                (object) [
                    'post_id' => 2,
                    'url' => 'http://example.org/hello-world-2',
                ],
                (object) [
                    'post_id' => 3,	
                    'url' => 'http://example.org/hello-world-3',
                ],
            ],
        ],
	],
    'shouldClearCacheOfRelatedPostIfPublishAndUsedCssWhenRucssIsEnabled' => [
		'config' => [
            'template_id' => 1,
            'post_status' => 'publish',
            'remove_unused_css' => 1,
            'results' => [
                (object) [
                    'post_id' => 2,
                    'url' => 'http://example.org/hello-world-2',
                ],
                (object) [
                    'post_id' => 3,	
                    'url' => 'http://example.org/hello-world-3',
                ],
            ],
        ],
	],
];
