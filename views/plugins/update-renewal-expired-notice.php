<?php
/**
 * Plugins renewal notice template.
 *
 * @since 3.14
 *
 * $data array {
 *     Data to populate the template.
 *
 *     @type string $version Next major release version.
 *     @type string $renew_url Renewal URL.
 *     @type string $release_url Major release announcement URL.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>

<tr class="plugin-update-tr active" id="wp-rocket-update">
	<td class="plugin-update colspanchange" colspan="4">
		<div class="wp-rocket-update inline notice">
			<p>
			<?php
			printf(
				// translators: %1$s = <strong>, %2$s = plugin version, %3$s = </strong>, %4$s = <a>, %5$s = </a>, %6$s = <a>.
				esc_html__( ' %1$sWP Rocket %2$s%3$s is available. %4$sLearn more%5$s about the updates and enhancements of this major version. You need an active license to use them on your website, don’t miss out! %6$sRenew Now%5$s', 'rocket' ),
				'<strong>',
				esc_html( $data['version'] ),
				'</strong>',
				'<a href="' . esc_url( $data['release_url'] ) . '" rel="noopener" target="_blank">',
				'</a>',
				'<a href="' . esc_url( $data['renew_url'] ) . '" rel="noopener" target="_blank">'
			);
			?>
			</p>
		</div>
	</td>
</tr>
