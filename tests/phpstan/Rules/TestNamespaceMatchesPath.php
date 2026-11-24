<?php

namespace WP_Rocket\Tests\phpstan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Requires a class under tests/{Unit|Integration}/<path>/ to be in namespace
 * WP_Rocket\Tests\{Unit|Integration}\<path>, matching the folder case exactly.
 * Characters invalid in a namespace segment become "_", and a segment starting with a digit gets a "_" prefix
 * (3rd-party => _3rd_party).
 *
 * @implements Rule<InClassNode>
 */
class TestNamespaceMatchesPath implements Rule {
	public function getNodeType(): string {
		return InClassNode::class;
	}

	public function processNode( Node $node, Scope $scope ): array {
		$class = $node->getClassReflection();

		if ( $class->isAnonymous() ) {
			return [];
		}

		$file = str_replace( '\\', '/', $scope->getFile() );

		if ( ! preg_match( '#^.*/tests/(Unit|Integration)/(?:(.+)/)?[^/]+\.php$#', $file, $matches ) ) {
			return [];
		}

		$segments = [ 'WP_Rocket', 'Tests', $matches[1] ];

		foreach ( array_filter( explode( '/', $matches[2] ?? '' ), 'strlen' ) as $folder ) {
			$segment    = preg_replace( '/[^A-Za-z0-9_]/', '_', $folder );
			$segments[] = ctype_digit( $segment[0] ) ? '_' . $segment : $segment;
		}

		$expected = implode( '\\', $segments );
		$position = strrpos( $class->getName(), '\\' );
		$actual   = false === $position ? '' : substr( $class->getName(), 0, $position );

		if ( $actual === $expected ) {
			return [];
		}

		return [
			RuleErrorBuilder::message( sprintf( 'Namespace "%1$s" of %2$s does not match its folder: expected "%3$s".', $actual, $class->getName(), $expected ) )
				->identifier( 'custom.rules.testNamespaceMatchesPath' )
				->build(),
		];
	}
}
