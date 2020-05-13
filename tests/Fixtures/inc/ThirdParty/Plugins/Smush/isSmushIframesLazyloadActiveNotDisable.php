<?php

return [
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
	// No iframe.
	[
		true,
		[
			'jpeg'   => true,
			'png'    => true,
			'gif'    => true,
			'svg'    => true,
			'foo'    => true,
			'iframe' => false,
		],
	],
	// Empty formats.
	[
		true,
		[],
	],
];
