<?php

$notice_html = <<<HTML
<div class="notice notice-warning ">
<strong>
WP Rocket</strong>
: Mod PageSpeed is not compatible with this plugin and may cause unexpected results.<a target="_blank" href="https://docs.wp-rocket.me/article/1376-mod-pagespeed">
More Info</a>
<p>
<a class="rocket-dismiss" href="http://example.org/wp-admin/admin-post.php?action=rocket_ignore&amp;box=rocket_warning_cron&amp;_wpnonce=123456">
Dismiss this notice.</a>
</p>
</div>
HTML;


return [

	'shouldNotShowNoticeWhenNoCapability' => [
		'config' => [
			'capability' => false,
		],
		'expected' => [
			'show_notice' => 0,
			'html' => ''
		]
	],

	'shouldNotShowNoticeWhenOtherScreen' => [
		'config' => [
			'capability' => true,
			'current_screen' => 'welcome'
		],
		'expected' => [
			'show_notice' => 0,
			'html' => ''
		]
	],

	'shouldNotShowNoticeWhenFoundTransientWithZeroValue' => [
		'config' => [
			'capability' => true,
			'current_screen' => 'settings_page_wprocket',
			'rocket_mod_pagespeed_enabled' => 0
		],
		'expected' => [
			'show_notice' => 0,
			'html' => ''
		]
	],

	'shouldShowNoticeWhenFoundTransientWithOneValueAndNotDismissed' => [
		'config' => [
			'capability' => true,
			'current_screen' => 'settings_page_wprocket',
			'rocket_mod_pagespeed_enabled' => 1,
			'boxes' => [],
		],
		'expected' => [
			'show_notice' => 1,
			'html' => $notice_html
		]
	],

	'shouldNotShowNoticeWhenFoundTransientWithOneValueAndDismissed' => [
		'config' => [
			'capability' => true,
			'current_screen' => 'settings_page_wprocket',
			'rocket_mod_pagespeed_enabled' => 1,
			'boxes' => [ 'rocket_error_mod_pagespeed' ],
		],
		'expected' => [
			'show_notice' => 0,
			'html' => ''
		]
	],

	'shouldShowNoticeWhenNotFoundTransientWithApacheModuleEnabled' => [
		'config' => [
			'capability' => true,
			'current_screen' => 'settings_page_wprocket',
			'rocket_mod_pagespeed_enabled' => false,
			'boxes' => [],
			'apache_get_modules' => [ 'mod_pagespeed' ]
		],
		'expected' => [
			'show_notice' => 1,
			'set_transient' => 1,
			'html' => $notice_html
		]
	],

	'shouldNotShowNoticeWhenNotFoundTransientWithApacheModuleDisabledAndNoHeaders' => [
		'config' => [
			'capability' => true,
			'current_screen' => 'settings_page_wprocket',
			'rocket_mod_pagespeed_enabled' => false,
			'apache_get_modules' => [],
			'home_response_headers' => [],
		],
		'expected' => [
			'show_notice' => 0,
			'set_transient' => 1,
			'html' => ''
		]
	],

	'shouldNotShowNoticeWhenNotFoundTransientWithNginxModuleDisabledAndNoHeaders' => [
		'config' => [
			'capability' => true,
			'current_screen' => 'settings_page_wprocket',
			'rocket_mod_pagespeed_enabled' => false,
			'home_response_headers' => [],
		],
		'expected' => [
			'show_notice' => 0,
			'set_transient' => 1,
			'html' => ''
		]
	],

	'shouldShowNoticeWhenNotFoundTransientWithNginxModuleEnabled' => [
		'config' => [
			'capability' => true,
			'current_screen' => 'settings_page_wprocket',
			'rocket_mod_pagespeed_enabled' => false,
			'home_response_headers' => [ 'X-Page-Speed' => 1 ],
			'boxes' => []
		],
		'expected' => [
			'show_notice' => 1,
			'set_transient' => 1,
			'html' => $notice_html
		]
	],

	'shouldShowNoticeWhenNotFoundTransientWithApacheModuleEnabledWithHomeRequest' => [
		'config' => [
			'capability' => true,
			'current_screen' => 'settings_page_wprocket',
			'rocket_mod_pagespeed_enabled' => false,
			'home_response_headers' => [ 'X-Mod-Pagespeed' => 1 ],
			'boxes' => []
		],
		'expected' => [
			'show_notice' => 1,
			'set_transient' => 1,
			'html' => $notice_html
		]
	],

];
