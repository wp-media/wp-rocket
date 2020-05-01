<?php

$file_entries = [
	'index.html'      => '',
	'index.html_gzip' => '',
];

$files   = [];
$files[] = $file_entries;
for ( $post_id = 1; $post_id < 10; $post_id ++ ) {
	$files["post{$post_id}"] = $file_entries;
}

return [
	'wp-content' => [
		'cache' => [

			'wp-rocket' => [
				'example.org'               => $files,
				'example.org-user1-123456'  => $files,
				'example.org-user2-123456'  => $files,
				'example.org-user3-123456'  => $files,
				'example.org-user4-123456'  => $files,
				'example.org-user5-123456'  => $files,
				'example.org-user7-123456'  => $files,
				'example.org-user8-123456'  => $files,
				'example.org-user9-123456'  => $files,
				'example.org-user10-123456' => $files,
				'example.org-user11-123456' => $files,
				'example.org-user12-123456' => $files,
				'example.org-user13-123456' => $files,
				'example.org-user14-123456' => $files,
				'example.org-user15-123456' => $files,
				'example.org-user17-123456' => $files,
				'example.org-user18-123456' => $files,
				'example.org-user19-123456' => $files,
				'example.org-user20-123456' => $files,
				'example.org-user21-123456' => $files,
				'example.org-user22-123456' => $files,
				'example.org-user23-123456' => $files,
				'example.org-user24-123456' => $files,
				'example.org-user25-123456' => $files,
				'example.org-user27-123456' => $files,
				'example.org-user28-123456' => $files,
				'example.org-user29-123456' => $files,
				'example.org-user30-123456' => $files,
			],
		],
	],
];
