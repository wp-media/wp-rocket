<?php

return [
	'chat_not_registered_and_allowed' => [
		'config'   => [ 'chat_registered' => false, 'can_manage' => true ],
		'expected' => [ 'printed' => true ],
	],
	'chat_registered'                 => [
		'config'   => [ 'chat_registered' => true, 'can_manage' => true ],
		'expected' => [ 'printed' => false ],
	],
	'user_cannot_manage_options'      => [
		'config'   => [ 'chat_registered' => false, 'can_manage' => false ],
		'expected' => [ 'printed' => false ],
	],
];
