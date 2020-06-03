<?php
return [
    // Emoji, Page Cache, Minify enabled in Hummingbird.
    [
        'hb_settings' => [
			'advanced' => [
				'emoji' => true,
			],
			'page_cache' => [
				'enabled' => true,
			],
			'minify' => [
				'enabled' => true,
			],
        ],
        'html' => <<<HTML
<p>Please deactivate the following Hummingbird options which conflict with WP Rocket features:</p>
<ul>
    <li>Hummingbird <em>page caching</em> conflicts with WP Rocket <em>page caching</em></li>
    <li>Hummingbird <em>asset optimization</em> conflicts with WP Rocket <em>file optimization</em></li>
    <li>Hummingbird <em>disable emoji</em> conflicts with WP Rockets <em>disable emoji</em></li>
</ul>
HTML
	    ,
    ],
];
