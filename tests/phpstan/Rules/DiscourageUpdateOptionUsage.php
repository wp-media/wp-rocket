<?php

namespace WP_Rocket\Tests\phpstan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class DiscourageUpdateOptionUsage implements Rule
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

		if ( $node->name instanceof Node\Name && $node->name->toString() === 'update_option' ) {
			return [
				RuleErrorBuilder::message( 'Usage of update_option() is discouraged. Use the Option object instead.' )
					->identifier( 'custom.rules.discourageApplyFilters' )
					->build(),
			];
		}

		return [];
	}
}
