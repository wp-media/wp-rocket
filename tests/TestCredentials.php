<?php

namespace WP_Rocket\Tests;

/**
 * Fake licence and RocketCDN credentials for tests.
 *
 * HTTP requests are mocked, so tests only need consistent values, not real secrets.
 */
final class TestCredentials {
	const EMAIL = 'test@example.org';

	const KEY = 'abcdef12';

	const ROCKETCDN_TOKEN = 'a1b2c3d4e5f6a7b8c9d0';
}
