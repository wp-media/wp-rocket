<?php

return [
	'testShouldReturnTrueWhenReseller' => [
		'is_reseller'  => true,
		'is_live_site' => true,
		'expected'     => true,
	],
	'testShouldReturnTrueWhenNotLiveSite' => [
		'is_reseller'  => false,
		'is_live_site' => false,
		'expected'     => true,
	],
	'testShouldReturnFalseWhenNotResellerAndLiveSite' => [
		'is_reseller'  => false,
		'is_live_site' => true,
		'expected'     => false,
	],
];