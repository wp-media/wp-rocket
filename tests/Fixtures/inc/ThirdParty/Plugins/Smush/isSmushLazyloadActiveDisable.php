<?php

return [
	/**
	 * Should disable WPRL.
	 */
	// One image format.
	[
		true,
		[
			'jpeg'   => true,
			'png'    => false,
			'gif'    => false,
			'svg'    => false,
			'foo'    => false,
			'iframe' => false,
		],
	],
	// Two image formats.
	[
		true,
		[
			'jpeg' => false,
			'png'  => true,
			'gif'  => true,
			'svg'  => false,
		],
	],
	// Three image formats.
	[
		true,
		[
			'jpeg' => true,
			'png'  => true,
			'gif'  => true,
			'svg'  => false,
		],
	],
	// All image formats.
	[
		true,
		[
			'jpeg' => true,
			'png'  => true,
			'gif'  => true,
			'svg'  => true,
		],
	],
	// One image format alone.
	[
		true,
		[
			'png' => true,
		],
	],
];
