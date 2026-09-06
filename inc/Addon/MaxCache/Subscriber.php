<?php
declare(strict_types=1);

namespace WP_Rocket\Addon\MaxCache;

use WP_Rocket\Admin\Options;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Wires the MAx Cache add-on into the plugin. Decisions belong to MaxCache, not here.
 *
 * @since 3.24
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * How long a question whose answer was "no" is left alone.
	 *
	 * @since 3.24
	 *
	 * @var int
	 */
	private const HOLD = 30 * MINUTE_IN_SECONDS;

	/**
	 * Instance of the MaxCache class.
	 *
	 * @var MaxCache
	 */
	private $maxcache;

	/**
	 * Instance of the Options class.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Whether this request is the one that switched the add-on on.
	 *
	 * @var bool
	 */
	private $switched_on = false;

	/**
	 * Constructor.
	 *
	 * @param MaxCache $maxcache    Instance of the MaxCache class.
	 * @param Options  $options_api Instance of the Options class.
	 */
	public function __construct( MaxCache $maxcache, Options $options_api ) {
		$this->maxcache    = $maxcache;
		$this->options_api = $options_api;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return [
			'after_rocket_htaccess_rules'           => 'add_rules',
			'rocket_htaccess_mod_rewrite'           => [ 'maybe_remove_rewrite_rules', 100 ],
			'rocket_maxcache_available'             => 'is_module_available',
			'rocket_maxcache_status'                => 'get_status',
			'rocket_htaccess_needed_without_apache' => [ 'is_htaccess_needed', 10, 2 ],
			'rocket_after_flush_htaccess'           => [ 'after_flush', 10, 2 ],
			'rocket_deactivation'                   => 'remove_on_deactivation',
			'rocket_hidden_settings_fields'         => 'carry_switch_state_through_the_form',
			'rocket_input_sanitize'                 => 'sanitize_option',
			'update_option_' . WP_ROCKET_SLUG       => [ 'forget_settings', 1 ],
			'pre_get_rocket_option_maxcache'        => 'return_switched_on',
			'admin_init'                            => [
				[ 'maybe_switch_on' ],
				[ 'maybe_restore_rules' ],
			],
			'admin_notices'                         => 'display_notice',
		];
	}

	/**
	 * Adds our directives to the block the plugin writes into .htaccess.
	 *
	 * @since 3.24
	 *
	 * @param string $rules Rules contributed by other integrations.
	 *
	 * @return string
	 */
	public function add_rules( $rules ): string {
		return (string) $rules . $this->maxcache->get_rules();
	}

	/**
	 * Stands the plugin's own serving rules down while the module serves.
	 *
	 * @since 3.24
	 *
	 * @param string|bool $rules The plugin's mod_rewrite serving rules, or false when a callback
	 *                           ahead of this one has already refused to serve them.
	 *
	 * @return string|bool
	 */
	public function maybe_remove_rewrite_rules( $rules ) {
		// A callback ahead of this one has already refused to serve by URI: leave its answer alone,
		// whichever way it said so.
		if ( ! is_string( $rules ) ) {
			return $rules;
		}

		// Asked rather than assumed: this callback stands at priority 100, and a refusal registered above
		// it has not run yet.
		if ( $this->maxcache->is_enabled() ) {
			return '';
		}

		return $rules;
	}

	/**
	 * Holds the corrective check off.
	 *
	 * @since 3.24
	 *
	 * @return void
	 */
	private function hold(): void {
		set_transient( 'rocket_maxcache_restore_check', 1, self::HOLD );
	}

	/**
	 * Holds the automatic decision off.
	 *
	 * @since 3.24
	 *
	 * @return void
	 */
	private function hold_the_decision(): void {
		set_transient( 'rocket_maxcache_switch_on_check', 1, self::HOLD );
	}

	/**
	 * Whether this admin request is one to write the file or the settings row from.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	private function acting_on_this_request(): bool {
		return ! wp_doing_ajax()
			&& current_user_can( 'rocket_manage_options' )
			&& rocket_valid_key()
			&& ! $this->is_settings_form();
	}

	/**
	 * Switches the add-on on once, the first time this site is seen on a server that can take it.
	 *
	 * @since 3.24
	 *
	 * @return void
	 */
	public function maybe_switch_on(): void {
		if ( ! $this->acting_on_this_request() ) {
			return;
		}

		$options = $this->options_api->get( 'settings', [] );

		if ( isset( $options['maxcache'] ) ) {
			return;
		}

		if ( false !== get_transient( 'rocket_maxcache_switch_on_check' ) ) {
			return;
		}

		if ( ! $this->maxcache->is_available() ) {
			// Held only so that this is not asked on every admin page: four connects to unix sockets
			// that are not there, which fail at once, so half an hour is generous.
			$this->hold_the_decision();

			return;
		}

		// A host that keeps the add-on off its platform has said this is not for its customers to
		// decide on, and a switch written here could never be turned back off.
		if ( ! $this->is_offered() ) {
			$this->hold_the_decision();

			return;
		}

		// A site that takes no rewrite of .htaccess is one the directives can never reach, so there is
		// nothing here to switch on and no answer of this site's to record.
		if ( $this->htaccess_writing_refused() ) {
			$this->hold_the_decision();

			return;
		}

		if ( ! $this->maxcache->can_switch_on() ) {
			/*
			 * The dearest answer of the three — a pass over every integration's
			 * rocket_htaccess_mod_rewrite callback, and on NGINX a wait on the daemon's socket — and
			 * the one that changes without anybody saving settings: a plugin that vetoed serving by
			 * URI is deactivated, a proxy goes away. Held for the same half hour, so that goes on
			 * being noticed.
			 *
			 * Except on the request that activated the plugin, where the settings row does not exist
			 * yet and the refusal says nothing about the site: no hold, so the next admin request asks
			 * again with the row in place.
			 */
			if ( [] !== $options ) {
				$this->hold_the_decision();
			}

			return;
		}

		// Read again rather than kept: everything asked above runs third-party code, and writing back
		// the copy this method started with would drop anything that code wrote to the row.
		$options = (array) $this->options_api->get( 'settings', [] );

		if ( isset( $options['maxcache'] ) ) {
			return;
		}

		$options['maxcache'] = 1;

		/*
		 * The plugin's own escape hatch for a write that is not a form submission: without it the
		 * registered sanitize callback adds a "Settings saved." notice to whatever admin page this
		 * happened on. That callback is also what drops the key again, so it is only worth writing
		 * where the callback is registered — otherwise the key would be stored for good and go on
		 * silencing that notice for every later write.
		 */
		if ( has_filter( 'sanitize_option_' . (string) rocket_get_constant( 'WP_ROCKET_SLUG', '' ) ) ) {
			$options['ignore'] = 1;
		}

		$this->options_api->set( 'settings', $options );

		// Read back rather than assumed: the write returns nothing, and a filter on the row or a
		// failed query would have this request announce a switch nobody stored.
		$stored = (array) $this->options_api->get( 'settings', [] );

		if ( ! isset( $stored['maxcache'] ) ) {
			// Held like every other refusal here: a row that does not take the write will not take the
			// next one either, and each attempt costs the probes, a re-sanitized row and a file rewrite.
			$this->hold_the_decision();

			return;
		}

		// The row is written, but the settings page renders from an Options_Data of its own, built from a
		// snapshot of the row taken before this request.
		$this->switched_on = true;

		set_transient( 'rocket_maxcache_notice', 1, WEEK_IN_SECONDS );
	}

	/**
	 * Answers what was just stored to everything that reads the option for the rest of this request.
	 *
	 * @since 3.24
	 *
	 * @param mixed $value Value passed by the filter.
	 *
	 * @return mixed
	 */
	public function return_switched_on( $value ) {
		return $this->switched_on ? 1 : $value;
	}

	/**
	 * Puts the plugin's own serving rules back once the module has stopped being the one to serve.
	 *
	 * @since 3.24
	 *
	 * @return void
	 */
	public function maybe_restore_rules(): void {
		if ( ! $this->acting_on_this_request() ) {
			return;
		}

		if ( false !== get_transient( 'rocket_maxcache_restore_check' ) ) {
			return;
		}

		// A notification the daemon never received leaves it compiled against the previous file while
		// the file itself is in order, so nothing below would notice.
		if ( false !== get_transient( 'rocket_maxcache_notify_failed' ) ) {
			$this->announce_written_file( $this->maxcache->htaccess_path() );
		}

		// Nothing of ours can be in the file on a site that has never answered this switch, and that
		// answer is in the row: asked first, it spares the probes and the read of the file.
		if ( ! $this->maxcache->decision_recorded() ) {
			$this->hold();

			return;
		}

		$available = $this->maxcache->is_available();

		// No module, the switch off, and nothing of ours in the file: there is no state here for anything
		// to put right, and none of the three changes without someone acting on this site.
		$directives = $this->maxcache->has_directives();

		if ( ! $available && ! $this->maxcache->options()->get( 'maxcache', 0 ) && ! $directives ) {
			$this->hold();

			return;
		}

		// Asked once, and below the exit above: every ask runs the whole reason chain, and with it one
		// pass over every integration's rocket_htaccess_mod_rewrite callback.
		$enabled = $this->maxcache->is_enabled();

		// Switched on and nothing of ours in the file: a hosting panel rewrote it, or the write that
		// should have put the directives there was refused.
		if ( $enabled && ! $directives ) {
			// Unless this site takes no rewrite of the file at all: there the flush writes nothing, and
			// asking again would pay the probes and the read for a write that cannot happen.
			if ( $this->htaccess_writing_refused() ) {
				$this->hold();

				return;
			}

			flush_rocket_htaccess();

			// Held either way: whether the write took or not, the next request would otherwise pay the
			// probes, the read of the file and the whole reason chain to reach the same answer.
			$this->hold();

			return;
		}

		if ( $enabled || ! $directives ) {
			$this->hold();

			return;
		}

		$writing_refused = $this->htaccess_writing_refused();

		global $is_apache;

		if ( $writing_refused || ! $is_apache ) {
			// A site that has asked the plugin not to write the file takes no rewrite of it; on a server
			// that never reads the file there are no rules of the plugin's in it to put back either.
			$section_gone = $this->maxcache->remove_directives();

			// Behind NGINX the block itself is there for this add-on's sake: with our section out of
			// it, nothing in it is read by anything, so the plugin takes the rest away as well.
			$block_gone = ! $writing_refused
				&& $this->maxcache->is_nginx()
				&& flush_rocket_htaccess( true );

			// That write announces itself, and one change of the file is one thing to tell the daemon.
			if ( $section_gone && ! $block_gone ) {
				$this->announce_written_file( $this->maxcache->htaccess_path() );
			}

			// Held either way. The verdict behind this write can differ between two admin requests —
			// a proxy fronting one and not the other — and without a hold the file flips on each.
			$this->hold();

			return;
		}

		flush_rocket_htaccess();

		// Asked of the file rather than of the return value.
		if ( ! $this->maxcache->has_directives() ) {
			$this->hold();

			return;
		}

		// Still there: on a block whose closing marker is gone the plugin's writer touches nothing at
		// all, while our own section is found by its own markers and can be taken out on its own.
		if ( $this->maxcache->remove_directives() ) {
			$this->announce_written_file( $this->maxcache->htaccess_path() );
		}

		$this->hold();
	}

	/**
	 * Shows, once, that delivery has moved to the server.
	 *
	 * @since 3.24
	 *
	 * @return void
	 */
	public function display_notice(): void {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( ! get_transient( 'rocket_maxcache_notice' ) ) {
			return;
		}

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( 'rocket_maxcache_notice', (array) $boxes, true ) ) {
			return;
		}

		// Read before the probes below, which cost sockets and a file: a switch that is off says all
		// this notice needs to know.
		if ( ! $this->maxcache->options()->get( 'maxcache', 0 ) ) {
			return;
		}

		// Asked of the file, and then of the add-on: the directives have to be in it and to still be
		// answering, or this would announce delivery that is not happening and be dismissed for good.
		if ( ! $this->maxcache->has_directives() || ! $this->maxcache->is_enabled() ) {
			return;
		}

		$redirect = admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG . '&rocket_source=notice_maxcache#addons' );
		$dismiss  = wp_nonce_url(
			admin_url( 'admin-post.php?action=rocket_ignore&box=rocket_maxcache_notice&redirect=' . rawurlencode( $redirect ) ),
			'rocket_ignore_rocket_maxcache_notice'
		);

		rocket_notice_html(
			[
				'message'                => __( 'MAx Cache is on: your web server is now set to deliver cached pages without loading PHP.', 'rocket' ),
				'action'                 => '<a class="button button-primary" href="' . esc_url( $dismiss ) . '">' . __( 'See it in the settings', 'rocket' ) . '</a>',
				'dismiss_button'         => 'rocket_maxcache_notice',
				'dismiss_button_class'   => 'button button-secondary',
				'dismiss_button_message' => __( 'Dismiss', 'rocket' ),
				'id'                     => 'rocket-maxcache-notice',
				// The native × forgets by the next page load; what this notice is gated on is the
				// rocket_boxes entry both controls above write, as every other box-gated notice does.
				'dismissible'            => '',
			]
		);
	}

	/**
	 * Tells the settings page why the add-on is not delivering, when it is not.
	 *
	 * @since 3.24
	 *
	 * @param string $status Value passed by the filter.
	 *
	 * @return string
	 */
	public function get_status( $status ): string {
		if ( ! $this->maxcache->options()->get( 'maxcache', 0 ) ) {
			return $this->maxcache->has_directives()
				? '<strong>' . esc_html__( 'Status: still delivering.', 'rocket' ) . '</strong> ' . $this->refused_write_reason()
				: (string) $status;
		}

		if ( ! $this->maxcache->is_enabled() ) {
			$reason = '<strong>' . esc_html__( 'Status: standing by.', 'rocket' ) . '</strong> '
				. esc_html( $this->maxcache->get_unsupported_reason() ) . ' ';

			// The directives are taken out as soon as this state is noticed, so finding them here
			// means that write was refused and the module is still the one answering.
			return $this->maxcache->has_directives()
				? $reason . $this->refused_write_reason()
				: $reason . esc_html__( 'WP Rocket is delivering the cache itself.', 'rocket' );
		}

		if ( ! $this->maxcache->has_directives() ) {
			if ( $this->htaccess_writing_refused() ) {
				// Nothing is wrong with the file: this site has asked the plugin not to write it,
				// and without those rules the server has nothing to serve from.
				return '<strong>' . esc_html__( 'Status: standing by.', 'rocket' ) . '</strong> '
					. esc_html__( 'This site does not let WP Rocket write the .htaccess file, and the rules go there.', 'rocket' ) . ' '
					. esc_html__( 'WP Rocket is delivering the cache itself.', 'rocket' );
			}

			return '<strong>' . esc_html__( 'Status: waiting.', 'rocket' ) . '</strong> '
				. esc_html__( 'The rules have not reached the .htaccess file yet. Check that the file is writable.', 'rocket' );
		}

		// Delivering, and on these sites not to everyone.
		if ( $this->maxcache->options()->get( 'cache_logged_user', 0 ) && ! $this->maxcache->serves_logged_in_users() ) {
			return '<strong>' . esc_html__( 'Status: delivering.', 'rocket' ) . '</strong> '
				. esc_html__( 'Logged-in visitors are served by WP Rocket itself: your settings give each of them cache files the server cannot name.', 'rocket' );
		}

		return (string) $status;
	}

	/**
	 * Says why the directives are still in the file, in the words of whatever refused the write.
	 *
	 * @since 3.24
	 *
	 * @return string
	 */
	private function refused_write_reason(): string {
		if ( $this->htaccess_writing_refused() ) {
			return esc_html__( 'The rules are still in the .htaccess file, and this site does not let WP Rocket write that file.', 'rocket' );
		}

		return esc_html__( 'The rules are still in the .htaccess file and could not be taken out. Check that the file is writable.', 'rocket' );
	}

	/**
	 * Tells the settings page whether the module is installed on this host.
	 *
	 * @since 3.24
	 *
	 * @param bool $available Value passed by the filter.
	 *
	 * @return bool
	 */
	public function is_module_available( $available ): bool {
		return (bool) $available || $this->maxcache->is_available();
	}

	/**
	 * Tells the plugin that the file is still needed on an NGINX host.
	 *
	 * @since 3.24
	 *
	 * @param bool $needed   Value passed by the filter.
	 * @param bool $removing Whether the caller is taking the plugin's rules out of the file.
	 *
	 * @return bool
	 */
	public function is_htaccess_needed( $needed, $removing = false ): bool {
		return $this->maxcache->is_htaccess_needed( (bool) $needed, (bool) $removing );
	}

	/**
	 * Carries what the form has to say about the switch through a save.
	 *
	 * @since 3.24
	 *
	 * @param array $fields Option names carried through the form as hidden fields.
	 *
	 * @return array
	 */
	public function carry_switch_state_through_the_form( $fields ): array {
		$fields = (array) $fields;

		// Where the switch is drawn, a marker rides along instead of the value: a form that comes back
		// without either was drawn before the switch existed, so its unchecked box is not an answer.
		$fields[] = $this->is_offered() ? 'maxcache_offered' : 'maxcache';

		return $fields;
	}

	/**
	 * Whether this site has told the plugin not to write the .htaccess file.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	private function htaccess_writing_refused(): bool {
		/** This filter is documented in inc/functions/htaccess.php */
		// Read as loosely as a typed read can be: that write tests the raw return, so a listener
		// answering with anything truthy stops it, and a narrower read here would disagree with it.
		return (bool) wpm_apply_filters_typed( '?boolean|?integer|?string|?array|?double', 'rocket_disable_htaccess', false );
	}

	/**
	 * Whether the settings page puts the switch in front of the user.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	private function is_offered(): bool {
		/** This filter is documented in inc/Engine/Admin/Settings/Page.php */
		return wpm_apply_filters_typed( 'boolean', 'rocket_maxcache_available', false )
			// The whole add-ons section is skipped while the licence is not valid.
			&& rocket_valid_key()
			&& $this->switch_is_displayed();
	}

	/**
	 * Whether the settings page draws the switch at all, as far as the hosts filtering it are asked.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	private function switch_is_displayed(): bool {
		/**
		 * This filter is documented in views/settings/fields/one-click-addon.php
		 */
		return (bool) wpm_apply_filters_typed( '?boolean|?integer|?string', 'rocket_display_input_maxcache', true );
	}

	/**
	 * Drops what was read of the settings once the row has been written.
	 *
	 * @since 3.24
	 *
	 * @return void
	 */
	public function forget_settings(): void {
		$this->maxcache->forget();
	}

	/**
	 * Sanitizes the option the way the plugin sanitizes every other one-click add-on.
	 *
	 * @since 3.24
	 *
	 * @param array $input Sanitized settings.
	 *
	 * @return array
	 */
	public function sanitize_option( $input ): array {
		$input = (array) $input;

		// Read from the row rather than from the object the container built at bootstrap.
		$settings = (array) $this->options_api->get( 'settings', [] );
		$stored   = $settings['maxcache'] ?? null;

		// Marker only, never stored: it says the form that sent this carried the switch.
		$offered_in_form = array_key_exists( 'maxcache_offered', $input );

		unset( $input['maxcache_offered'] );

		if ( ! isset( $input['maxcache'] ) && ! $this->is_settings_form() ) {
			// The plugin writing its own row: an upgrade, a licence refresh. Nothing is being
			// answered here, so carry whatever was decided before, if anything was.
			if ( null !== $stored ) {
				$input['maxcache'] = (int) $stored;
			}

			return $input;
		}

		if ( empty( $input['maxcache'] ) && $this->is_settings_form() && ! $offered_in_form ) {
			// A form that carried no switch is not answering for one: an absence, not a "no". A zero
			// from anywhere else is an answer and is recorded.
			if ( null === $stored ) {
				unset( $input['maxcache'] );
			} else {
				$input['maxcache'] = (int) $stored;
			}

			return $input;
		}

		$input['maxcache'] = empty( $input['maxcache'] ) ? 0 : 1;

		// Someone acting on this switch is not something to keep waiting behind the corrective check or
		// the automatic decision.
		if ( $input['maxcache'] !== (int) $stored ) {
			delete_transient( 'rocket_maxcache_restore_check' );
			delete_transient( 'rocket_maxcache_switch_on_check' );
		}

		return $input;
	}

	/**
	 * Whether the settings row is being written by a submission of the settings form.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	private function is_settings_form(): bool {
		// Read to tell one caller from another, not to act on the request: the submission itself is
		// verified by the Settings API before this filter runs.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		return isset( $_POST['option_page'] )
			&& (string) rocket_get_constant( 'WP_ROCKET_PLUGIN_SLUG', '' ) === sanitize_key( wp_unslash( $_POST['option_page'] ) );
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Takes this add-on's section out as the plugin goes away, where the plugin's own write did not.
	 *
	 * @since 3.24
	 *
	 * @return void
	 */
	public function remove_on_deactivation(): void {
		if ( ! $this->maxcache->has_directives() ) {
			return;
		}

		if ( $this->maxcache->remove_directives() ) {
			$this->announce_written_file( $this->maxcache->htaccess_path() );
		}
	}

	/**
	 * Takes note that the file has been written, and tells the daemon that reads it.
	 *
	 * @since 3.24
	 *
	 * @param string $filename Absolute path to the file.
	 * @param bool   $changed  Whether that call rewrote it.
	 *
	 * @return void
	 */
	public function after_flush( $filename, $changed = true ): void {
		// A file that was not rewritten has nothing to tell the daemon, and nothing this request read
		// of it has gone stale: the stamp read_htaccess() keeps would catch a change anyway.
		if ( ! $changed ) {
			return;
		}

		// What this request read of that file no longer describes it: the checks that follow the write
		// and the status line rendered later on the same page have to see what is there now.
		$this->maxcache->forget();

		// A site that has never answered this switch has never had anything of ours in the file, so
		// there is nothing for the daemon to recompile on its account.
		if ( ! $this->maxcache->decision_recorded() ) {
			return;
		}

		// Told for every write after that, without asking whose rules are in the file afterwards: the
		// write that takes ours out is the one the daemon most needs to hear about.
		$this->announce_written_file( $filename );
	}

	/**
	 * Names the file that has just been written to the daemon that reads it, and takes note of a
	 * notification it did not receive: the daemon goes on serving by the previous version of the file,
	 * so maybe_restore_rules() sends it again from the next admin request. NGINX only.
	 *
	 * @since 3.24
	 *
	 * @param string $filename Absolute path to the file.
	 *
	 * @return void
	 */
	private function announce_written_file( $filename ): void {
		if ( ! $this->maxcache->is_nginx() ) {
			return;
		}

		if ( '' === (string) $filename ) {
			return;
		}

		if ( $this->maxcache->notify_configd( dirname( (string) $filename ) ) ) {
			delete_transient( 'rocket_maxcache_notify_failed' );

			return;
		}

		// The daemon compiles this file into its own configuration, so one that was not reached goes on
		// serving by the previous version of it.
		set_transient( 'rocket_maxcache_notify_failed', 1, WEEK_IN_SECONDS );
	}
}
