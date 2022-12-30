<?php
return [
	'testShouldAddRegex' => [
		'config' => 'background-image\s*:\s*(?<attr>\s*url\s*\((?<url>[^)]+)\))\s*;?',
		'expected' => '(--awb-)?background-image\s*:\s*(?<attr>\s*url\s*\((?<url>[^)]+)\))\s*;?'
	]
];
