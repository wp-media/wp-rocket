<?php

return [
	'shouldReturnTrueIfPostTypeIsElementor' => [
		'config' => [
            'post_type' => 'elementor_library',
            'allow_exclusion' => false,
        ],
        'expected' => true,
	],
	'shouldReturnFalseIfPostTypeIsNotElementor' => [
		'config' => [
            'post_type' => 'post',
            'allow_exclusion' => false,
        ],
        'expected' => false,
	],
];
