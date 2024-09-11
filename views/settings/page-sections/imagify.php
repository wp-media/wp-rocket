<?php
/**
 * Imagify section template.
 *
 * @since 3.2
 */

defined( 'ABSPATH' ) || exit;

?>

<div id="imagify" class="wpr-Page">
	<div class="wpr-sectionHeader">
		<h2 class="wpr-title1 wpr-icon-imagify-hover"><?php esc_html_e( 'Image Optimization', 'rocket' ); ?></h2>
	</div>
	<pre>
</pre>
	<div class="wpr-mt-2">
		<h4 class="wpr-title2">
			<?php
			// Translators: %1$s = <strong>, %2$s = </strong>.
			printf( esc_html__( '%1$sWP Rocket created IMAGIFY to give your website an extra speed boost!%2$s', 'rocket' ), '<strong>', '</strong>' );
			?>
		</h4>
	</div>
	
	<p class="wpr-fs-md">
		<?php esc_html_e( 'Images can account for 50% of your loading time!', 'rocket' ); ?>
	</p>
	<p class="wpr-fs-md">
		<?php esc_html_e( 'Imagify automatically optimizes all your images, helping your website gain precious seconds while saving you time. With just one click, it resizes, compresses, and converts your images to WebP and AVIF formats without sacrificing quality.', 'rocket' ); ?>
	</p>

	<div class="wpr-imagify">
		<div class="wpr-imagify-description">
			<ul>
				<li class="wpr-imagify-info">
					<span class="text">
						<?php
						// Translators: %1$s = <strong>, %2$s = </strong>.
						printf( esc_html__( '%1$sCompress%2$s all your images in one click', 'rocket' ), '<strong>', '</strong>' );
						?>
					</span>
				</li>
				<li class="wpr-imagify-info">
					<span class="text">
						<?php
						// Translators: %1$s = <strong>, %2$s = </strong>.
						printf( esc_html__( '%1$sConvert%2$s images to WebP and Avif', 'rocket' ), '<strong>', '</strong>' );
						?>
					</span>
				</li>
				<li class="wpr-imagify-info">
					<span class="text">
						<?php
						// Translators: %1$s = <strong>, %2$s = </strong>.
						printf( esc_html__( '%1$sResize%2$s your images on the fly', 'rocket' ), '<strong>', '</strong>' );
						?>
					</span>
				</li>
				<li class="wpr-imagify-info">
					<span class="text">
						<?php
						// Translators: %1$s = <strong>, %2$s = </strong>.
						printf( esc_html__( '%1$sFree plan%2$s includes 20MB/month (around 200 images)', 'rocket' ), '<strong>', '</strong>' );
						?>
					</span>
				</li>
			</ul>

		</div>
		<div class="wpr-imagify-screenshot">
			<img src="<?php echo esc_attr( WP_ROCKET_ASSETS_IMG_URL . 'imagify-score.png' ); ?>" alt="" width="613" height="394">
		</div>
	</div>
	<?php if ( $data ) : ?>
		<div class="wpr-imagify-plugin-tile">
			<img src="<?php echo esc_url( $data->icons['svg'] ); ?>" alt="Imagify logo" width="65" height="65"> 
			<div class="wpr-imagify-plugin-tile-info">
				<h4 class="wpr-imagify-plugin-tile-title">
					<?php
					// Translators: %1$s = <strong>, %2$s = </strong>.
					printf( esc_html__( '%1$sInstall Imagify, the Easiest WordPress Image Optimizer%2$s', 'rocket' ), '<strong>', '</strong>' );
					?>
				</h4>
				<div class="wpr-star-rating">
					<?php
						wp_star_rating(
							[
								'rating' => ( $data->rating / 100 ) * 5,
								'type'   => 'rating',
								'number' => $data->num_ratings,
							]
							);
					?>
					<div class="num-ratings" aria-hidden="true">(<?php echo esc_html( number_format_i18n( $data->num_ratings ) ); ?>)</div>					
				</div>
				<div class="wpr-fs-sm">
				<?php echo esc_html( number_format_i18n( $data->active_installs ) ); ?>+ Active installations
				</div>
			</div>
			<?php
			if ( ! \Imagify_Partner::is_imagify_activated() ) {
				$imagify = new \Imagify_Partner( 'wp-rocket' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

				if ( \Imagify_Partner::is_imagify_installed() ) {
					$button_text = __( 'Activate Imagify', 'rocket' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
					$button_icon = 'wpr-icon-chevron-down'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				} else {
					$button_text = __( 'Install Imagify', 'rocket' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
					$button_icon = 'wpr-imagify-install'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				}

				echo '<a class="wpr-button ' . esc_attr( $button_icon ) . '" href="' . esc_url( $imagify->get_post_install_url() ) . '">' . esc_html( $button_text ) . '</a>';
			}
			?>
		</div>
	<?php endif; ?>
</div>
