<?php
$rule = '<IfModule mod_headers.c>
Header append Cache-Control " s-maxage=3600, stale-while-revalidate=21600" "expr=%{CONTENT_TYPE} =~ m#text/html#"
</IfModule>
';

return [
	'testShouldChangeUpdateHtaccessRules' => [
		'expected' => $rule,
	],
];
