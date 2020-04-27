<?php

return [
	/**
	 * Should disable WPRL.
	 */
	// Smush enabled for image.
	[
		true,
		[
			'jpeg'   => true,
			'iframe' => false,
		],
		[ true, true, true, false ],
	],
	// Smush enabled for iframe.
	[
		true,
		[
			'jpeg'   => false,
			'iframe' => true,
		],
		[ true, true, false, true ],
	],
	// Smush enabled for image and iframe.
	[
		true,
		[
			'jpeg'   => true,
			'iframe' => true,
		],
		[ true, true, true, true ],
	],
];
