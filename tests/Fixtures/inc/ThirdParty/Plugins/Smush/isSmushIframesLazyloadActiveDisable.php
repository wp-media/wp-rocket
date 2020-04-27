<?php

return [
	// Iframe enabled.
	[
		true,
		[
			'jpeg'   => false,
			'png'    => false,
			'gif'    => false,
			'svg'    => false,
			'foo'    => false,
			'iframe' => true,
		],
	],
	// Iframe alone.
	[
		true,
		[
			'iframe' => true,
		],
	],
];
