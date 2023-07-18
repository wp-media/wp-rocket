<?php

return [
	'vfs_dir'   => 'wp-content/',
	'test_data' => [
		'shouldReturnExclusions' => [
			'config'   => [
				'exclusions' => [],
			],
			'expected' => [
				'/wp-includes/js/dist/i18n.min.js',
				'/interactive-3d-flipbook-powered-physics-engine/assets/js/html2canvas.min.js',
                '/interactive-3d-flipbook-powered-physics-engine/assets/js/pdf.min.js',
                '/interactive-3d-flipbook-powered-physics-engine/assets/js/three.min.js',
                '/interactive-3d-flipbook-powered-physics-engine/assets/js/3d-flip-book.min.js',
                '/google-site-kit/dist/assets/js/(.*).js',
                '/wp-live-chat-support/public/js/callus(.*).js',
				'/wp-content/themes/uncode/library/js/init.js',
				'/wp-content/themes/uncode/library/js/min/init.min.js',
				'/wp-content/themes/uncode/library/js/init.min.js',
				'/wp-content/themes/uncode/library/js/ai-uncode.js',
				'/wp-content/themes/uncode/library/js/min/ai-uncode.min.js',
				'/wp-content/themes/uncode/library/js/ai-uncode.min.js',
			],
		],
	],
];
