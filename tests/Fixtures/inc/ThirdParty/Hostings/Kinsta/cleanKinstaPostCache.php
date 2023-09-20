<?php
return [
	'shouldPurgePost' => [
		'config' => [
			'post' => (object) [
				'ID' => 'id'
			],
		],
		'expected' => [
			'id' => 'id',
			'type' => 'post',
 		]
	]
];
