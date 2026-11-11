<?php

namespace GroupOne\WapClient;

/**
 * Test double of an older library copy, without unavailable_reason().
 */
class AppPasswordManagerLegacy {
	public static $available     = false;
	public static $https_missing = false;

	public static function are_app_passwords_available(): bool {
		return self::$available;
	}

	public static function is_https_missing(): bool {
		return self::$https_missing;
	}
}
