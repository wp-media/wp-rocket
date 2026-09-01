<?php

return [
	'shouldReturnFalseWhenUnlimitedElementsNotPresent' => [
		'config'   => [ 'define_unlimited_elements_inc' => false ],
		'expected' => false,
	],
	'shouldReturnTrueWhenUnlimitedElementsPresent'     => [
		'config'   => [ 'define_unlimited_elements_inc' => true ],
		'expected' => true,
	],
];
