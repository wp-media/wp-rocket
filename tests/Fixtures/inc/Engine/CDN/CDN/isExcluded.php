<?php

return [
	// Standard asset URLs — should NOT be excluded.
	'allowsStylesheetWithVersion'          => [
		'url'      => '/wp-content/themes/mytheme/style.css?ver=1.0.0',
		'expected' => false,
	],
	'allowsImageWithWidthParam'            => [
		'url'      => '/wp-content/uploads/2024/01/hero.png?w=300',
		'expected' => false,
	],
	'allowsJsWithVersion'                  => [
		'url'      => '/wp-content/plugins/myplugin/script.js?ver=2.0',
		'expected' => false,
	],

	// Extension-based exclusions (pre-existing behaviour).
	'excludesPhpFiles'                     => [
		'url'      => '/wp-content/themes/mytheme/page.php',
		'expected' => true,
	],
	'excludesHtmlFiles'                    => [
		'url'      => '/wp-content/themes/mytheme/template.html',
		'expected' => true,
	],
	'excludesRootPath'                     => [
		'url'      => '/',
		'expected' => true,
	],

	// SGCaptcha / well-known paths — extension only in query string.
	'excludesSGCaptchaUrl'                 => [
		'url'      => '/.well-known/sgcaptcha/?r=%2Fwp-content%2Fthemes%2Fsavoy%2Fassets%2Fimg%2Fplaceholder.png',
		'expected' => true,
	],
	'excludesWellKnownWithQueryExtension'  => [
		'url'      => '/.well-known/some-service/?file=image.jpg',
		'expected' => true,
	],
	'excludesDynamicPathWithQueryExt'      => [
		'url'      => '/captcha/?image=challenge.png',
		'expected' => true,
	],
];
