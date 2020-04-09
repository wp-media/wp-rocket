<?php

return [
	'vfs_dir'   => 'public/',

	// Virtual filesystem structure.
	'structure' => [
		'.htaccess' => '',
		'wp-content' => [],
	],
	'test_data' => [
		[
			'<IfModule mod_headers.c>
				# Serve gzip compressed CSS and JS files if they exist
				# and the client accepts gzip.
				RewriteCond "%{HTTP:Accept-encoding}" "gzip"
				RewriteCond "%{REQUEST_FILENAME}\.gz" -s
				RewriteRule "^(.*)\.(css|js)"         "$1\.$2\.gz" [QSA]
				# Serve correct content types, and prevent mod_deflate double gzip.
				RewriteRule "\.css\.gz$" "-" [T=text/css,E=no-gzip:1]
				RewriteRule "\.js\.gz$"  "-" [T=text/javascript,E=no-gzip:1]
				<FilesMatch "(\.js\.gz|\.css\.gz)$">
					# Serve correct encoding type.
					Header append Content-Encoding gzip
					# Force proxies to cache gzipped &
					# non-gzipped css/js files separately.
					Header append Vary Accept-Encoding
				</FilesMatch>
			</IfModule>'
		],
	],
];
