<?php

return [
    'shouldBailOutIfOldIpNotFound' =>[
        'old_value' => '',
        'values'   => '141.94.254.72',
		'expected' => ''
    ],
	'shouldPopOldIp' => [
        'old_value' => '135.125.83.227',
        'values'   => '135.125.83.227, 141.94.254.72',
		'expected' => '141.94.254.72'
	],
];
