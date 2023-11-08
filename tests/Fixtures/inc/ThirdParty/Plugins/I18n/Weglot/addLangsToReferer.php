<?php

return [
    'shouldReturnUpdatedLangsReferer' => [
        'noChangeHomepage' => [
            'referer' => '/',
            'lang' => '',
            'expected' => '/'
        ],
        'noChangePage' => [
            'referer' => '/path/to/page/',
            'lang' => '',
            'expected' => '/path/to/page/'
        ],
        'ChangeLanguagePage' => [
            'referer' => '/path/to/page/',
            'lang' => 'es',
            'expected' => '/es/path/to/page/'
        ],
        'ChangeLanguageWithQueryParams' => [
            'referer' => '/path/to/page?param1=value1&param2=value2',
            'lang' => 'fr',
            'expected' => '/fr/path/to/page?param1=value1&param2=value2'
        ],
    ],
];