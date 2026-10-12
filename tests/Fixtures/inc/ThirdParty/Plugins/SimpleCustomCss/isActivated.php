<?php

return [
	'shouldReturnFalseWhenSimpleCustomCssNotPresent' => [
		'config'   => [ 'sccss_file' => null ],
		'expected' => false,
	],
	'shouldReturnTrueWhenSimpleCustomCssPresent'     => [
		'config'   => [ 'sccss_file' => 'sccss.php' ],
		'expected' => true,
	],
];
