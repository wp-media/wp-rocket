<?php
$oldValue = [
    'cache_mobile'        => true,
    'purge_cron_interval' => true,
    'purge_cron_unit'     => true,
    'minify_css'          => false,
    'exclude_css'         => '',
    'minify_js'           => false,
    'exclude_js'          => '',
    'analytics_enabled'   => '',
    'cdn'                 => false,
    'cdn_cnames'          => false,
    'cache_reject_uri'    => [],
];

$newValue = [
    'cache_mobile'        => true,
    'purge_cron_interval' => true,
    'purge_cron_unit'     => true,
    'minify_css'          => false,
    'exclude_css'         => '',
    'minify_js'           => false,
    'exclude_js'          => '',
    'analytics_enabled'   => '',
    'cdn'                 => false,
    'cdn_cnames'          => false,
    'cache_reject_uri'    => [
        '/hello-world',
        '/testing/(.*)',
    ],
];

$expectedNewValueWithTrailingSlahsInCacheRejectUriPatterns = [
    'cache_mobile'        => true,
    'purge_cron_interval' => true,
    'purge_cron_unit'     => true,
    'minify_css'          => false,
    'exclude_css'         => '',
    'minify_js'           => false,
    'exclude_js'          => '',
    'analytics_enabled'   => '',
    'cdn'                 => false,
    'cdn_cnames'          => false,
    'cache_reject_uri'    => [
        '/hello-world/',
        '/testing/(.*)/',
    ],
];

$newValueWithTrailingSlashInCacheRejectUriPatternsWithPermalinksHavingNoTrailingSlash = [
    'cache_mobile'        => true,
    'purge_cron_interval' => true,
    'purge_cron_unit'     => true,
    'minify_css'          => false,
    'exclude_css'         => '',
    'minify_js'           => false,
    'exclude_js'          => '',
    'analytics_enabled'   => '',
    'cdn'                 => false,
    'cdn_cnames'          => false,
    'cache_reject_uri'    => [
        '/hello-world',
        '/',
    ],
];

$newValueWithIndexInCacheRejectUriPatternsWithPermalinks = [
    'cache_mobile'        => true,
    'purge_cron_interval' => true,
    'purge_cron_unit'     => true,
    'minify_css'          => false,
    'exclude_css'         => '',
    'minify_js'           => false,
    'exclude_js'          => '',
    'analytics_enabled'   => '',
    'cdn'                 => false,
    'cdn_cnames'          => false,
    'cache_reject_uri'    => [
        '/hello-world',
        '/index.php(.*)',
    ],
];

$expectedValueWithIndexInCacheRejectUriPatternsWithPermalinksHavingTrailingSlash = [
    'cache_mobile'        => true,
    'purge_cron_interval' => true,
    'purge_cron_unit'     => true,
    'minify_css'          => false,
    'exclude_css'         => '',
    'minify_js'           => false,
    'exclude_js'          => '',
    'analytics_enabled'   => '',
    'cdn'                 => false,
    'cdn_cnames'          => false,
    'cache_reject_uri'    => [
        '/hello-world/',
        '/index.php(.*)',
    ],
];

return [
    'testShouldReturnEmptyArrayWhenCacheRejectUriValueIsEmpty' => [
        'config' => [
            'old_value' => $oldValue,
            'value' => $oldValue,
        ],
        'expected' => $oldValue,
    ],
    'testShouldMatchCacheRejectUriPatternsWithPermalinkStructureHavingTrailingSlash' => [
        'config' => [
            'old_value' => $oldValue,
            'value' => $newValue,
            'permalink' => [
                'trailing_slash' => true,
                'structure' => '/%postname%/',
            ],
        ],
        'expected' => $expectedNewValueWithTrailingSlahsInCacheRejectUriPatterns,
    ],
    'testShouldMatchCacheRejecturiPatternsWithPermalinkStructureHavingNoTrailingSlash' => [
        'config' => [
            'old_value' => $oldValue,
            'value' => $newValue,
            'permalink' => [
                'trailing_slash' => false,
                'structure' => '/%postname%',
            ],
        ],
        'expected' => $newValue,
    ],
    'testShouldNotRemoveTrailingSlashOnSingleLineInCacheRejectUriPatternsWithPermalinkHavingNoTrailingSlash' => [
        'config' => [
            'old_value' => $oldValue,
            'value' => $newValueWithTrailingSlashInCacheRejectUriPatternsWithPermalinksHavingNoTrailingSlash,
            'permalink' => [
                'trailing_slash' => false,
                'structure' => '/%postname%',
            ],
        ],
        'expected' => $newValueWithTrailingSlashInCacheRejectUriPatternsWithPermalinksHavingNoTrailingSlash,
    ],
    'testShouldNotAddTrailingSlashToPatternWithIndexInCacheRejectUriWithPermalinkHavingTrailingSlash' => [
        'config' => [
            'old_value' => $oldValue,
            'value' => $newValueWithIndexInCacheRejectUriPatternsWithPermalinks,
            'permalink' => [
                'trailing_slash' => true,
                'structure' => '/%postname%/',
            ],
        ],
        'expected' => $expectedValueWithIndexInCacheRejectUriPatternsWithPermalinksHavingTrailingSlash,
    ],
];