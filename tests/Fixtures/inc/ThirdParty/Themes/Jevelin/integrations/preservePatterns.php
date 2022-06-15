<?php

return [
	'test_data' => [

		'testShouldPreservePattern' => [
				'config' => [
					'stylesheet' => 'jevelin',
					'theme-name' => 'Jevelin',
					'is-child'   => '',
					'set-lazy'   => 0,
					'patterns' => [],
				],
				'expected' => [
					'#heading-',
				]
			],
		]
 ];
