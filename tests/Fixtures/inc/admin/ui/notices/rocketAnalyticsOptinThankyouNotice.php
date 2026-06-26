<?php

return [
	'test_data' => [
		'shouldRenderNoticeWithoutDataTableWhenTransientExistsAndConditionsMet' => [
			[
				'screen_id'       => 'settings_page_wprocket',
				'user_can'        => true,
				'transient_value' => 1,
			],
			[
				'notice_rendered'   => true,
				'transient_checked' => true,
				'transient_deleted' => true,
			],
		],
		'shouldRenderNothingWhenTransientIsAbsent'                             => [
			[
				'screen_id'       => 'settings_page_wprocket',
				'user_can'        => true,
				'transient_value' => false,
			],
			[
				'notice_rendered'   => false,
				'transient_checked' => true,
				'transient_deleted' => false,
			],
		],
		'shouldRenderNothingWhenScreenIsWrong'                                 => [
			[
				'screen_id' => 'dashboard',
				'user_can'  => true,
			],
			[
				'notice_rendered'   => false,
				'transient_checked' => false,
				'transient_deleted' => false,
			],
		],
	],
];
