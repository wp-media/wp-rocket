<?php

$html = <<<HTML
<div id="wpr-mobile_cpcss_view" class="wpr-tools">
<div class="wpr-tools-col">
<div class="wpr-title3 wpr-tools-label wpr-icon-stack">
Enable Google Font Optimization</div>
<div class="wpr-field-description wpr-hide-on-click">
Improves font performance and combines multiple font requests to reduce the number of HTTP requests.</div>
<div class="wpr-field-description wpr-hide-on-click">
This is a one-time action and this button will be removed afterwards.<a href="https://docs.wp-rocket.me/article/1312-optimize-google-fonts" data-beacon-article="5e8687c22c7d3a7e9aea4c4a" target="_blank" rel="noopener noreferrer">
More info</a>
</div>
<div class="wpr-field-description wpr-field wpr-isHidden wpr-show-on-click">
Google Fonts Optimization is now enabled for your site.<a href="https://docs.wp-rocket.me/article/1312-optimize-google-fonts" data-beacon-article="5e8687c22c7d3a7e9aea4c4a" target="_blank" rel="noopener noreferrer">
More info</a>
</div>
</div>
<div class="wpr-tools-col">
<button id="wpr-action-rocket_enable_google_fonts" class="wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-refresh">
Optimize Google Fonts</button>
</div>
</div>

HTML
;

return [
	'shouldOutputEnablerSettingHtml' => [
		'config'   => [
			'is-user-auth'    => true,
			'is-gf-minify' => false,
		],
		'expected' => $html,
	],

	'shouldBailWhenUserAuthFails' => [
		'config'   => [
			'is-user-auth'    => false,
			'is-gf-minify' => false,
		],
		'expected' => '',
	],

	'shouldBailWhenMinifyAlreadyOn' => [
		'config'   => [
			'is-user-auth'    => true,
			'is-gf-minify' => true,
		],
		'expected' => '',
	],
];
