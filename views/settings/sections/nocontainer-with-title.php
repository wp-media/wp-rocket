<?php
/**
 * Settings fields with no container template.
 *
 * @since 3.22
 *
 * @param array $data {
 *     Fields container data.
 *
 *     @type string $id          Section identifier.
 *     @type string $title       Section title.
 *     @type string $description Section description.
 *     @type string $class       Section classes.
 *     @type string $helper      Section helper text.
 *     @type string $help        Data to pass to beacon.
 *     @type string $page        Page section identifier.
 * }
 */

defined( 'ABSPATH' ) || exit;

$rocket_track_context = [
	'page_cdn'        => 'Content Delivery',
	'rocket_insights' => 'Rocket Insights',
][ $data['page'] ] ?? 'Settings';

?>


<div class="wpr-optionHeader <?php echo esc_attr( $data['class'] ); ?>">
	<h3 class="wpr-title2"><?php echo $data['title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?></h3>
	<?php
	if ( ! empty( $data['help'] ) ) {
		$rocket_cdn_help = ! empty( $data['help']['rocketcdn_url'] );
		$this->render_parts_with_data(
			'need-help',
			[
				'url'        => $data['help']['url'],
				'context'    => $rocket_track_context,
				'page'       => $data['page_title'],
				'section'    => $data['title'],
				'class'      => $rocket_cdn_help ? 'exclude-cdn-help-js' : '',
				'attributes' => $rocket_cdn_help ? [
					'data-rocketcdn-url' => $data['help']['rocketcdn_url'],
					'data-rocketcdn-id'  => $data['help']['rocketcdn_id'],
					'data-other-cdn-url' => $data['help']['other_cdn_url'],
					'data-other-cdn-id'  => $data['help']['other_cdn_id'],
				] : [],
			]
		);
	}
	?>
</div>

<?php $this->render_settings_fields( $data['page'], $data['id'] ); ?>