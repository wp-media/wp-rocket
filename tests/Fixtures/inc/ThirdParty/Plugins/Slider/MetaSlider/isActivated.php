<?php

return [
	'shouldReturnFalseWhenClassMissing' => [
		'config'   => [ 'define_metaslider' => false ],
		'expected' => false,
	],
	'shouldReturnTrueWhenClassPresent'  => [
		'config'   => [ 'define_metaslider' => true ],
		'expected' => true,
	],
];
