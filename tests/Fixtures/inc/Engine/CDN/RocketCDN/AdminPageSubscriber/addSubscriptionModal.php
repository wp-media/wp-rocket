<?php

return [

	'testShouldDisplayNothingWhenWhiteLabel' => [
		'config'   => [
			'white_label' => true,
			'home_url'    => 'http://localhost',
		],
		'expected' => '',
	],

	'testShouldDisplayNothingWhenNotLiveSite' => [
		'config'   => [
			'home_url' => 'http://localhost',
		],
		'expected' => '',
	],

	'testShouldDisplayModalWithProductionURL' => [
		'config' => [
			'home_url' => 'http://example.org',
		],
		'expected' => <<<HTML
<div class="wpr-rocketcdn-modal" id="wpr-rocketcdn-modal" aria-hidden="true">
	<div class="wpr-rocketcdn-modal__overlay" tabindex="-1">
		<div class="wpr-rocketcdn-modal__container" role="dialog" aria-modal="true" aria-labelledby="wpr-rocketcdn-modal-title">
			<div id="wpr-rocketcdn-modal-content">
				<iframe id="rocketcdn-iframe" src="https://api.wp-rocket.me/cdn/iframe?website=http://example.org&#038;callback=http://example.org/index.php?rest_route=/wp-rocket/v1/rocketcdn/" width="674" height="425"></iframe>
			</div>
		</div>
	</div>
</div>
HTML
	]
];
