<?php
/**
 * Used to safely deserialize schedule information when retrieving a stored scheduled action.
 *
 * This helps to guard against deserialization attacks, while maintaining backwards compatibility
 * with older versions of Action Scheduler.
 *
 * @internal This implementation is subject to change without notice or back-compat measures.
 * @see      https://github.com/woocommerce/action-scheduler/issues/1318
 * @since    4.1.0
 */
class ActionScheduler_ScheduleDeserializer {
	/**
	 * Maximum object-graph depth we will walk while vetting a blob.
	 *
	 * Real schedules nest only a handful of levels deep (a cron schedule's CronExpression graph is
	 * the deepest at ~6). Anything beyond this bound is treated as untrustworthy rather than walked.
	 *
	 * @var int
	 */
	private const MAX_GRAPH_DEPTH = 100;

	/**
	 * Action Scheduler's own concrete schedule classes. Along with any class that implements
	 * ActionScheduler_Schedule, these are always trusted.
	 *
	 * @var string[]
	 */
	private const KNOWN_SCHEDULE_CLASSES = array(
		ActionScheduler_SimpleSchedule::class,
		ActionScheduler_IntervalSchedule::class,
		ActionScheduler_CronSchedule::class,
		ActionScheduler_CanceledSchedule::class,
		ActionScheduler_NullSchedule::class,
	);

	/**
	 * The default supporting classes Action Scheduler is willing to instantiate when nested in a schedule.
	 *
	 * @var string[]
	 */
	private const DEFAULT_NESTED_CLASSES = array(
		// The bundled CronExpression family, which ActionScheduler_CronSchedule nests.
		CronExpression::class,
		CronExpression_FieldFactory::class,
		CronExpression_AbstractField::class,
		CronExpression_MinutesField::class,
		CronExpression_HoursField::class,
		CronExpression_DayOfMonthField::class,
		CronExpression_MonthField::class,
		CronExpression_DayOfWeekField::class,
		CronExpression_YearField::class,

		// Additional trusted classes, likely to be used in schedule representations.
		ActionScheduler_DateTime::class,
		DateTime::class,
		DateTimeImmutable::class,
		DateTimeZone::class,
		DateInterval::class,
	);

	/**
	 * The raw serialized schedule blob being deserialized.
	 *
	 * @var string
	 */
	protected $source_data;

	/**
	 * The class name of the outermost object in the blob.
	 *
	 * @var string
	 */
	protected $outer_class;

	/**
	 * List of classes that are known to be acceptable outer classes.
	 *
	 * @var string[]
	 */
	private $allowed_scheduler_classes;

	/**
	 * Any nested objects must be instances of one of these classes.
	 *
	 * @var string[]
	 */
	private $allowed_nested_classes;

	/**
	 * Object IDs already visited during the walk, keyed by spl_object_id, to short-circuit cycles
	 * and shared references.
	 *
	 * @var array<int,bool>
	 */
	private $seen = array();

	/**
	 * Unexpected/disallowed class names discovered nested in the blob.
	 *
	 * @var string[]
	 */
	private $potential_offenders;

	/**
	 * Safely deserialize a stored schedule blob without instantiating unexpected classes.
	 *
	 * @param mixed $blob The raw serialized schedule blob as stored in the database.
	 *
	 * @return ActionScheduler_Schedule|false
	 */
	public static function unserialize( $blob ) {
		/**
		 * Filters the list of classes that are permitted as nested properties of a schedule object.
		 *
		 * The result of this filter is not memoized: this is deliberate, so that it can be changed
		 * if the context (including the current blog, in a multisite network) alters during the
		 * same request.
		 *
		 * @since 4.1.0
		 *
		 * @param string[] $default List of allowed nested class names.
		 */
		$allowed_nested = (array) apply_filters( 'action_scheduler_allowed_nested_schedule_classes', self::DEFAULT_NESTED_CLASSES );
		$deserializer   = new self( self::KNOWN_SCHEDULE_CLASSES, $allowed_nested );
		return $deserializer( $blob );
	}

