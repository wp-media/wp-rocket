<?php

return [
	'shouldDoNothingWhenFolderWritableAndNoTransient' => [
		'config' => [
			'can_manage'           => true,
			'is_enabled'           => true,
			'is_writable_folder'   => true,
			'transient_value'      => false,
		],
		'expected' => [
			'notice_called' => false,
		],
	],
	'shouldShowTopLevelNoticeWhenFolderNotWritable' => [
		'config' => [
			'can_manage'           => true,
			'is_enabled'           => true,
			'is_writable_folder'   => false,
			'transient_value'      => false,
		],
		'expected' => [
			'notice_called' => true,
		],
	],
	'shouldShowSubfolderNoticeWhenTransientSet' => [
		'config' => [
			'can_manage'           => true,
			'is_enabled'           => true,
			'is_writable_folder'   => true,
			'transient_value'      => 'http://example.com',
		],
		'expected' => [
			'notice_called' => true,
		],
	],
	'shouldDoNothingWhenRucssDisabledEvenIfTransientSet' => [
		'config' => [
			'can_manage'           => true,
			'is_enabled'           => false,
			'is_writable_folder'   => true,
			'transient_value'      => 'http://example.com',
		],
		'expected' => [
			'notice_called' => false,
		],
	],
	'shouldDoNothingWhenUserCannotManageOptions' => [
		'config' => [
			'can_manage'           => false,
			'is_enabled'           => true,
			'is_writable_folder'   => true,
			'transient_value'      => 'http://example.com',
		],
		'expected' => [
			'notice_called' => false,
		],
	],
];
