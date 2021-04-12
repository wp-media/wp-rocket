<?php

$script = <<<HTML
		<script>
		window.addEventListener( 'load', function() {
			var dismissBtn  = document.querySelectorAll( '#rocketcdn-promote-notice .notice-dismiss, #rocketcdn-promote-notice #rocketcdn-learn-more-dismiss' );

			dismissBtn.forEach(function(element) {
				element.addEventListener( 'click', function( event ) {
					var httpRequest = new XMLHttpRequest(),
						postData    = '';

					postData += 'action=rocketcdn_dismiss_notice';
					postData += '&nonce=wp_rocket_nonce';
					httpRequest.open( 'POST', 'http://example.org/wp-admin/admin-ajax.php' );
					httpRequest.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' )
					httpRequest.send( postData );
				});
			});
		});
		</script>
HTML
;

return [

	'testShouldDisplayNothingWhenWhiteLabel' => [
		'config' => [
			'white_label' => true,
			'home_url'    => 'http://example.org',
		],
		'expected' => '',
	],

	'testShouldDisplayNothingWhenNotLiveSite' => [
		'config' => [
			'home_url' => 'http://localhost',
		],
		'expected' => '',
	],

	'testShouldNotAddScriptWhenNoCapability' => [
		'config' => [
			'home_url' => 'http://example.org',
			'role'     => 'editor',
		],
		'expected' => '',
	],

	'testShouldNotAddScriptWhenNotRocketPage' => [
		'config' => [
			'home_url' => 'http://example.org',
			'role'     => 'administrator',
			'cap'      => 'rocket_manage_options',
			'screen'   => 'edit.php',
		],
		'expected' => '',
	],

	'testShouldNotAddScriptWhenDismissed' => [
		'config' => [
			'home_url'  => 'http://example.org',
			'role'      => 'administrator',
			'cap'       => 'rocket_manage_options',
			'screen'    => 'settings_page_wprocket',
			'dismissed' => true,
		],
		'expected' => '',
	],

	'testShouldNotAddScriptWhenActive' => [
		'config' => [
			'home_url'  => 'http://example.org',
			'role'      => 'administrator',
			'cap'       => 'rocket_manage_options',
			'screen'    => 'settings_page_wprocket',
			'transient' => [ 'subscription_status' => 'running' ],
		],
		'expected' => '',
	],

	'testShouldAddScriptWhenNotActive' => [
		'config' => [
			'home_url'  => 'http://example.org',
			'role'      => 'administrator',
			'cap'       => 'rocket_manage_options',
			'screen'    => 'settings_page_wprocket',
			'transient' => [ 'subscription_status' => 'cancelled' ],
		],
		'expected' => $script,
	],
];
