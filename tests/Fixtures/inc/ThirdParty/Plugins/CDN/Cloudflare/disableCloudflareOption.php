<?php
return [
    'shouldDisable' => [
        'config' => [
			'settings' => [
				'do_cloudflare' => false,
			]
        ],
	    'expected' => [
		    'settings' => [
			    'do_cloudflare' => false,
		    ]
	    ]
    ],

];
