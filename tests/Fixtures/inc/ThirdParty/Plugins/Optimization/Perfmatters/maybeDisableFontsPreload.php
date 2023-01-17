<?php
return [
    'shouldDisableFontsPreloadWhenPerfmattersLocalGoogleFontsisEnabled' => [
        'config' => [
            'perfmatters_options' => [
                'fonts' => [
                    'local_google_fonts' => 1,
                ],
            ],
        ],
        'expected' => false,
    ],
    'shouldNotDisableFontsPreloadWhenPerfmattersLocalGoogleFontsisDisabled' => [
        'config' => [
            'perfmatters_options' => [
                'fonts' => [
                    'local_google_fonts' => '',
                ],
            ],
        ],
        'expected' => true,
    ],
];