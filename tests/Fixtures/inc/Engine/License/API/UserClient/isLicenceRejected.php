<?php

return [
	'testShouldReturnTrueWhenFlagged' => [
		'transient' => 1,
		'expected'  => true,
	],
	'testShouldReturnFalseWhenNotFlagged' => [
		'transient' => false,
		'expected'  => false,
	],
];
