<?php

namespace WP_Rocket\deprecated;

/**
 * Trait to use in a deprecated class, or a class containing deprecated methods.
 */
trait DeprecatedClassTrait {
	/**
	 * Marks a class as deprecated and informs when it has been used.
	 * Similar to _deprecated_constructor(), but with different strings.
	 * The current behavior is to trigger a user error if `WP_DEBUG` is true.
	 *
	 * @since  3.6
	 *
	 * @param string $version     The version of WordPress that deprecated the class.
	 * @param string $replacement Optional. The method that should have been called. Default null.
	 */
	private static function deprecated_class( $version, $replacement = null ) {
		/**
		 * Fires when a deprecated class is called.
		 *
		 * @since  3.6
		 *
		 * @param string $class       The class containing the deprecated constructor.
		 * @param string $version     The version of WordPress that deprecated the class.
		 * @param string $replacement Optional. The method that should have been called.
		 */
		do_action( 'rocket_deprecated_class_run', static::class, $version, $replacement );

		if ( ! WP_DEBUG ) {
			return;
		}

		/**
		 * Filters whether to trigger an error for deprecated classes.
		 * `WP_DEBUG` must be true in addition to the filter evaluating to true.
		 *
		 * @since  3.6
		 *
		 * @param bool $trigger Whether to trigger the error for deprecated classes. Default true.
		 */
		if ( ! apply_filters( 'rocket_deprecated_class_trigger_error', true ) ) {
			return;
		}

		if ( function_exists( '__' ) ) {
			if ( ! empty( $replacement ) ) {
				/**
				 * With replacement.
				 */
				$message = sprintf(
					/* translators: 1: PHP class name, 2: version number, 3: replacement class name. */
					__( 'The called class %1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', 'rocket' ),
					'<code>' . static::class . '</code>',
					'<strong>' . $version . '</strong>',
					'<code>' . $replacement . '</code>'
				);
			} else {
				/**
				 * Without replacement.
				 */
				$message = sprintf(
					/* translators: 1: PHP class name, 2: version number. */
					__( 'The called class %1$s is <strong>deprecated</strong> since version %2$s!', 'rocket' ),
					'<code>' . static::class . '</code>',
					'<strong>' . $version . '</strong>'
				);
			}
		} elseif ( ! empty( $replacement ) ) {
			/**
			 * With replacement.
			 */
			$message = sprintf(
				'The called class %1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.',
				'<code>' . static::class . '</code>',
				'<strong>' . $version . '</strong>',
				'<code>' . $replacement . '</code>'
			);
		} else {
			/**
			 * Without replacement.
			 */
			$message = sprintf(
				'The called class %1$s is <strong>deprecated</strong> since version %2$s!',
				'<code>' . static::class . '</code>',
				'<strong>' . $version . '</strong>'
			);
		}

		call_user_func( 'trigger_error', $message );
	}
}
