<?php
return [
    'shouldReturnHomeUrlWhenLangEmpty' => [
		'home_url' => 'http://example.org',
		'lang' => '',
		'expected' => 'http://example.org',
    ],
    'shouldReturnUpdated' => [
        'home_url' => 'http://example.org',
		'lang' => 'fr',
		'expected' => 'http://example.org/fr',
    ],
];
