<?php
return [
	'testShouldConvertToOne' => [
		'config' => [
			'jetpack_xml_sitemap' => 'test'
		],
		'expected' => [
			'jetpack_xml_sitemap' => 1
		]
	],
	'testShouldConvertToZero' => [
		'config' => [
			'jetpack_xml_sitemap' => ''
		],
		'expected' => [
			'jetpack_xml_sitemap' => 0
		]
	]
];
