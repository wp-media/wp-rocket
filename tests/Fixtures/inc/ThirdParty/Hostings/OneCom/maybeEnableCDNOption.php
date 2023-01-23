<?php

return [
    'testShouldReturnTrueWithOneDotComCDNEnabled' => [
        'config' => [
            'cdn' => null,
            'oc_cdn_enabled' => true,
        ],
        'expected' => [
            'return' => true,
        ],
    ],
    'testShouldReturnNullWithOneDotComCDNDisabled' => [
        'config' => [
            'cdn' => null,
            'oc_cdn_enabled' => false,
        ],
        'expected' => [
            'return' => null,
        ],
    ],
    'testShouldReturnFalseWithOneDotComCDNEnabledAndWPContentDirChanged' => [
	    'config' => [
		    'cdn' => null,
		    'oc_cdn_enabled' => true,
		    'wp_content_dir' => 'vfs://public/wp-content-changed',
	    ],
	    'expected' => [
		    'return' => false,
	    ],
    ],
    'testShouldReturntrueWithOneDotComCDNEnabledAndWPContentDirNotChanged' => [
	    'config' => [
		    'cdn' => null,
		    'oc_cdn_enabled' => true,
		    'wp_content_dir' => 'vfs://public/wp-content',
	    ],
	    'expected' => [
		    'return' => true,
	    ],
    ],
];
