<?php
return [
	'vfs_dir' => 'wp-content/',

	'test_data' => [

		'ShouldFlushWithTrailingSlash' => [
			'config' => [
				'old_permalink_structure' => '/%postname%',
				'new_permalink_structure' => '/%postname%/',
			],
			'expected' => '<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{REQUEST_URI} !wp-admin
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_METHOD} GET
RewriteCond %{REQUEST_URI} !(.*)/$
RewriteRule ^(.*)$ /$1/ [R=301,L]
</IfModule>'
		],

		'ShouldFlushWithNoTrailingSlash' => [
			'config' => [
				'old_permalink_structure' => '/%postname%/',
				'new_permalink_structure' => '/%postname%',
			],
			'expected' => '<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{REQUEST_URI} !wp-admin
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_METHOD} GET
RewriteCond %{REQUEST_URI} (.*)/$
RewriteRule ^(.*)/$ /$1 [R=301,L]
</IfModule>'
		],

	]
];
