<?php

return [
	/**
	 * Should not disable WPRL.
	 */
	// Smush not enabled, WPR enabled.
	[
		false,
		[],
		[ true, true, false, false ],
	],
	// Smush enabled, WPR not enabled.
	[
		true,
		[
			'jpeg'   => true,
			'iframe' => true,
		],
		[ false, false, false, false ],
	],
];
