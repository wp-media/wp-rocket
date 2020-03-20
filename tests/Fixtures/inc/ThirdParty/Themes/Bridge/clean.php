<?php
return [
    // Custom CSS options enabled.
    [
        [
			'custom_css'     => 0,
			'custom_svg_css' => 0,
			'custom_js'      => 0,
        ],
        [
			'custom_css'     => 1,
			'custom_svg_css' => 0,
			'custom_js'      => 0,
        ],
        [
			[
				'minify_css',
				0,
				1,
			],
			[
				'minify_js',
				0,
				0,
			],
		],
    ],
    // Custom SVG CSS options enabled.
    [
        [
			'custom_css'     => 0,
			'custom_svg_css' => 0,
			'custom_js'      => 0,
        ],
        [
			'custom_css'     => 0,
			'custom_svg_css' => 1,
			'custom_js'      => 0,
        ],
        [
			[
				'minify_css',
				0,
				1,
			],
			[
				'minify_js',
				0,
				0,
			],
		],
    ],
    // All CSS options enabled.
    [
        [
			'custom_css'     => 0,
			'custom_svg_css' => 0,
			'custom_js'      => 0,
        ],
        [
			'custom_css'     => 1,
			'custom_svg_css' => 1,
			'custom_js'      => 0,
        ],
        [
			[
				'minify_css',
				0,
				1,
			],
			[
				'minify_js',
				0,
				0,
			],
		],
    ],
    // All JS options enabled.
    [
        [
			'custom_css'     => 0,
			'custom_svg_css' => 0,
			'custom_js'      => 0,
        ],
        [
			'custom_css'     => 0,
			'custom_svg_css' => 0,
			'custom_js'      => 1,
        ],
        [
			[
				'minify_css',
				0,
				1,
			],
			[
				'minify_js',
				0,
				1,
			],
		],
    ],
    // All options enabled.
    [
        [
			'custom_css'     => 0,
			'custom_svg_css' => 0,
			'custom_js'      => 0,
        ],
        [
			'custom_css'     => 1,
			'custom_svg_css' => 1,
			'custom_js'      => 1,
        ],
        [
			[
				'minify_css',
				0,
				1,
			],
			[
				'minify_js',
				0,
				1,
			],
		],
    ],
];