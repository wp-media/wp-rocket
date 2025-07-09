<?php
return [
    'shouldReturnDynamicUrlforSecondLanguage' => [
		'config' => [
			'current_url' => 'http://example.org/fr/hello-world',
			'language' => 'fr',
            'slug' => 'bonjour-le-monde'
		],
		'expected' => 'http://example.org/fr/bonjour-le-monde',
    ],
];
