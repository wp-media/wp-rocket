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

$oldValue['cache_reject_uri'] = [
    '/hello-world',
    '/testing/(.*)',
];

// Update settings.
$newValue = $oldValue;

$newValue['cache_reject_uri'] = array_map( function( $pattern ){
    return $pattern . '/';
}, $newValue['cache_reject_uri'] );
$newValueWithTrailingSlahsInCacheRejectUriPatterns = $newValue;

$newValue = $oldValue;

$oldValue['cache_reject_uri'][1] = '/';
$newValueWithTrailingSlashInCacheRejectUriPatternsWithPermalinksHavingNoTrailingSlash = $oldValue;

$oldValue['cache_reject_uri'] = [
    '/hello-world',
    '/index.php(.*)',
];
$newValueWithIndexInCacheRejectUriPatternsWithPermalinks = $oldValue;

$oldValue['cache_reject_uri'] = [
    '/hello-world/',
    '/index.php(.*)',
];
$expectedValueWithIndexInCacheRejectUriPatternsWithPermalinksHavingTrailingSlash = $oldValue;

// Restore settings.
$oldValue['cache_reject_uri'] = [];

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
        'expected' => $newValueWithTrailingSlahsInCacheRejectUriPatterns,
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