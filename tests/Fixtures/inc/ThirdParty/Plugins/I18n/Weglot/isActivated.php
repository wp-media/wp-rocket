<?php

return [
	'shouldReturnFalseWhenWeglotNotPresent' => [
		'config'   => [ 'define_context_weglot' => false ],
		'expected' => false,
	],
	'shouldReturnTrueWhenWeglotPresent'     => [
		'config'   => [ 'define_context_weglot' => true ],
		'expected' => true,
	],
];
