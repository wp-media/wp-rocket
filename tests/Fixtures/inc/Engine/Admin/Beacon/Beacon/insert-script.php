<?php

return [
	'vfs_dir'   => 'public/',
	'structure' => [
		'wp-content' => [
			'plugins' => [
				'wp-rocket' => [
					'views' => [
						'settings' => [
							'beacon.php' => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'views/settings/beacon.php' ),
						],
					],
				],
			],
		],
	],
	'test_data' => [
		'testShouldEchoDefault' => [
			'locale'   => 'en_US',
			'expected' => '<script type="text/javascript">!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});</script>
			<script type="text/javascript">window.Beacon(\'init\', \'44cc73fb-7636-4206-b115-c7b33823551b\')</script>
			<script>window.Beacon("identify", {"email":"dummy@wp-rocket.me","Website":"http:\/\/example.org"});</script>
			<script>window.Beacon("session-data", {"Website":"http:\/\/example.org","WordPress Version":"5.4","WP Rocket Version":"3.6","Theme":"WordPress Default","Plugins Enabled":"","WP Rocket Active Options":""});</script>
			<script>window.addEventListener("hashchange", function () {
	window.Beacon("suggest");
}, false);</script>',
		],
		'testShouldEchoFR' => [
			'locale'   => 'fr_FR',
			'expected' => '<script type="text/javascript">!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});</script>
			<script type="text/javascript">window.Beacon(\'init\', \'9db9417a-5e2f-41dd-8857-1421d5112aea\')</script>
			<script>window.Beacon("identify", {"email":"dummy@wp-rocket.me","Website":"http:\/\/example.org"});</script>
			<script>window.Beacon("session-data", {"Website":"http:\/\/example.org","WordPress Version":"5.4","WP Rocket Version":"3.6","Theme":"WordPress Default","Plugins Enabled":"","WP Rocket Active Options":""});</script>
			<script>window.addEventListener("hashchange", function () {
	window.Beacon("suggest");
}, false);</script>',
		],
	],
];
