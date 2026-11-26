<?php

return [
	'test_data' => [
		'schemeLessInputOnHttpsHome'    => [ 'https', 'https://{host}/page', '{host}/page', true ],
		'httpInputOnHttpsHome'          => [ 'https', 'https://{host}/page', 'http://{host}/page', true ],
		'httpsInputOnHttpsHome'         => [ 'https', 'https://{host}/page', 'https://{host}/page', true ],
		'httpInputOnHttpHome'           => [ 'http', 'http://{host}/page', 'http://{host}/page', true ],
		'otherHostInputIsLeftUntouched' => [ 'https', 'https://{host}/page', 'http://other.test/page', false ],
	],
];
