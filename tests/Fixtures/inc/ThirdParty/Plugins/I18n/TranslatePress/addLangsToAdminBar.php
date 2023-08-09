<?php
return [
    'shouldReturnUpdatedLangsLinks' => [
		'langlinks' => [],
		'expected' => [
			'fr_FR' => [
				'code' => 'fr',
				'flag' => '<img class="trp-flag-image" src="http://example.org/wp-content/translatepress-multilingual/assets/images/flags/fr_FR.png" width="18" height="12" alt="fr_FR" title="french">',
				'anchor' => 'french',
			],
			'en_US' => [
				'code' => 'en',
				'flag' => '<img class="trp-flag-image" src="http://example.org/wp-content/translatepress-multilingual/assets/images/flags/en_US.png" width="18" height="12" alt="en_US" title="english">',
				'anchor' => 'english',
			],
		],
    ],
];
