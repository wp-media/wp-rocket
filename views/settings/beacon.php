<?php
/**
 * Beacon JS template.
 *
 * @since 3.6
 *
 * @param array $data {
 *      @type string $form_id  Beacon form ID.
 *      @type string $identify Identify data to send to Helpscout.
 *      @type string $session  Session data to send to Helpscout.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>
<script type="text/javascript">!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});</script>
<script type="text/javascript">window.Beacon('init', '<?php echo esc_js( $data['form_id'] ); ?>')</script>
<script>window.Beacon("identify", <?php echo $data['identify']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>);</script>
<script>window.Beacon("session-data", <?php echo $data['session']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>);</script>
<script>window.addEventListener("hashchange", function () {
	window.Beacon("suggest");
}, false);</script>
