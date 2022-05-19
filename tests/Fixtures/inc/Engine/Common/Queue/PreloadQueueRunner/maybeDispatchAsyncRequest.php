<?php
return [

	'notAdminShouldDoNothing' => [
		'config' => [
			'is_admin' => false,
			'is_locked' => true,
		]
	],
	'lockedShouldDoNothing' => [
		'config' => [
			'is_admin' => true,
			'is_locked' => true,
		]

	],
	'unlockAndAdminShouldDispatch' => [
		'config' => [
			'is_admin' => true,
			'is_locked' => false,
		]

	]
];
