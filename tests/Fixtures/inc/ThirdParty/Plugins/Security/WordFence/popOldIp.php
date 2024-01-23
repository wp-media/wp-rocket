<?php

return [
    'shouldBailOutIfOldIpNotFound' =>[
        'old_value' => '127.0.0.1',
        'values'   => '141.94.254.72',
		'expected' => '141.94.254.72'
    ],
	'shouldPopOldIp' => [
        'old_value' => '135.125.83.227',
        'values'   => '135.125.83.227,141.94.254.72',
		'expected' => '141.94.254.72'
	],
];
