<?php

return [
	'shouldDisableCdnWhenAddonTurnedOn'      => [
		'config'   => [
			'old_value' => [ 'enable-addon' => 0 ],
			'new_value' => [ 'enable-addon' => 1 ],
		],
		'expected' => [ 'cdn_disabled' => true ],
	],
	'shouldDoNothingWhenAddonTurnedOff'      => [
		'config'   => [
			'old_value' => [ 'enable-addon' => 1 ],
			'new_value' => [ 'enable-addon' => 0 ],
		],
		'expected' => [ 'cdn_disabled' => false ],
	],
	'shouldDoNothingWhenAddonValueUnchanged' => [
		'config'   => [
			'old_value' => [ 'enable-addon' => 1 ],
			'new_value' => [ 'enable-addon' => 1 ],
		],
		'expected' => [ 'cdn_disabled' => false ],
	],
];
