<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Used to display the repeater field on settings form
 *
 * @since 2.2
 */
class WP_Rocket_Repeater_Field {

	/**
	 * Name of the option to save for fields
	 *
	 * @access private
	 * @var string
	 */
	private $option;

	/**
	 * Label screen print in <legend>
	 *
	 * @access private
	 * @var string
	 */
	private $label_screen;

	/**
	 * Value of the placeholder attribute on the fields
	 *
	 * @access private
	 * @var string
	 */
	private $placeholder;

	/**
	 * Determines whether fields can be ordered via drag'n drop
	 *
	 * @access private
	 * @var bool
	 */
	private $is_drag_n_drop;

	/**
	 * Label of the button to add a new field
	 *
	 * @access private
	 * @var string
	 */
	private $label_add_field;

	/**
	 * Constructor.
	 *
	 * @param array $args An associative array with information about the repeater field.
	 * @access public
	 */
	function __construct( $args ) {

		$this->option          = $args['name'];
		$this->label_screen    = ! empty( $args['label_screen'] ) ? esc_html( $args['label_screen'] ) : false;
		$this->placeholder     = ! empty( $args['placeholder'] ) ? 'placeholder="' . $args['placeholder'] . '" ' : '';
		$this->is_drag_n_drop  = ! empty( $args['repeater_drag_n_drop'] ) ? true : false;
		$this->label_add_field = ! empty( $args['repeater_label_add_field'] ) ? $args['repeater_label_add_field'] : false;

		add_filter( 'rocket_repeater_field_classes', array( $this, 'add_drag_n_drop_classes' ) );
		add_action( 'before_rocket_repeater_field', array( $this, 'add_drag_n_drop_support' ) );

	}

	/**
	 * Add Drag'n Drop support
	 *
	 * @since 2.2
	 * @access public
	 */
	public function add_drag_n_drop_support() {

		if ( $this->is_drag_n_drop ) { ?>

			<span class="dashicons dashicons-sort rkt-module-move hide-if-no-js"></span>

		<?php
		}

	}

	/**
	 * Add classes for Drag'n Drop support
	 *
	 * @since 2.2
	 * @access public
	 *
	 * @param string $classes All classes in rocket_repeater_field_classes filter.
	 */
	public function add_drag_n_drop_classes( $classes ) {

		$classes .= $this->is_drag_n_drop ? ' rkt-module-drag' : '';
		return $classes;

	}

	/**
	 * Get the label screen
	 *
	 * @since 2.2
	 * @access private
	 */
	private function the_label_screen() {

		if ( $this->label_screen ) {
			echo '<legend class="screen-reader-text"><span>' . $this->label_screen . '</span></legend>';
		}

	}

	/**
	 * Get the field tags
	 *
	 * @since 2.2
	 * @access private
	 *
	 * @param string $key The key of the field.
	 * @param string $value The value of the field.
	 * @param bool   $remove_button If true, remove button is display.
	 */
	private function the_field( $key = null, $value = '', $remove_button = true ) {
	?>

		<p class="<?php echo apply_filters( 'rocket_repeater_field_classes', false ); ?>">

			<?php

			/**
			 * Fires before print the input tag of the field
			 *
			 * @since 2.2
			 * @param string $option The option name
			 */
			do_action( 'before_rocket_repeater_field', $this->option );

			?>

			<input style="width: 32em" type="text" <?php echo $this->placeholder; ?> class="<?php echo $this->option; ?> regular-text" name="wp_rocket_settings[<?php echo $this->option; ?>][<?php echo $key; ?>]" value="<?php echo $value; ?>" />

			<?php if ( $remove_button ) { ?>
				<span class="dashicons dashicons-no rkt-module-remove hide-if-no-js "></span>
			<?php } ?>

			<?php

			/**
			 * Fires after print the input tag of the field
			 *
			 * @since 2.2
			 * @param string $option The option name
			 */
			do_action( 'after_rocket_repeater_field', $this->option );

			?>

		</p>

	<?php
	}

	/**
	 * Display the repeater field.
	 *
	 * @since 2.2
	 * @access public
	 */
	public function render() {
	?>

		<fieldset>

			<?php $this->the_label_screen(); ?>

			<div id="rkt-drop-<?php echo $this->option; ?>" class="rkt-module rkt-module-drop">

				<?php

				$_option = get_rocket_option( $this->option );

				if ( $_option ) {

					foreach ( $_option as $key => $value ) {
						$this->the_field( $key, $value, true );
					}

					// If no values yet, use this template inside.
				} else {
					$this->the_field( 0, '', false );
				}
				?>

			</div>

			<?php // Clone Template. ?>
			<div class="rkt-module-model hide-if-js">
				<?php $this->the_field(); ?>
			</div>

			<p><a href="javascript:void(0)" class="rkt-module-clone hide-if-no-js button-secondary"><?php echo $this->label_add_field; ?></a></p>

		</fieldset>
	<?php

	}

}