	/**
	 * Build our ActionScheduler_ScheduleDeserializer instance.
	 *
	 * @param array $allowed_scheduler_classes Scheduler classes we accept.
	 * @param array $allowed_nested_classes    Nested classes we accept.
	 */
	public function __construct( array $allowed_scheduler_classes, array $allowed_nested_classes ) {
		$this->allowed_scheduler_classes = $allowed_scheduler_classes;
		$this->allowed_nested_classes    = $allowed_nested_classes;
	}

	/**
	 * Deserialize a blob of serialized data.
	 *
	 * @param mixed $serialized_blob Data to unserialize. Expected to be a string.
	 *
	 * @return ActionScheduler_Schedule|false
	 */
	public function __invoke( $serialized_blob ) {
		if ( ! is_string( $serialized_blob ) || '' === $serialized_blob ) {
			return false;
		}

		$this->seen                = array();
		$this->potential_offenders = array();
		$this->source_data         = $serialized_blob;

		try {
			return $this->deserialize();
		} catch ( Throwable $e ) {
			return false;
		}
	}

	/**
	 * Perform the two-pass deserialization for this instance's already-validated blob.
	 *
	 * @return ActionScheduler_Schedule|false
	 */
	private function deserialize() {
		// Initial attempt to unserialize, specifying all trusted classes: for default actions using
		// supplied schedule classes, this should succeed without any problems. Otherwise, the result
		// may contain one or more __PHP_Incomplete_Class references that we will need to examine.
		$all_trusted_classes = array_merge( $this->allowed_scheduler_classes, $this->allowed_nested_classes );
		$candidate           = @unserialize( $this->source_data, array( 'allowed_classes' => $all_trusted_classes ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize, WordPress.PHP.NoSilencedErrors.Discouraged

		// A schedule is always an object graph.
		if ( ! is_object( $candidate ) ) {
			return false;
		}

		$this->outer_class = $this->class_name_of( $candidate );

		// Verify that we have a valid schedule representation, scanning for disallowed nested classes.
		if ( ! $this->is_schedule_implementation( $this->outer_class ) || ! $this->scan_object_graph( $candidate, true, 0 ) ) {
			return $this->reject( $this->outer_class, false );
		}

		// If the candidate is an acceptable schedule class, and doesn't contain any potential offenders, it is safe.
		if ( ! $candidate instanceof __PHP_Incomplete_Class && empty( $this->potential_offenders ) ) {
			return $candidate;
		}

		// Otherwise the top-level object is itself a valid schedule, but references one or more disallowed classes.
		foreach ( $this->potential_offenders as $offender ) {
			if ( ! $this->is_allowed_nested_class( $offender, $this->allowed_nested_classes ) ) {
				return $this->reject( $offender, true );
			}
		}

		// If we reach this point, any potential offenders must in fact be safe.
		$allowed = array_values(
			array_unique(
				array_merge( $all_trusted_classes, array( $this->outer_class ), $this->potential_offenders )
			)
		);

		// Re-run unserialize.
		return @unserialize( $this->source_data, array( 'allowed_classes' => $allowed ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize, WordPress.PHP.NoSilencedErrors.Discouraged
	}

	/**
	 * Walk the parsed graph once, recording unexpected (placeholder) classes and guarding traversal.
	 *
	 * Objects whose class was outside the safe set are __PHP_Incomplete_Class placeholders; their
	 * original names are collected into $this->potential_offenders (the root is excluded — it is tracked
	 * separately as $this->outer_class). Real objects are trusted and only recursed into so a placeholder
	 * nested inside one is still found. A tampered blob can decode (via `r:`/`R:` references) into a
	 * self-referential or extremely deep graph, so visited objects are tracked to short-circuit cycles
	 * and shared references, and recursion depth is capped.
	 *
	 * @param mixed $value   The value to walk (object, array, or scalar).
	 * @param bool  $is_root Whether $value is the outermost object (excluded from $this->potential_offenders).
	 * @param int   $depth   Current recursion depth.
	 *
	 * @return bool True if the value was fully walked; false if it was too deep to vet safely.
	 */
	private function scan_object_graph( $value, bool $is_root, int $depth ): bool {
		if ( $depth > self::MAX_GRAPH_DEPTH ) {
			return false;
		}

		if ( is_object( $value ) ) {
			$object_id = spl_object_id( $value );
			if ( isset( $this->seen[ $object_id ] ) ) {
				return true; // Already walked (cycle or shared reference); nothing new to collect.
			}
			$this->seen[ $object_id ] = true;

			$is_placeholder = $value instanceof __PHP_Incomplete_Class;
			if ( $is_placeholder && ! $is_root ) {
				$this->potential_offenders[] = $this->class_name_of( $value );
			}

			foreach ( (array) $value as $key => $child ) {
				if ( $is_placeholder && '__PHP_Incomplete_Class_Name' === $key ) {
					continue;
				}
				if ( ! $this->scan_object_graph( $child, false, $depth + 1 ) ) {
					return false;
				}
			}

			return true;
		}

		if ( is_array( $value ) ) {
			foreach ( $value as $child ) {
				if ( ! $this->scan_object_graph( $child, false, $depth + 1 ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Handle a blob that references a class outside the vetted set.
	 *
	 * Always surfaces the event for observability. Under enforcement (the default) the blob is
	 * rejected by returning false, which callers already handle like a corrupt schedule.
	 *
	 * Shadow mode ("report only") relaxes this, but only for a $shadow_eligible rejection — one where
	 * the outermost object is a valid schedule and merely a *nested* class was unexpected. That is the
	 * sole case where a mis-tuned allow-list could disrupt an otherwise-legitimate action, so it is the
	 * only case shadow mode lets through. Structural rejections ($shadow_eligible false) — a top-level
	 * non-schedule (the issue #1318 PoC) or an unwalkable object graph — have no legitimate form and are
	 * always rejected, even in shadow mode, so report-only can never be downgraded into reviving the
	 * vulnerability.
	 *
	 * @param string $offending_class The class that triggered the rejection.
	 * @param bool   $shadow_eligible Whether shadow mode may let this particular rejection through.
	 * @return ActionScheduler_Schedule|false False when rejected. A shadow-mode passthrough is only
	 *                                        reached for a nested rejection, i.e. after the top-level
	 *                                        schedule gate passed, so any object returned is a schedule.
	 */
	protected function reject( $offending_class, $shadow_eligible ) {
		// A rejection stands unless we are in shadow mode AND this rejection is eligible for report-only
		// passthrough. Resolve it once so the reported outcome and the branch taken cannot disagree, and
		// the action_scheduler_enforce_schedule_allowed_classes filter runs a single time.
		$rejected = self::is_enforced() || ! $shadow_eligible;

		/**
		 * Fires when a stored schedule blob references a class Action Scheduler did not expect.
		 *
		 * @since 4.1.0
		 *
		 * @param string   $offending_class    The class that triggered the rejection.
		 * @param string   $outer_class        The outermost class in the blob.
		 * @param string[] $unexpected_classes Every class the blob names that is outside the safe set.
		 * @param bool     $rejected           Whether the blob was rejected (true) or allowed through in
		 *                                     shadow mode (false). Always true for a structural rejection
		 *                                     (top-level non-schedule or unwalkable graph), which shadow
		 *                                     mode cannot relax.
		 */
		do_action( 'action_scheduler_unexpected_schedule_class', $offending_class, $this->outer_class, $this->potential_offenders, $rejected );

		if ( $rejected ) {
			return false;
		}

		// Shadow mode, nested-only rejection: behave as Action Scheduler did before this change. This is
		// reached only when the top-level object is a valid schedule, so the unrestricted parse can no
		// longer instantiate a bare top-level gadget.
		return @unserialize( $this->source_data ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize, WordPress.PHP.NoSilencedErrors.Discouraged
	}

	/**
	 * Decide whether the outermost class in a schedule blob may be instantiated.
	 *
	 * Allowed if the class is loaded and implements ActionScheduler_Schedule. A class that is not
	 * loaded cannot be validated (and, prior to this change, already produced a broken action), so
	 * rejecting it here does not regress any case that previously worked.
	 *
	 * @param string $class_name Class name discovered in the blob.
	 * @return bool
	 */
	private function is_schedule_implementation( string $class_name ): bool {
		if ( '' === $class_name || ! class_exists( $class_name ) ) {
			return false;
		}

		$implements = class_implements( $class_name );
		return is_array( $implements ) && isset( $implements['ActionScheduler_Schedule'] );
	}

	/**
	 * Decide whether a nested class (a property of the schedule) may be instantiated.
	 *
	 * The only classes Action Scheduler itself ever nests inside a schedule are the bundled
	 * CronExpression family. Composite schedules that nest another schedule are also fine. The
	 * default list is filterable so extenders can vet their own supporting classes.
	 *
	 * @param string   $class_name     Class name discovered nested in the blob.
	 * @param string[] $allowed_nested The resolved nested allow-list to check against.
	 *
	 * @return bool
	 */
	private function is_allowed_nested_class( string $class_name, array $allowed_nested ): bool {
		if ( '' === $class_name ) {
			return false;
		}

		if ( in_array( $class_name, $allowed_nested, true ) ) {
			return true;
		}

		// A schedule nested inside another schedule (composite schedules) is safe by the same rule
		// as the top-level check.
		if ( class_exists( $class_name ) ) {
			$implements = class_implements( $class_name );
			if ( is_array( $implements ) && isset( $implements['ActionScheduler_Schedule'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the class name of an object parsed with a restricted allowed_classes set.
	 *
	 * Objects whose class was disallowed become __PHP_Incomplete_Class, which hides the original
	 * name behind get_class(); it lives in the __PHP_Incomplete_Class_Name pseudo-property instead.
	 * stdClass is never converted by PHP, so its real name is returned directly.
	 *
	 * @param object $maybe_incomplete Object parsed with a restricted allowed_classes set.
	 * @return string The original class name, or '' if it cannot be determined.
	 */
	private function class_name_of( object $maybe_incomplete ): string {
		if ( $maybe_incomplete instanceof __PHP_Incomplete_Class ) {
			$vars = (array) $maybe_incomplete;
			return isset( $vars['__PHP_Incomplete_Class_Name'] ) ? (string) $vars['__PHP_Incomplete_Class_Name'] : '';
		}

		return get_class( $maybe_incomplete );
	}

	/**
	 * Whether an unexpected class causes the blob to be rejected (true) or merely reported while the
	 * legacy, unrestricted deserialization proceeds (false, "shadow mode").
	 *
	 * Enforcement is on by default. Site owners can opt into shadow mode for a release to gather
	 * data before enforcing, without changing any stored data.
	 *
	 * @return bool
	 */
	public static function is_enforced(): bool {
		/**
		 * Filters whether Action Scheduler rejects schedule blobs referencing unexpected classes.
		 *
		 * Return false to run in "shadow mode": unexpected classes are reported via the
		 * `action_scheduler_unexpected_schedule_class` action but the blob is still deserialized
		 * exactly as it was before this hardening. Useful to confirm the allow-list is complete for
		 * your site before switching enforcement on.
		 *
		 * @since 4.1.0
		 *
		 * @param bool $enforced Whether to reject blobs with unexpected classes. Default true.
		 */
		return (bool) apply_filters( 'action_scheduler_enforce_schedule_allowed_classes', true );
	}
}
