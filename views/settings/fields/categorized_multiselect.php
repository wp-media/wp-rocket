<?php
namespace WP_Rocket\Views\Settings\Fields\CategorizedMultiselect;

use stdClass;/**
 * Select field template.
 *
 * @param array $data {
 *     Radio buttons  arguments.
 *
 *     @type string $id              Field identifier.
 *     @type string $label           Field label.
 *     @type string $container_class Field container class.
 *     @type string $value           Field value.
 *     @type string $description     Field description.
 *     @type array $items            Exclusion list.
 *     @type array $items            Exclusion list.
 *     @type array $wp_rocket_scripts Script exclusion list.
 *     @type array $wp_rocket_themes Theme exclusion list.
 *     @type array $wp_rocket_plugins Plugin exclusion list.
 *     @type array $wp_rocket_textarea Textarea exclusion list.
 *     @type array $wp_rocket_select_exclusions Selected exclusion list values.
 *     @type array $wp_rocket_state Currently exclusion list.
 *     @type array  $options {
 *          Option options.
 *
 *          @type string $description Option value.
 *          @type string $label Option label.
 *          @type array  $sub_fields fields to show when option is selected.
 *     }
 * }
 *@since 3.10
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render the list.
 *
 * @param string $title title from the list.
 * @param string $input_name name from the input.
 * @param array  $list list to render.
 * @param array  $state current state.
 * @param bool   $open is the list open.
 * @return void
 */
function render_list( string $title, string $input_name, array $list, array $state, bool $open = false ) {
	$has_selected = false;

	foreach ( $list as $id => $item ) {
		if ( in_array( $id, $state, true ) ) {
			$has_selected = true;
		}
	}
	?>
		<div class="wpr-list<?php echo $open ? ' open' : ''; ?>">
			<div class="wpr-list-header">
				<div class="wpr-checkbox">
					<input class="wpr-main-checkbox" type="checkbox" id="<?php echo esc_attr( $input_name ); ?>" name="<?php echo esc_attr( $input_name ); ?>"
											<?php
											echo $has_selected ? ' checked="checked"'
											: '';
											?>
							/>

					<label><span class="wpr-multiple-select-<?php echo esc_attr( $input_name ); ?>">
							<?php
								echo esc_attr( $title );
							?>
					</span></label>
				</div>
				<div class="wpr-list-header-arrow">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M233.4 105.4c12.5-12.5 32.8-12.5 45.3 0l192 192c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L256 173.3 86.6 342.6c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l192-192z"/></svg>
				</div>
			</div>
			<div class="wpr-list-body">
				<ul>
					<?php
					foreach ( $list as $id => $item ) {
						?>
						<li>
							<div class="wpr-checkbox">
								<input type="checkbox" name="<?php echo esc_attr( $id ); ?>"
									   value='<?php echo esc_attr( wp_json_encode( $item->exclusions ) ); ?>'
										<?php echo in_array( $id, $state, true ) ? ' checked="checked"' : ''; ?> />
								<label> <img src="<?php echo $item['icon']; ?>"/>
									<?php echo esc_attr( $item->title ); ?>
								</label>
							</div>
						</li>
					<?php
					}
					?>
				</ul>
			</div>
		</div>
	<?php
}

?>

<div id='<?php echo esc_attr( $data['id'] ); ?>' class="wpr-field wpr-multiple-select <?php echo esc_attr( $data['container_class'] ); ?>" data-default="<?php echo ( ! empty( $data['default'] ) ? 'wpr-radio-' . esc_attr( $data['default'] ) : '' ); ?>" <?php echo $data['parent']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $data['parent'] escaped with esc_attr. ?>>

	<h2><?php echo esc_html( __( 'Excluded JavaScript Files', 'rocket' ) ); ?></h2>
	<p>
	<?php
	echo esc_html( __( 'Add JavaScript files to be excluded from delaying execution by selected them / or by add in “My scripts”.', 'rocket' ) );
	?>
	</p>

	<?php
	foreach ( $data['items'] as $item_key => $item ) {
		render_list(
				esc_html( $item['title'] ),
				esc_attr( $data['id'] ) . '_' . $item_key,
				$item['items'],
				$data['selected'],
				'scripts' === $item_key
		);
	}
	?>

	<p><?php echo esc_html( __( 'Specify URLS or keywords in “My scripts”, which are not in the above categories.', 'rocket' ) ); ?></p>
	<div class="wpr-list open">
		<div class="wpr-list-header">
			<h3><?php echo esc_html( __( 'My scripts', 'rocket' ) ); ?></h3>
		</div>
		<div class="wpr-list-body wpr-textarea">
			<textarea name="wp_rocket_settings[<?php echo esc_attr( $data['id'] ); ?>]" placeholder="
				<?php
				echo esc_html( __( 'ex : /wp-includes/js/jquery/jquery.min.js', 'rocket' ) );
				?>
			"><?php echo esc_textarea( $data['wp_rocket_textarea'] ); ?></textarea>
		</div>
	</div>
</div>
