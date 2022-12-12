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
 * @since 3.10
 */

defined( 'ABSPATH' ) || exit;
?>

<div id='<?php echo esc_attr( $data['id'] ); ?>' class="wpr-field wpr-multiple-select <?php echo esc_attr( $data['container_class'] ); ?>" data-default="<?php echo ( ! empty( $data['default'] ) ? 'wpr-radio-' . esc_attr( $data['default'] ) : '' ); ?>" <?php echo $data['parent']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $data['parent'] escaped with esc_attr. ?>>
	<h2><?php echo esc_html( __( 'Excluded JavaScript Files', 'rocket' ) ); ?></h2>
	<p>
	<?php
	echo esc_html( __( 'Add JavaScript files to be excluded from delaying execution by selected them / or by add in “My scripts”.', 'rocket' ) );
	?>
	</p>

	<?php
	foreach ( $data['items'] as $rocket_item_key => $rocket_item ) {
		if ( empty( $rocket_item['items'] ) ) {
			continue;
		}
		?>
		<div class="wpr-list<?php echo 'scripts' === $rocket_item_key ? ' open' : ''; ?>">
			<div class="wpr-list-header">
				<div class="wpr-checkbox">
					<input class="wpr-main-checkbox" type="checkbox" />

					<label>
						<span class="wpr-multiple-select">
							<?php echo esc_html( $rocket_item['title'] ); ?>
						</span>
					</label>
				</div>
				<div class="wpr-list-header-arrow">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M233.4 105.4c12.5-12.5 32.8-12.5 45.3 0l192 192c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L256 173.3 86.6 342.6c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l192-192z"/></svg>
				</div>
			</div>
			<div class="wpr-list-body">
				<ul>
					<?php
					foreach ( $rocket_item['items'] as $rocket_oneitem ) {
						?>
						<li>
							<div class="wpr-checkbox">
								<input type="checkbox" name="wp_rocket_settings[<?php echo esc_attr( $data['id'] ); ?>][]"
									value='<?php echo esc_attr( $rocket_oneitem['id'] ); ?>'
									<?php echo checked( in_array( $rocket_oneitem['id'], $data['selected'], true ) ); ?> />
								<label>
									<img src="<?php echo esc_url( $rocket_oneitem['icon'] ); ?>"/>
									<?php echo esc_attr( $rocket_oneitem['title'] ); ?>
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
	unset( $rocket_item_key, $rocket_item, $rocket_oneitem );
	?>
</div>
