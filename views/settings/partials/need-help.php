<?php
/**
 * "Need Help?" link of a settings section header.
 *
 * On pages with the support chat, clicking it sends the prompt to the chat; elsewhere it opens the help URL.
 *
 * @param array $data {
 *     Link data.
 *
 *     @type string|null $url        Help URL. Without one, a button is printed instead of a link.
 *     @type string      $context    Tracking context sent with the "Need Help" event.
 *     @type string      $page       Settings tab name, used in the chat prompt.
 *     @type string      $section    Settings section name, used in the chat prompt.
 *     @type string      $class      Optional. Extra CSS classes.
 *     @type array       $attributes Optional. Extra attributes, as name => value.
 * }
 */

defined( 'ABSPATH' ) || exit;

$rocket_need_help_url     = $data['url'] ?? null;
$rocket_need_help_tag     = null === $rocket_need_help_url ? 'button' : 'a';
$rocket_need_help_classes = trim( 'wpr-infoAction wpr-infoAction--help wpr-icon-help ' . ( $data['class'] ?? '' ) );
$rocket_need_help_prompt  = sprintf(
	// translators: 1: settings tab name, 2: settings section name.
	__( 'I need help with \'%1$s\' > \'%2$s\'', 'rocket' ),
	$data['page'] ?? '',
	$data['section'] ?? ''
);
?>
<<?php echo $rocket_need_help_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed tag name. ?>
<?php if ( 'a' === $rocket_need_help_tag ) : ?>
	href="<?php echo esc_url( $rocket_need_help_url ); ?>"
	target="_blank"
	rel="noopener noreferrer"
<?php endif; ?>
<?php foreach ( $data['attributes'] ?? [] as $rocket_need_help_name => $rocket_need_help_value ) : ?>
	<?php echo esc_attr( $rocket_need_help_name ); ?>="<?php echo esc_attr( $rocket_need_help_value ); ?>"
<?php endforeach; ?>
	data-wap-prompt="<?php echo esc_attr( $rocket_need_help_prompt ); ?>"
	data-wpr_track_button="Need Help"
	data-wpr_track_context="<?php echo esc_attr( $data['context'] ?? 'Settings' ); ?>"
	class="<?php echo esc_attr( $rocket_need_help_classes ); ?>"><?php esc_html_e( 'Need Help?', 'rocket' ); ?></<?php echo $rocket_need_help_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed tag name. ?>>
