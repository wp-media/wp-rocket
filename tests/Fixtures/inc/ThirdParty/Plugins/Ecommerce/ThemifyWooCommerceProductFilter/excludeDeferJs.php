<?php

return [
    'test_data' => [
        'testShouldNotAddTestToExclusionList' => [
            'is_plugin_active' => 0,
            'tests' => [
                'simplybook.(.*)/v2/widget/widget.js',
                '/wp-includes/js/dist/i18n.min.js',
                '/wp-content/plugins/wpfront-notification-bar/js/wpfront-notification-bar(.*).js',
                '/wp-content/plugins/oxygen/component-framework/vendor/aos/aos.js',
                '/wp-content/plugins/ewww-image-optimizer/includes/check-webp(.min)?.js',
            ],
            'expected' => [],
        ],
        'testShouldAddTestToExclusionList' => [
            'is_plugin_active' => 1,
            'tests' => [
                'simplybook.(.*)/v2/widget/widget.js',
                '/wp-includes/js/dist/i18n.min.js',
                '/wp-content/plugins/wpfront-notification-bar/js/wpfront-notification-bar(.*).js',
                '/wp-content/plugins/oxygen/component-framework/vendor/aos/aos.js',
                '/wp-content/plugins/ewww-image-optimizer/includes/check-webp(.min)?.js',
            ],
            'expected' => [
                '/wp-includes/js/jquery/jquery.min.js',
            ]
        ],
    ]
];