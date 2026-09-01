<?php

return [
	'shouldReturnFalseWhenWordFenceNotPresent' => [
		'config'   => [ 'define_wordfence_version' => false ],
		'expected' => false,
	],
	'shouldReturnTrueWhenWordFencePresent'     => [
		'config'   => [ 'define_wordfence_version' => true ],
		'expected' => true,
	],
];
