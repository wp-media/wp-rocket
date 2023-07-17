<?php
return [
    'ChangeToDisableShouldRemove' => [
        'config' => [
              'old_value' => [
				  'remove_unused_css' => true,
			  ],
              'value' => [
				  'remove_unused_css' => false,
			  ],

        ],
		'expected' => false,
    ],
    'AlreadyDisabledShouldKeep' => [
        'config' => [
              'old_value' => [
				  'remove_unused_css' => false,
			  ],
              'value' => [
				  'remove_unused_css' => false,
			  ],
        ],
		'expected' => true,
	],
    'EnabledShouldKeep' => [
        'config' => [
              'old_value' => [
				  'remove_unused_css' => false,
			  ],
              'value' => [
				  'remove_unused_css' => true,
			  ],

        ],
		'expected' => true,
	],
    'NoValueShouldKeep' => [
        'config' => [
              'old_value' => [

			  ],
              'value' => [

			  ],

        ],
		'expected' => true,
	],

];
