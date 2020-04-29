<?php

return [
	'vfs_dir' => 'wp-content/',

	'test_data' => [
		[
			'type'     => 'invalid',
			'constant' => false,
			'expected' => 0755,
		],
		[
			'type'     => 'dir',
			'constant' => false,
			'expected' => 0777,
		],
		[
			'type'     => 'dirs',
			'constant' => false,
			'expected' => 0777,
		],
		[
			'type'     => 'folder',
			'constant' => false,
			'expected' => 0777,
		],
		[
			'type'     => 'folders',
			'constant' => false,
			'expected' => 0777,
		],
		[
			'type'     => 'dir',
			'constant' => 'FS_CHMOD_DIR',
			'expected' => 0777,
		],

		[
			'type'     => 'file',
			'constant' => false,
			'expected' => 0666,
		],
		[
			'type'     => 'files',
			'constant' => false,
			'expected' => 0666,
		],
		[
			'type'     => 'file',
			'constant' => 'FS_CHMOD_FILE',
			'expected' => 0666,
		],
	],
];
