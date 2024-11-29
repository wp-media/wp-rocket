<?php

namespace WP_Rocket\Tests\phpstan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class DiscourageWPOptionUsage implements Rule
{
	public function getNodeType(): string
	{
		return FuncCall::class;
	}

	public function processNode( Node $node, Scope $scope ): array
	{
		if (!$node instanceof FuncCall) {
			return [];
		}

		$functionName = $node->name instanceof Node\Name ? $node->name->toString() : '';

		$discouragedFunctions = [
			'update_option' => 'Usage of update_option() is discouraged. Use the Option object instead.',
			'get_option' => 'Usage of get_option() is discouraged. Use the Option object instead.',
			'delete_option' => 'Usage of delete_option() is discouraged. Use the Option object instead.',
		];

		if (isset($discouragedFunctions[$functionName])) {
			return [
				RuleErrorBuilder::message($discouragedFunctions[$functionName])
					->identifier('custom.rules.discourageOptionUsage')
					->build(),
			];
		}

		return [];
	}
}
