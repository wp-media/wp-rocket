<?php

return [
	'test_data' => [
		'testShouldEchoDefaultWithNulledLicense' => [
			'config'   => [
				'locale'        => 'en_US',
				'customer_data' => false,
			],
			'expected' => [
				'data'   => [
					'form_id'  => '44cc73fb-7636-4206-b115-c7b33823551b',
					'identify' => '{"email":"dummy@wp-rocket.me","Website":"http:\/\/example.org"}',
					'session'  => '{"Website":"http:\/\/example.org","WordPress Version":"5.4","WP Rocket Version":"3.6","Theme":"WordPress Default","Plugins Enabled":"","WP Rocket Active Options":""}',
					'prefill'  => '{"fields":[{"id":21728,"value":108003}]}',
				],
				'script' => <<<SCRIPT
<script>!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});
window.Beacon('init', '44cc73fb-7636-4206-b115-c7b33823551b')
window.Beacon("identify", {"email":"dummy@wp-rocket.me","Website":"http:\/\/example.org"});
window.Beacon("session-data", {"Website":"http:\/\/example.org","WordPress Version":"5.4","WP Rocket Version":"3.6","Theme":"WordPress Default","Plugins Enabled":"","WP Rocket Active Options":""});
window.Beacon("prefill", {"fields":[{"id":21728,"value":108003}]});
window.addEventListener("hashchange", function () {
	window.Beacon("suggest");
}, false);</script>
SCRIPT
				,
			],
		],
		'testShouldEchoDefaultWithUnavailableLicense' => [
			'config'   => [
				'locale'        => 'en_US',
				'customer_data' => (object) [
					'licence_account' => 'Unavailable',
				],
			],
			'expected' => [
				'data'   => [
					'form_id'  => '44cc73fb-7636-4206-b115-c7b33823551b',
					'identify' => '{"email":"dummy@wp-rocket.me","Website":"http:\/\/example.org"}',
					'session'  => '{"Website":"http:\/\/example.org","WordPress Version":"5.4","WP Rocket Version":"3.6","Theme":"WordPress Default","Plugins Enabled":"","WP Rocket Active Options":""}',
					'prefill'  => '{"fields":[{"id":21728,"value":108003}]}',
				],
				'script' => <<<SCRIPT
<script>!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});
window.Beacon('init', '44cc73fb-7636-4206-b115-c7b33823551b')
window.Beacon("identify", {"email":"dummy@wp-rocket.me","Website":"http:\/\/example.org"});
window.Beacon("session-data", {"Website":"http:\/\/example.org","WordPress Version":"5.4","WP Rocket Version":"3.6","Theme":"WordPress Default","Plugins Enabled":"","WP Rocket Active Options":""});
window.Beacon("prefill", {"fields":[{"id":21728,"value":108003}]});
window.addEventListener("hashchange", function () {
	window.Beacon("suggest");
}, false);</script>
SCRIPT
				,
			],
		],
		'testShouldEchoDefaultWithSingleLicense' => [
			'config'   => [
				'locale'        => 'en_US',
				'customer_data' => (object) [
					'licence_account' => 'Single',
				],
			],
			'expected' => [
				'data'   => [
					'form_id'  => '44cc73fb-7636-4206-b115-c7b33823551b',
					'identify' => '{"email":"dummy@wp-rocket.me","Website":"http:\/\/example.org"}',
					'session'  => '{"Website":"http:\/\/example.org","WordPress Version":"5.4","WP Rocket Version":"3.6","Theme":"WordPress Default","Plugins Enabled":"","WP Rocket Active Options":""}',
					'prefill'  => '{"fields":[{"id":21728,"value":108000}]}',
				],
				'script' => <<<SCRIPT
<script>!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});
window.Beacon('init', '44cc73fb-7636-4206-b115-c7b33823551b')
window.Beacon("identify", {"email":"dummy@wp-rocket.me","Website":"http:\/\/example.org"});
window.Beacon("session-data", {"Website":"http:\/\/example.org","WordPress Version":"5.4","WP Rocket Version":"3.6","Theme":"WordPress Default","Plugins Enabled":"","WP Rocket Active Options":""});
window.Beacon("prefill", {"fields":[{"id":21728,"value":108000}]});
window.addEventListener("hashchange", function () {
	window.Beacon("suggest");
}, false);</script>
SCRIPT
				,
			],
		],
		'testShouldEchoDefaultWithPlusLicense' => [
			'config'   => [
				'locale'        => 'en_US',
				'customer_data' => (object) [
					'licence_account' => 'Plus',
				],
			],
			'expected' => [
				'data'   => [
					'form_id'  => '44cc73fb-7636-4206-b115-c7b33823551b',
					'identify' => '{"email":"dummy@wp-rocket.me","Website":"http:\/\/example.org"}',
					'session'  => '{"Website":"http:\/\/example.org","WordPress Version":"5.4","WP Rocket Version":"3.6","Theme":"WordPress Default","Plugins Enabled":"","WP Rocket Active Options":""}',
					'prefill'  => '{"fields":[{"id":21728,"value":108001}]}',
				],
				'script' => <<<SCRIPT
<script>!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});
window.Beacon('init', '44cc73fb-7636-4206-b115-c7b33823551b')
window.Beacon("identify", {"email":"dummy@wp-rocket.me","Website":"http:\/\/example.org"});
window.Beacon("session-data", {"Website":"http:\/\/example.org","WordPress Version":"5.4","WP Rocket Version":"3.6","Theme":"WordPress Default","Plugins Enabled":"","WP Rocket Active Options":""});
window.Beacon("prefill", {"fields":[{"id":21728,"value":108001}]});
window.addEventListener("hashchange", function () {
	window.Beacon("suggest");
}, false);</script>
SCRIPT
				,
			],
		],
		'testShouldEchoDefaultWithInfiniteLicense' => [
			'config'   => [
				'locale'        => 'en_US',
				'customer_data' => (object) [
					'licence_account' => 'Infinite',
				],
			],
			'expected' => [
				'data'   => [
					'form_id'  => '44cc73fb-7636-4206-b115-c7b33823551b',
					'identify' => '{"email":"dummy@wp-rocket.me","Website":"http:\/\/example.org"}',
					'session'  => '{"Website":"http:\/\/example.org","WordPress Version":"5.4","WP Rocket Version":"3.6","Theme":"WordPress Default","Plugins Enabled":"","WP Rocket Active Options":""}',
					'prefill'  => '{"fields":[{"id":21728,"value":108002}]}',
				],
				'script' => <<<SCRIPT
<script>!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});
window.Beacon('init', '44cc73fb-7636-4206-b115-c7b33823551b')
window.Beacon("identify", {"email":"dummy@wp-rocket.me","Website":"http:\/\/example.org"});
window.Beacon("session-data", {"Website":"http:\/\/example.org","WordPress Version":"5.4","WP Rocket Version":"3.6","Theme":"WordPress Default","Plugins Enabled":"","WP Rocket Active Options":""});
window.Beacon("prefill", {"fields":[{"id":21728,"value":108002}]});
window.addEventListener("hashchange", function () {
	window.Beacon("suggest");
}, false);</script>
SCRIPT
				,
			],
		],
		'testShouldEchoFR' => [
			'config'   => [
				'locale'        => 'fr_FR',
				'customer_data' => false,
			],
			'expected' => [
				'data'   => [
					'form_id'  => '9db9417a-5e2f-41dd-8857-1421d5112aea',
					'identify' => '{"email":"dummy@wp-rocket.me","Website":"http:\/\/example.org"}',
					'session'  => '{"Website":"http:\/\/example.org","WordPress Version":"5.4","WP Rocket Version":"3.6","Theme":"WordPress Default","Plugins Enabled":"","WP Rocket Active Options":""}',
					'prefill'  => '{"fields":[{"id":21728,"value":108003}]}',
				],
				'script' => <<<SCRIPT
<script>!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});
window.Beacon('init', '9db9417a-5e2f-41dd-8857-1421d5112aea')
window.Beacon("identify", {"email":"dummy@wp-rocket.me","Website":"http:\/\/example.org"});
window.Beacon("session-data", {"Website":"http:\/\/example.org","WordPress Version":"5.4","WP Rocket Version":"3.6","Theme":"WordPress Default","Plugins Enabled":"","WP Rocket Active Options":""});
window.Beacon("prefill", {"fields":[{"id":21728,"value":108003}]});
window.addEventListener("hashchange", function () {
	window.Beacon("suggest");
}, false);</script>
SCRIPT
				,
			],
		],
	],
];
