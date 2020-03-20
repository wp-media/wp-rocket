<?php
return [
    // No options enabled.
    [
        [
			'custom_css'     => 0,
			'custom_svg_css' => 0,
			'custom_js'      => 0,
        ],
        [
			'custom_css'     => 0,
			'custom_svg_css' => 0,
			'custom_js'      => 0,
        ],
        [
			[
				'minify_css',
				0,
				0,
			],
			[
				'minify_js',
				0,
				0,
			],
		],
    ],
    // Minify CSS option enabled, custom CSS & SVG disabled.
    [
        [
			'custom_css'     => 0,
			'custom_svg_css' => 0,
			'custom_js'      => 0,
        ],
        [
			'custom_css'     => 0,
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
    // Minify JS option enabled, custom JS disabled.
    [
        [
			'custom_css'     => 0,
			'custom_svg_css' => 0,
			'custom_js'      => 0,
        ],
        [
			'custom_css'     => 0,
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
				1,
			],
		],
    ],
];