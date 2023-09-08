<?php

$post = new WP_Post();
$post->post_type = 'elementor_library';

$wrong_post = new WP_Post();
$post->post_type = 'wrong';

return [
    'ShouldSkipAdminBar' => [
        'config' => [
              'should_skip' => false,
              'post' => $post,

        ],
        'expected' => true
    ],
	'NoPostShouldReturnSame' => [
		'config' => [
			'should_skip' => false,
			'post' => null,

		],
		'expected' => false
	],
	'WrongPostTypeShouldReturnSame' => [
		'config' => [
			'should_skip' => false,
			'post' => $wrong_post,

		],
		'expected' => false
	],
];
