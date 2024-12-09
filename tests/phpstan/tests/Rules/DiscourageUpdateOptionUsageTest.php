<?php

namespace WP_Rocket\Tests\phpstan\tests\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use WP_Rocket\Tests\phpstan\Rules\DiscourageUpdateOptionUsage;

class DiscourageUpdateOptionUsageTest extends RuleTestCase {

	protected function getRule(): Rule {
		return new DiscourageUpdateOptionUsage();
	}

	public function testValidShouldNotHaveErrors() {
		$this->analyse([__DIR__ . '/../data/DiscourageUpdateOptionUsageTest/valid.php'], [
		]);
	}

	public function testShouldGetError() {
		$this->analyse([__DIR__ . '/../data/DiscourageUpdateOptionUsageTest/not-valid.php'], [
			[
				"Usage of update_option() is discouraged. Use the Option object instead.",
				38
			]
		]);
	}
}
