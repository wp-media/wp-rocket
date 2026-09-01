<?php

return [
	'shouldReturnFalseWhenSimpleCustomCssNotPresent' => [
		'config'   => [ 'define_sccss_file' => false ],
		'expected' => false,
	],
	'shouldReturnTrueWhenSimpleCustomCssPresent'     => [
		'config'   => [ 'define_sccss_file' => true ],
		'expected' => true,
	],
];
