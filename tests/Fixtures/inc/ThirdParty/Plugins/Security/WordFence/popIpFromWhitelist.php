<?php

return [
    'shouldBailOutIfIpNotFound' =>[
        'whitelisted'   => '135.125.83.227,127.0.0.1',
		'expected' => '135.125.83.227,127.0.0.1',
    ],
	'shouldpopIpFromWhitelist' => [
        'whitelisted'   => '127.0.0.1,141.94.254.72',
		'expected' => '127.0.0.1',
	],
];
