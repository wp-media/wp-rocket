<?php

namespace GroupOne\WapClient;

/**
 * Test double of the library class, with unavailable_reason() (library 2.6.0 and later).
 */
class AppPasswordManagerWithReason {
	public static $available   = false;
	public static $https_missing = false;
	public static $reason      = '';

	public static function are_app_passwords_available(): bool {
		return self::$available;
	}

	public static function is_https_missing(): bool {
		return self::$https_missing;
	}

	public static function unavailable_reason(): string {
		return self::$reason;
	}
}
