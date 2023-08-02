<?php
return [
    'shouldReturnUpdatedLangsLinks' => [
		'langlinks' => [],
		'expected' => [
			'fr' => [
				'code' => 'fr',
				'flag' => '<img class="trp-flag-image" src="http://example.org/wp-content/translatepress-multilingual/assets/images/flags/fr.png" width="18" height="12" alt="fr" title="french">',
				'anchor' => 'french',
			],
			'us' => [
				'code' => 'us',
				'flag' => '<img class="trp-flag-image" src="http://example.org/wp-content/translatepress-multilingual/assets/images/flags/us.png" width="18" height="12" alt="us" title="english">',
				'anchor' => 'english',
			],
		],
    ],
];
