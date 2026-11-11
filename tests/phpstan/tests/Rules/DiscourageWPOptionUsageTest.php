<?php

namespace WP_Rocket\Tests\phpstan\tests\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use WP_Rocket\Tests\phpstan\Rules\DiscourageWPOptionUsage;

class DiscourageWPOptionUsageTest extends RuleTestCase {

	protected function getRule(): Rule {
		return new DiscourageWPOptionUsage();
	}

	public function testValidShouldNotHaveErrors() {
		$this->analyse([__DIR__ . '/../data/DiscourageWPOptionUsageTest/valid.php'], [
		]);
	}

	public function testShouldHaveErrorWithDeleteOption() {
		$this->analyse([__DIR__ . '/../data/DiscourageWPOptionUsageTest/use-delete-option.php'], [
			[
				"Usage of delete_option() is discouraged. Use the Option object instead.",
				19
			]
		]);
	}

	public function testShouldHaveErrorWithGetOption() {
		$this->analyse([__DIR__ . '/../data/DiscourageWPOptionUsageTest/use-get-option.php'], [
			[
				"Usage of get_option() is discouraged. Use the Option object instead.",
				19
			]
		]);
	}

	public function testShouldHaveErrorWithUpdateOption() {
		$this->analyse([__DIR__ . '/../data/DiscourageWPOptionUsageTest/use-update-option.php'], [
			[
				"Usage of update_option() is discouraged. Use the Option object instead.",
				19
			]
		]);
	}
}
