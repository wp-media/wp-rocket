<?php
return [
    'ExcludeShouldExcludePages' => [
        'config' => [
			'use_trailing_slashes' => false,
            'urls' => [],
			'checkout_id' => 10,
			'cart_id' => 18,
			'myaccount_id' => 21,
			'i18n_urls' => [
				'url',
			],
        ],
        'expected' => [
			'urls' => [
				'url',
				'url',
				'url',
			],
			'checkout_id' => 10,
			'cart_id' => 18,
			'myaccount_id' => 21,
			'type' => 'page',
			'pattern' => '(.*)',
        ]
    ],

];
