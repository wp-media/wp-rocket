<?php

return [
	'testShouldContainKnownAllowedKeyByDefault'  => [
		'config'   => [
			'filter_callback' => null,
		],
		'expected' => [
			'contains'         => 'cache_webp',
			'does_not_contain' => 'analytics_enabled',
		],
	],
	'testShouldNotContainCredentialKeyByDefault' => [
		'config'   => [
			'filter_callback' => null,
		],
		'expected' => [
			'contains'         => 'minify_css',
			'does_not_contain' => 'secret_cache_key',
		],
	],
	'testShouldContainCustomKeyWhenFilterAddsIt' => [
		'config'   => [
			'filter_callback' => static function ( array $allowlist ): array {
				$allowlist[] = 'my_custom_key';
				return $allowlist;
			},
		],
		'expected' => [
			'contains'         => 'my_custom_key',
			'does_not_contain' => null,
		],
	],
	'testShouldNotContainKeyWhenFilterRemovesIt' => [
		'config'   => [
			'filter_callback' => static function ( array $allowlist ): array {
				return array_values(
					array_filter(
						$allowlist,
						static function ( string $option_key ): bool {
							return 'cache_webp' !== $option_key;
						}
					)
				);
			},
		],
		'expected' => [
			'contains'         => null,
			'does_not_contain' => 'cache_webp',
		],
	],
];
