<?php
return [
    'LowerVersionShouldClean' => [
        'config' => [
              'old_version' => '3.12.1',
        ],
		'expected' => true,
	],
    'HigherVersionShouldDoNothing' => [
        'config' => [
              'old_version' => '3.15',
        ],
		'expected' => false,
    ],

];
