<?php

namespace WP_Rocket\Tests\phpstan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class DiscourageWPOptionUsage implements Rule {
	public function getNodeType(): string {
		return FuncCall::class;
	}

	public function processNode( Node $node, Scope $scope ): array {
		if ( !$node instanceof FuncCall ) {
			return [];
		}

		$functionName = $node->name instanceof Node\Name ? $node->name->toString() : '';

		$discouragedFunctions = [
			'update_option' => true,
			'get_option'    => true,
			'delete_option' => true,
		];

		if ( isset( $discouragedFunctions[ $functionName ] ) ) {
			return [
				RuleErrorBuilder::message( sprintf( 'Usage of %1$s() is discouraged. Use the Option object instead.', $functionName ) )
					->identifier('custom.rules.discourageOptionUsage')
					->build(),
			];
		}

		return [];
	}
}
