<?php

return [
	'shouldReturnFalseWhenOptimusFileNotDefined' => [
		'config'   => [ 'define_optimus_file' => false ],
		'expected' => false,
	],
	'shouldReturnTrueWhenOptimusFilePresent'     => [
		'config'   => [ 'define_optimus_file' => true ],
		'expected' => true,
	],
];
