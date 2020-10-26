<?php

return [
	'vfs_dir' => 'public/',

	'test_data' => [
		'testLinkHasNoIntegrityAttribute' => [
			'config' => [
				'asset' => [
					0 => '<link rel="stylesheet" href="http://external-domain.org/path/to/style.css" type="text/css" media="all">',
					'url' => 'http://external-domain.org/path/to/style.css'
				],
			],
			'expected' => '<link rel="stylesheet" href="http://external-domain.org/path/to/style.css" type="text/css" media="all">'
		],

		'testLinkWithNotValidIntegrityAttributeFormat' => [
			'config' => [
				'asset' => [
					0 => '<link rel="stylesheet" href="http://external-domain.org/path/to/style.css" integrity="test" type="text/css" media="all">',
					'url' => 'http://external-domain.org/path/to/style.css'
				],
			],
			'expected' => '<link rel="stylesheet" href="http://external-domain.org/path/to/style.css" integrity="test" type="text/css" media="all">'
		],

		'testLinkWithNotValidIntegrityAttribute' => [
			'config' => [
				'asset' => [
					0 => '<link rel="stylesheet" href="http://external-domain.org/path/to/style.css" integrity="sha384-notvalid" type="text/css" media="all">',
					'url' => 'http://external-domain.org/path/to/style.css'
				],
				'file_contents' => 'external css content',
			],
			'expected' => false
		],

		'testLinkWithNotValidIntegrityAttributeHashAlgorithm' => [
			'config' => [
				'asset' => [
					0 => '<link rel="stylesheet" href="http://external-domain.org/path/to/style.css" integrity="algorithmNotValid-notvalid" type="text/css" media="all">',
					'url' => 'http://external-domain.org/path/to/style.css'
				],
			],
			'expected' => false
		],

		'testLinkWithValidIntegrityAttribute' => [
			'config' => [
				'asset' => [
					0 => '<link rel="stylesheet" href="http://external-domain.org/path/to/style.css" integrity="sha384-Iwk3Na27oumffZOWcRt56FelXSzZqulFKATFo2oGfWyNRov+XJlD798hbG25kbVd" type="text/css" media="all">',
					'url' => 'http://external-domain.org/path/to/style.css'
				],
				'file_contents' => 'external css content',
			],
			'expected' => '<link rel="stylesheet" href="http://external-domain.org/path/to/style.css" type="text/css" media="all">'
		],

	],
];
