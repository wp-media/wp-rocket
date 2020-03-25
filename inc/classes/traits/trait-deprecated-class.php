<?php

namespace WP_Rocket\Traits;

/**
 * Trait to use in a deprecated class, or a class containing deprecated methods.
 */
trait DeprecatedClass {
	/**
	 * Marks a class as deprecated and informs when it has been used.
	 * Similar to _deprecated_constructor(), but with different strings.
	 * The current behavior is to trigger a user error if `WP_DEBUG` is true.
	 *
	 * @since  3.6
	 * @author Grégory Viguier
	 *
	 * @param string $version     The version of WordPress that deprecated the class.
	 * @param string $replacement Optional. The method that should have been called. Default null.
	 */
	private static function deprecated_class( $version, $replacement = null ) {
		/**
		 * Fires when a deprecated class is called.
		 *
		 * @since  3.6
		 * @author Grégory Viguier
		 *
		 * @param string $class       The class containing the deprecated constructor.
		 * @param string $version     The version of WordPress that deprecated the class.
		 * @param string $replacement Optional. The method that should have been called.
		 */
		do_action( 'rocket_deprecated_class_run', self::class, $version, $replacement );

		if ( ! WP_DEBUG ) {
			return;
		}

		/**
		 * Filters whether to trigger an error for deprecated classes.
		 * `WP_DEBUG` must be true in addition to the filter evaluating to true.
		 *
		 * @since  3.6
		 * @author Grégory Viguier
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
				call_user_func(
					'trigger_error',
					sprintf(
						/* translators: 1: PHP class name, 2: version number, 3: replacement class name. */
						__( 'The called class %1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', 'rocket' ),
						'<code>' . self::class . '</code>',
						'<strong>' . $version . '</strong>',
						'<code>' . $replacement . '</code>'
					)
				);
				return;
			}

			/**
			 * Without replacement.
			 */
			call_user_func(
				'trigger_error',
				sprintf(
					/* translators: 1: PHP class name, 2: version number. */
					__( 'The called class %1$s is <strong>deprecated</strong> since version %2$s!', 'rocket' ),
					'<code>' . self::class . '</code>',
					'<strong>' . $version . '</strong>'
				)
			);
			return;
		}

		if ( ! empty( $replacement ) ) {
			/**
			 * With replacement.
			 */
			call_user_func(
				'trigger_error',
				sprintf(
					'The called class %1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.',
					'<code>' . self::class . '</code>',
					'<strong>' . $version . '</strong>',
					'<code>' . $replacement . '</code>'
				)
			);
			return;
		}

		/**
		 * Without replacement.
		 */
		call_user_func(
			'trigger_error',
			sprintf(
				'The called class %1$s is <strong>deprecated</strong> since version %2$s!',
				'<code>' . self::class . '</code>',
				'<strong>' . $version . '</strong>'
			)
		);
	}
}
