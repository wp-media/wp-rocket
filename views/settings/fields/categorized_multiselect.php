<?php
/**
* Select field template.
*
* @since 3.10
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
*     @type array  $options {
*          Option options.
*
*          @type string $description Option value.
*          @type string $label Option label.
*          @type array  $sub_fields fields to show when option is selected.
*     }
* }
*/

defined( 'ABSPATH' ) || exit;
$items = key_exists('items', $data) ? $data['items'] : [];

$scripts = key_exists('scripts', $items) ? $items['scripts'] : [];

$themes = key_exists('themes', $items) ? $items['themes'] : [];

$plugins = key_exists('plugins', $items) ? $items['plugins'] : [];


function fetch_icon(array $item) {
	if( key_exists('icon_url', $item)) {
		return $item['icon_url'];
	}
	return esc_url( WP_ROCKET_ASSETS_IMG_URL . 'default-icon.png' );
}

function render_list_item(array $item) {
	?>
	<li>
		<div class="wpr-checkbox">
			<input type="checkbox" />
			<label> <img src="<?php echo fetch_icon($item); ?>"/>
			<?php echo $item['title']; ?>
			</label>
		</div>
	</li>
	<?php
}

function render_list(string $title, string $input_name, array $list, bool $open = false) {
	?>
		<div class="wpr-list<?php echo $open ? ' open' : ''; ?>">
			<div class="wpr-list-header">
				<div class="wpr-checkbox"><input type="checkbox" id="<?php echo $input_name; ?>" name="<?php echo $input_name; ?>"> <label><?php
						echo
						$title; ?></label></div>
				<div class="wpr-list-header-arrow">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M233.4 105.4c12.5-12.5 32.8-12.5 45.3 0l192 192c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L256 173.3 86.6 342.6c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l192-192z"/></svg>
				</div>
			</div>
			<div class="wpr-list-body">
				<ul>
					<?php
					foreach ($list as $item):
						render_list_item($item);
					endforeach;
					?>
				</ul>
			</div>
		</div>
	<?php
}

?>

<div id = '<?php echo esc_attr( $data['id'] ); ?>' class="wpr-field wpr-multiple-select <?php echo esc_attr( $data['container_class'] ); ?>" data-default="<?php echo ( ! empty( $data['default'] ) ? 'wpr-radio-' . esc_attr( $data['default'] ) : '' ); ?>" <?php echo $data['parent']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $data['parent'] escaped with esc_attr. ?>>

	<h2><?php echo __('Excluded JavaScript Files', 'rocket'); ?></h2>
	<p><?php echo __('Add JavaScript files to be excluded from delaying execution by selected them / or by add in “My scripts”.', 'rocket');
	?></p>

	<?php render_list( __('Analytics & Ads', 'rocket'), esc_attr( $data['id'] ) . '_' . 'ads', $scripts, true); ?>

	<?php render_list( __('Plugins', 'rocket'), esc_attr( $data['id'] ) . '_' . 'plugins', $themes, true); ?>

	<?php render_list( __('Themes', 'rocket'), esc_attr( $data['id'] ) . '_' . 'themes', $plugins, true); ?>

	<p><?php echo __('Specify URLS or keywords in “My scripts”, which are not in the above categories.', 'rocket') ?></p>
	<div class="wpr-list open">
		<div class="wpr-list-header">
			<h3><?php echo __('My scripts', 'rocket'); ?></h3>
		</div>
		<div class="wpr-list-body wpr-textarea">
			<textarea name="<?php echo esc_attr( $data['id'] ); ?>" placeholder="<?php echo __('ex : /wp-includes/js/jquery/jquery.min.js', 'rocket') ?>"></textarea>
		</div>
	</div>

	<input name="<?php echo esc_attr( $data['id'] ); ?>_selected" type="hidden"/>
	<input name="<?php echo esc_attr( $data['id'] ); ?>_selected_exclusions" type="hidden">
</div>
