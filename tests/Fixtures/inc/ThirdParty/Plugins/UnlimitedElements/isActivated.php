<?php

return [
	'shouldReturnFalseWhenUnlimitedElementsNotPresent' => [
		'config'   => [ 'unlimited_elements_inc' => null ],
		'expected' => false,
	],
	'shouldReturnTrueWhenUnlimitedElementsPresent'     => [
		'config'   => [ 'unlimited_elements_inc' => '/path/to/unlimited-elements.php' ],
		'expected' => true,
	],
];
