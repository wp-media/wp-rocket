<?php
/**
 * Deactivation intent form template.
 *
 * @since 3.0
 *
 * $data array {
 *     Data to populate the form.
 *
 *     @type string $safe_mode_url    URL to activate WP Rocket safe mode.
 *     @type string $deactivation_url URL to deactivate the plugin.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>
<div class="wpr-Modal">
	<div class="wpr-Modal-header">
		<div>
			<button class="wpr-Modal-return wpr-icon-chevron-left"><?php _e( 'Return', 'rocket' ); ?></button>
			<h2><?php _e( 'WP Rocket feedback', 'rocket' ); ?></h2>
		</div>
		<button class="wpr-Modal-close wpr-icon-close"><?php _e( 'Close', 'rocket' ); ?></button>
	</div>
	<div class="wpr-Modal-content">
		<div class="wpr-Modal-question wpr-isOpen">
			<h3><?php _e( 'May we have a little info about why you are deactivating?', 'rocket' ); ?></h3>
			<ul>
				<li>
					<input type="radio" name="reason" id="reason-temporary" value="Temporary Deactivation">
					<label for="reason-temporary"><?php _e( '<strong>It is a temporary deactivation.</strong> I am just debugging an issue.', 'rocket' ); ?></label>
				</li>
				<li>
					<input type="radio" name="reason" id="reason-broke" value="Broken Layout">
					<label for="reason-broke"><?php _e( 'The plugin <strong>broke my layout</strong> or some functionality.', 'rocket' ); ?></label>
				</li>
				<li>
					<input type="radio" name="reason" id="reason-score" value="Score">
					<label for="reason-score"><?php _e( 'My PageSpeed or GTMetrix <strong>score did not improve.</strong>', 'rocket' ); ?></label>
				</li>
				<li>
					<input type="radio" name="reason" id="reason-loading" value="Loading Time">
					<label for="reason-loading"><?php _e( 'I did not notice a difference in loading time.', 'rocket' ); ?></label>
				</li>
				<li>
					<input type="radio" name="reason" id="reason-complicated" value="Complicated">
					<label for="reason-complicated"><?php _e( 'The plugin is <strong>too complicated to configure.</strong>', 'rocket' ); ?></label>
				</li>
				<li>
					<input type="radio" name="reason" id="reason-host" value="Host">
					<label for="reason-host"><?php _e( 'My host already has its own caching system.', 'rocket' ); ?></label>
					<div class="wpr-Modal-fieldHidden">
						<input type="text" name="reason-hostname" id="reason-hostname" value="" placeholder="<?php _e( 'What is the name of your web host?', 'rocket' ); ?>">
					</div>
				</li>
				<li>
					<input type="radio" name="reason" id="reason-other" value="Other">
					<label for="reason-other"><?php _e( 'Other', 'rocket' ); ?></label>
					<div class="wpr-Modal-fieldHidden">
						<textarea name="reason-other-details" id="reason-other-details" placeholder="<?php _e( 'Let us know why you are deactivating WP Rocket so we can improve the plugin', 'rocket' ); ?>"></textarea>
					</div>
				</li>
			</ul>
			<input id="wpr-reason" type="hidden" value="">
			<input id="wpr-details" type="hidden" value="">
		</div>
		<div id="reason-broke-panel" class="wpr-Modal-hidden">
			<h3><?php _e( 'The plugin broke my layout or some functionality', 'rocket' ); ?></h3>
			<p><?php _e( 'This type of issue can usually be fixed by deactivating some options in WP Rocket.', 'rocket' ); ?></p>
			<p><?php _e( 'Click "Apply Safe Mode" to quickly disable LazyLoad, File Optimization, Embeds and CDN options. Then check your site to see if the issue has resolved.', 'rocket' ); ?></p>
			<div class="text-center">
				<button id="wpr-action-safe_mode" class="wpr-button"><?php _e( 'Apply safe mode', 'rocket' ); ?></button>
			</div>
			<div class="wpr-Modal-safeMode wpr-icon-check show-if-safe-mode">
				<div class="wpr-Modal-safeMode-title wpr-title3"><?php _e( 'Safe mode applied.', 'rocket' ); ?></div>
				<?php _e( 'Review your site in a private/logged out browser window.', 'rocket' ); ?>
			</div>
			<p class="show-if-safe-mode"><?php _e( 'Is the issue fixed? Now you can reactivate options one at a time to determine which one caused the problem. <a href="https://docs.wp-rocket.me/article/19-resolving-issues-with-file-optimization/?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank">More info</a>', 'rocket' ); ?></p>
		</div>
		<div id="reason-score-panel" class="wpr-Modal-hidden">
			<h3><?php _e( 'My PageSpeed or GT Metrix score did not improve', 'rocket' ); ?></h3>
			<p><?php _e( 'WP Rocket makes your site faster. The PageSpeed grade or GTMetrix score are not indicators of speed.  Neither your real visitors, nor Google will ever see your website’s “grade”. Speed is the only metric that matters for SEO and conversions.', 'rocket' ); ?></p>
			<p><?php _e( 'Yoast, the expert on all things related to SEO for WordPress states:', 'rocket' ); ?></p>
			<blockquote cite="https://yoast.com/ask-yoast-google-page-speed/"><?php _e( '[Google] just looks at how fast your website loads for users, so you don’t have to obsess over that specific score. You have to make sure your website is as fast as you can get it.', 'rocket' ); ?></blockquote>
			<cite><a href="https://yoast.com/ask-yoast-google-page-speed/" target="_blank">https://yoast.com/ask-yoast-google-page-speed/</a></cite>

			<p><?php _e( 'How to measure the load time of your site:<br><a href="https://wp-rocket.me/blog/correctly-measure-websites-page-load-time/?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank">https://wp-rocket.me/blog/correctly-measure-websites-page-load-time/</a>', 'rocket' ); ?></p>
			<p><?php _e( 'Why you should not be chasing a PageSpeed score:<br><a href="https://wp-rocket.me/blog/the-truth-about-google-pagespeed-insights/?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank">https://wp-rocket.me/blog/the-truth-about-google-pagespeed-insights/</a>', 'rocket' ); ?></p>
		</div>
		<div id="reason-loading-panel" class="wpr-Modal-hidden">
			<h3><?php _e( 'I did not notice a difference in loading time', 'rocket' ); ?></h3>
			<p><?php _e( 'Make sure you look at your site while logged out to see the fast, cached pages!', 'rocket' ); ?>
			<p><?php _e( 'The best way to see the improvement WP Rocket provides is to perform speed tests. Follow this guide to correctly measure the load time of your website:<br><a href="https://wp-rocket.me/blog/correctly-measure-websites-page-load-time/?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank">https://wp-rocket.me/blog/correctly-measure-websites-page-load-time/</a>', 'rocket' ); ?>
		</div>
		<div id="reason-complicated-panel" class="wpr-Modal-hidden">
			<h3><?php _e( 'The plugin is too complicated to configure', 'rocket' ); ?></h3>
			<p><?php _e( 'We are sorry to hear you are finding it difficult to use WP Rocket.', 'rocket' ); ?></p>
			<p><?php _e( 'WP Rocket is the only caching plugin that provides 80% of best practices in speed optimization, by default. That means you do not have to do anything besides activate WP Rocket and your site will already be faster!', 'rocket' ); ?></p>
			<p><?php _e( 'The additional options are not required for a fast site, they are for fine-tuning.', 'rocket' ); ?></p>
			<p><?php _e( 'To see the benefit WP Rocket is already providing, measure the speed of your site using a tool like Pingdom:<br><a href="https://wp-rocket.me/blog/correctly-measure-websites-page-load-time/?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank">https://wp-rocket.me/blog/correctly-measure-websites-page-load-time/</a>', 'rocket' ); ?></p>
		</div>
	</div>
	<div class="wpr-Modal-footer">
		<div>
			<a href="<?php echo esc_attr( $data['deactivation_url'] ); ?>" class="button button-primary wpr-isDisabled" disabled id="mixpanel-send-deactivation"><?php _e( 'Send & Deactivate', 'rocket' ); ?></a>
			<button class="wpr-Modal-cancel"><?php _e( 'Cancel', 'rocket' ); ?></button>
		</div>
		<a href="<?php echo esc_attr( $data['deactivation_url'] ); ?>" class="button button-secondary"><?php _e( 'Skip & Deactivate', 'rocket' ); ?></a>
	</div>
</div>
<div class="wpr-Modal-overlay"></div>
