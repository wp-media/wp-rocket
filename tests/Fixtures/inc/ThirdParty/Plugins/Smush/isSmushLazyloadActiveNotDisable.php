<?php

return [
	/**
	 * Should not disable WPRL.
	 */
	// Disabled.
	[
		false,
		[
			'jpeg'   => true,
			'png'    => true,
			'gif'    => true,
			'svg'    => true,
			'iframe' => true,
		],
	],
	// No image formats.
	[
		true,
		[
			'jpeg'   => false,
			'png'    => false,
			'gif'    => false,
			'svg'    => false,
			'foo'    => true,
			'iframe' => true,
		],
	],
	// Empty formats.
	[
		true,
		[],
	],
];
