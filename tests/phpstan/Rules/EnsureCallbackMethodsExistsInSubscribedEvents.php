<?php

namespace WP_Rocket\Tests\phpstan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class EnsureCallbackMethodsExistsInSubscribedEvents implements Rule {

	/**
	 * Returns the type of the node that this rule is interested in.
	 *
	 * @return string The class name of the node type.
	 */
	public function getNodeType(): string {
		return Return_::class;
	}

	/**
	 * Processes a node to ensure that callback methods exist in subscribed events.
	 *
	 * @param Node  $node The node to process.
	 * @param Scope $scope The scope in which the node is being processed.
	 * @return array An array of errors found during the processing of the node.
	 */
	public function processNode( Node $node, Scope $scope ): array {
		// Bail out early if the node is not a return statement with an expression.
		if ( ! ( $node instanceof Return_ && $node->expr ) ) {
			return [];
		}

		$function_name = $scope->getFunctionName();
		// Bail out early if the method is not `get_subscribed_events`.
		if ( 'get_subscribed_events' !== $function_name ) {
			return [];
		}

		// Check if the return expression is an array.
		if ( $node->expr instanceof Node\Expr\Array_ ) {
			return $this->analyzeArray( $node->expr, $scope );
		}

		return [];
	}

	/**
	 * Analyzes the array structure returned by `get_subscribed_events`.
	 *
	 * @param Node\Expr\Array_ $array_expr The array expression node to analyze.
	 * @param Scope            $scope The scope in which the array expression is being analyzed.
	 * @return array An array of errors found during the analysis of the array structure.
	 */
	private function analyzeArray( Node\Expr\Array_ $array_expr, Scope $scope ): array {
		$errors = [];

		foreach ( $array_expr->items as $item ) {
			// Skip invalid array items.
			if ( ! $item instanceof ArrayItem ) { // @phpstan-ignore-line PHPStan mess up with the type, and report a false positive error.
				continue;
			}

			$method_value = $item->value; // @phpstan-ignore-line Because of the above issue, we need to ignore the error here as it thinks it's unreachable.

			// Analyze the method value.
			$errors = array_merge( $errors, $this->analyzeMethodValue( $method_value, $scope ) );
		}

		return $errors;
	}

	/**
	 * Analyzes the method value from the array structure.
	 *
	 * @param Node  $method_value The method value node to analyze.
	 * @param Scope $scope The scope in which the method value is being analyzed.
	 * @return array An array of errors found during the analysis of the method value.
	 * @phpstan-ignore-next-line While running phpstan, it can't detect it's being used while in real it is.
	 */
	private function analyzeMethodValue( Node $method_value, Scope $scope ): array {
		$errors = [];

		if ( $method_value instanceof Node\Scalar\String_ ) {
			// Simple structure: array('hook_name' => 'method_name').
			return $this->checkIfMethodExistsInClass( $method_value->value, $scope, $method_value );
		}

		if ( $method_value instanceof Node\Expr\Array_ ) {
			// More complex structures: array or nested array.
			foreach ( $method_value->items as $sub_item ) {
				if ( ! ( $sub_item instanceof ArrayItem ) ) { // @phpstan-ignore-line
					continue;
				}

				// @phpstan-ignore-next-line While running phpstan, it can't detect it's being used while in real it is.
				if ( $sub_item->value instanceof Node\Scalar\String_ ) {
					// Handle string callback in nested array.
					$errors = array_merge( $errors, $this->checkIfMethodExistsInClass( $sub_item->value->value, $scope, $sub_item->value ) );
				} elseif ( $sub_item->value instanceof Node\Expr\Array_ ) {
					// Recursively analyze nested arrays.
					$errors = array_merge( $errors, $this->analyzeMethodValue( $sub_item->value, $scope ) );
				}
			}
		}

		return $errors;
	}

	/**
	 * Checks if a method exists in the class, its parent class, or its interfaces.
	 *
	 * @param string $method_name The name of the method to check.
	 * @param Scope  $scope The scope in which the method is being checked.
	 * @param Node   $node The node representing the method call.
	 * @return array An array of errors if the method does not exist, otherwise an empty array.
	 */
	public function checkIfMethodExistsInClass( string $method_name, Scope $scope, Node $node ): array {
		$class_reflection = $scope->getClassReflection();

		// Bail out early if the class reflection or method is found.
		if ( $class_reflection && $class_reflection->hasMethod( $method_name ) ) {
			return [];
		}

		$parent_class = $class_reflection ? $class_reflection->getParentClass() : null;
		if ( $parent_class && $parent_class->hasMethod( $method_name ) ) {
			return [];
		}

		foreach ( $class_reflection->getInterfaces() as $interface ) {
			if ( $interface->hasMethod( $method_name ) ) {
				return [];
			}
		}

		// If the method doesn't exist, return an error.
		$error_message = sprintf(
			"The callback function '%s' declared within 'get_subscribed_events' does not exist in the class '%s'.",
			$method_name,
			$class_reflection ? $class_reflection->getName() : 'unknown'
		);

		return [
			RuleErrorBuilder::message( $error_message )
				->line( $node->getLine() ) // Add the line number.
				->identifier( 'callbackMethodNotFound' )
				->build(),
		];
	}
}
