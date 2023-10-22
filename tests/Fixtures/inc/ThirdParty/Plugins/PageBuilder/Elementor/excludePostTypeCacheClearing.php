<?php

return [
	'shouldReturnTrueIfPostTypeIsElementor' => [
		'config' => [
            'post' => (object) [
                'post_type' => 'elementor_library',
            ],
            'elementor_library',
            'allow_exclusion' => null,
        ],
        'expected' => true,
	],
	'shouldReturnFalseIfPostTypeIsNotElementor' => [
		'config' => [
            'post' => (object) [
                'post_type' => 'post',
            ],
            'allow_exclusion' => null,
        ],
        'expected' => null,
	],
];
