<?php
return [
	'shouldSwitchOnOnApache'                    => [
		'config'   => [
			'mode'         => 'apache',
			'writes_files' => true,
			'daemon'       => false,
		],
		'expected' => true,
	],
	'shouldNotSwitchOnWithoutTheModule'         => [
		'config'   => [
			'mode'         => 'none',
			'writes_files' => true,
			'daemon'       => true,
		],
		'expected' => false,
	],
	'shouldNotSwitchOnOnARefusedConfiguration'  => [
		'config'   => [
			'mode'         => 'apache',
			'writes_files' => false,
			'daemon'       => true,
		],
		'expected' => false,
	],
	'shouldSwitchOnOnNginxWhenTheDaemonAnswers' => [
		'config'   => [
			'mode'         => 'nginx',
			'writes_files' => true,
			'daemon'       => true,
		],
		'expected' => true,
	],
	'shouldNotSwitchOnOnNginxWithoutTheDaemon'  => [
		'config'   => [
			'mode'         => 'nginx',
			'writes_files' => true,
			'daemon'       => false,
		],
		'expected' => false,
	],
];
