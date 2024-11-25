<?php

namespace WP_Rocket\Tests\phpstan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class EnsureSubscribedEventsOutPutValid implements Rule {

	/**
	 * @inheritDoc
	 */
	public function getNodeType(): string {
		return Return_::class; // We want to inspect return statements
	}

	/**
	 * @inheritDoc
	 */
	public function processNode( Node $node, Scope $scope ): array {

		$errors = [];

		if (! $node instanceof Return_ || ! $node->expr) {
			return [];
		}

		$functionName = $scope->getFunctionName();
		if ('get_subscribed_events' !== $functionName) {
			return [];
		}

		if ( ! $node->expr instanceof Node\Expr\Array_) {
			return [
				RuleErrorBuilder::message('Return type of `get_subscribed_events` method should be an array')
								->build()
			];
		}

		foreach ($node->expr->items as $item) {
			if (! $item instanceof Node\Expr\ArrayItem) {
				continue;
			}

			if (! $item->key instanceof Node\Scalar\String_) {
				$errors []= RuleErrorBuilder::message('Events should be string')->build();
				continue;
			}

			$methodValue = $item->value;

			if($methodValue instanceof Node\Scalar\String_) {
				continue;
			}

			if(! $methodValue instanceof Node\Expr\Array_) {
				$errors []= RuleErrorBuilder::message('Callbacks should be string or array type')->build();
				continue;
			}

			foreach ($methodValue->items as $item) {
				if(! $item instanceof Node\Expr\ArrayItem) {
					continue;
				}

				if(! $item->value instanceof Node\Expr\Array_) {
					$errors= [...$errors, ...$this->validate_callback($methodValue)];
					break;
				}

				$definition = $item->value;

				$errors= [...$errors, ...$this->validate_callback($definition)];

			}
		}


		return $errors;
	}

	protected function validate_callback(Node\Expr\Array_ $definition): array {

		$errors = [];

		if ( count( $definition->items ) > 3 ) {
			return [
				RuleErrorBuilder::message( 'A registration cannot have more than 3 parameters' )
										 ->line( $definition->getStartLine() )
										 ->build()
			];
		}

		if ( count( $definition->items ) === 0 ) {
			return [
				RuleErrorBuilder::message( 'A registration cannot be an empty array' )
										 ->line( $definition->getStartLine() )
										 ->build()
				];
		}

		$item = array_shift( $definition->items );

		if ( ! $item->value instanceof Node\Scalar\String_ ) {
			return [
				RuleErrorBuilder::message( 'The first parameter from a registration should be a string' )
										 ->line( $definition->getStartLine() )
										 ->build()
			];
		}

		if(count($definition->items) == 0) {
			return [];
		}

		$item = array_shift( $definition->items );

		if ( ! $item->value instanceof Node\Scalar\LNumber ) {
			return [
				RuleErrorBuilder::message( 'The second parameter for the priority should be an integer' )
								->line( $definition->getStartLine() )
								->build()
			];
		}

		if(count($definition->items) == 0) {
			return [];
		}

		$item = array_shift( $definition->items );

		if ( ! $item->value instanceof Node\Scalar\LNumber ) {
			return [
				RuleErrorBuilder::message( 'The third parameter for the number of arguments should be an integer' )
								->line( $definition->getStartLine() )
								->build()
			];
		}

		return $errors;
	}
}
