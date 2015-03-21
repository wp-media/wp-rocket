<?php defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' ); ?>
<h1 class="hide-if-no-js"><?php _e( 'FAQ\'s Table of Content', 'rocket' ); ?></h1>
<ol class="hide-if-no-js">
	<li><h2><a href="#Q2"><?php _e( 'How many sites is my key valid for?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q3"><?php _e( 'What exactly does WP Rocket do?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q4"><?php _e( 'I haven\'t turned any of the basic options on, does WP Rocket work?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q5"><?php _e( 'What should I do in case of a problem with WP Rocket that I can\'t solve?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q6"><?php _e( 'My license has expired, what should I do?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q7"><?php _e( 'I want to change the URL of my website associated with my license, what should I do?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q8"><?php _e( 'Which tools should I use to measure the performance of my website?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q9"><?php _e( 'Does WP Rocket work with default permalinks?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q10"><?php _e( 'Which web servers WP Rocket is compatible with?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q11"><?php _e( 'Reports from PageSpeed and YSlow tell me that the content is not gziped and/or did not expire, what should I do?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q12"><?php _e( 'Is WP Rocket compatible with others cache plugins like WP Super Cache or W3 Total Cache?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q13"><?php _e( 'Is WP Rocket compatible with WP Touch, WordPress Mobile Pack and WP Mobile Detector?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q14"><?php _e( 'Is WP Rocket compatible with e-commerce plugins?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q15"><?php _e( 'Is WP Rocket compatible with WPML or qTranslate?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q16"><?php _e( 'What are the minification and concatenation of files?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q17"><?php _e( 'What should I do if WP Rocket distorts my website display?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q18"><?php _e( 'How often is the cache updated?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q19"><?php _e( 'How to exclude a particular page from caching?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q20"><?php _e( 'How do the robots preload files from the cache?', 'rocket' );?></a></h2></li>
</ol>
<hr>
<div id="insidefaq">
	<h2><span id="Q2">&#160;</span><?php _e( 'How many sites is my key valid for?', 'rocket' );?></h2>
	<p><?php _e( 'A key is valid for one domain name. If you have example.com, that makes one key.', 'rocket' );?></p>
	<p><?php _e( 'If you have example.com, www.example.com, dev.example.com, demo.example.com, example.com/dev/, example.com/demo/ all these sites depend on the same WP Rocket key.', 'rocket' );?></p>
	<p><?php _e( 'However, a different base domain name, like example.org or example2.com, would require an additional key each.', 'rocket' );?></p>

	<h2><span id="Q3">&#160;</span><?php _e( 'What exactly does WP Rocket do?', 'rocket' );?></h2>
	<p><?php _e( 'WP Rocket is a full cache plugin that comes with many features:', 'rocket' );?></p>
	<ul>
		<li><?php _e( 'Caching of all the pages for quick viewing', 'rocket' );?></li>
		<li><?php _e( 'Preloading the cache of files using two bots in Python', 'rocket' );?></li>
		<li><?php _e( 'Reduction of the number of HTTP requests to reduce loading time', 'rocket' );?></li>
		<li><?php _e( 'Decreasing bandwidth usage with GZIP compression', 'rocket' );?></li>
		<li><?php _e( 'Management of the headers (expires, etags...)', 'rocket' );?></li>
		<li><?php _e( 'Minification and concatenation of JS and CSS files', 'rocket' );?></li>
		<li><?php _e( 'Deferred loading of images (LazyLoad)', 'rocket' );?></li>
		<li><?php _e( 'Deferred loading of JavaScript files', 'rocket' );?></li>
		<li><?php _e( 'Image Optimisation', 'rocket' );?></li>
	</ul>

	<h2><span id="Q4">&#160;</span><?php _e( 'I have turned any of the basic options on, does WP Rocket work?', 'rocket' );?></h2>
	<p><?php _e( 'Yes.', 'rocket' );?></p>
	<p><?php _e( 'The basic options are additional optimizations that could be described as bonuses. These options are not essential to improve the loading time of your website.', 'rocket' );?></p>
	<p><?php _e( 'Whatever your WP Rocket configuration is, the following features will still be active:', 'rocket' );?></p>
	<ul>
		<li><?php _e( 'Caching of all the pages for quick viewing', 'rocket' );?></li>
		<li><?php _e( 'Decrease bandwidth usage with our GZIP compression', 'rocket' );?></li>
		<li><?php _e( 'Management of the headers (expires, etags...)', 'rocket' );?></li>
		<li><?php _e( 'Image Optimisation', 'rocket' );?></li>
	</ul>

	<h2><span id="Q5">&#160;</span><?php _e( 'What should I do in case of a problem with WP Rocket that I can\'t solve?', 'rocket' );?></h2>
	<p><?php echo sprintf( __( 'If none of the answers from our FAQ provide a solution to your problem, you can let us know about your problem on our <a href="%s" target="_blank">support</a>. We will answer you as soon as possible.', 'rocket' ), 'http://support.wp-rocket.me' );?></p>

	<h2><span id="Q6">&#160;</span><?php _e( 'My license has expired, what should I do?', 'rocket' );?></h2>
	<p><?php _e( 'Don\'t panic, WP Rocket will continue to operate without a problem. You will receive an email telling you that your license will soon expire. You will find a renewal link which will be active even after the expiration.', 'rocket' );?></p>

	<h2><span id="Q7">&#160;</span><?php _e( 'I want to change the URL of my site associated with my license, what should I do?', 'rocket' );?></h2>
	<p><?php echo sprintf( __( 'It\'s easy to transfer your license to another site. Follow the steps here: <a href="%1$s">%1$s</a>', 'rocket' ), 'http://docs.wp-rocket.me/article/28-transfering-your-license-to-another-site' ); ?></p>

	<h2><span id="Q8">&#160;</span><?php _e( 'Which tools should I use to measure the performance of my site?', 'rocket' );?></h2>
	<p><?php _e( 'You can measure the performance of your website using the following tools:', 'rocket' );?></p>
	<ul>
		<li><a href="http://tools.pingdom.com/fpt/" target="_blank">Pingdom Tools</a></li>
		<li><a href="http://gtmetrix.com/" target="_blank">GT Metrix</a></li>
		<li><a href="http://www.webpagetest.org/" target="_blank">Webpagetest</a></li>
		<li><a href="https://www.dareboost.com/en/home" target="_blank">Dareboost</a></li>
	</ul>
	<p><?php _e( 'These tools give you two indications:', 'rocket' );?></p>
	<ul>
		<li><?php _e( 'a global note of good practices to apply', 'rocket' );?></li>
		<li><?php _e( 'a loading time', 'rocket' );?></li>
	</ul>
	<p><?php _e( 'These data are indicative and do not necessarily reflect the actual display speed of your website', 'rocket' );?>.</p>
	<p><?php echo sprintf( __( 'For tests closest to reality load time we recommend using <a href="%s" target="_blank">Pingdom Tools</a>.', 'rocket' ), 'http://tools.pingdom.com/fpt/' );?></p>

	<h2><span id="Q9">&#160;</span><?php _e( 'Does WP Rocket work with default permalinks?', 'rocket' );?></h2>
	<p><?php _e( 'No.', 'rocket' );?></p>
	<p><?php echo sprintf( __( 'It\'s necessary to have custom permalinks of the type <code>%s</code> rather than <code>%s</code>.', 'rocket' ), 'http://example.com/mon-article/', 'http://example.com/?p=1234' );?></p>

	<h2><span id="Q10">&#160;</span><?php _e( 'Which web servers is WP Rocket compatible with?', 'rocket' );?></h2>
	<p><?php _e( 'WP Rocket is compatible with <strong>Apache, NGINX, Microsoft IIS et Litepseed</strong> web servers.', 'rocket' );?></p>

	<h2><span id="Q11">&#160;</span><?php _e( 'Reports from PageSpeed and YSlow tell me that the content is not gziped and/or did not expire, what should I do?', 'rocket' );?></h2>
	<p><?php echo sprintf( __( 'WP Rocket automatically adds optimal timeout rules for static files and compresses them using gzip. If they are not applied, it is possible that there is a plugin conflict (exemple: <a href="%s" target="_blank">WP Retina 2x</a>). Try temporarily disabling all plugins except WP Rocket and retest.', 'rocket' ), 'http://wordpress.org/plugins/wp-retina-2x/' );?></p>
	<p><?php _e( 'If this is not conclusive, it means that <code>mod_expire</code> and/or <code>mod_deflate</code> are not enabled on your server.', 'rocket' );?></p>

	<h2><span id="Q12">&#160;</span><?php _e( 'Is WP Rocket compatible with others cache plugins like WP Super Cache or W3 Total Cache?', 'rocket' );?></h2>
	<p><?php _e( 'No.', 'rocket' );?></p>
	<p><?php _e( 'It\'s imperative to <strong>remove all other optimization plugins</strong> (cache, minification, LazyLoad) PRIOR to the activation of WP Rocket.', 'rocket' );?></p>

	<h2><span id="Q13">&#160;</span><?php _e( 'Is WP Rocket compatible with WP Touch, WordPress Mobile Pack and WP Mobile Detector?', 'rocket' );?></h2>
	<p><?php _e( 'Yes.', 'rocket' );?></p>
	<p><?php _e( 'On the other hand, in the basic options, you must uncheck the <code>enable caching for mobile devices</code>.', 'rocket' );?></p>

	<h2><span id="Q14">&#160;</span><?php _e( 'Is WP Rocket compatible with e-commerce plugins?', 'rocket' );?></h2>
	<p>
		<?php echo wp_sprintf( __( 'Yes, WP Rocket automatically excludes "View Cart" and "Checkout" pages from the cache if you use one of these plugins: %l.', 'rocket' ), array( 'WooCommerce', 'Easy Digital Download', 'Jigoshop', 'iThemes Exchange', 'WP-Shop' ) );?>
		<br/><br/>
		<?php printf( __( 'If you don\'t use one of them, you must follow these instructions: <a href="%s">How To Use WP Rocket On Your Ecommerce Site (EN)</a>.', 'rocket' ), 'http://docs.wp-rocket.me/article/27-how-to-use-wp-rocket-on-your-ecommerce-site' );?>
	</p>

	<h2><span id="Q15">&#160;</span><?php _e( 'Is WP Rocket compatible with WPML or qTranslate?', 'rocket' );?></h2>
	<p><?php _e( 'Yes.', 'rocket' );?></p>
	<p><?php _e( 'You even have the possibility to empty/preload the cache for a specific language or for all languages at the same time.', 'rocket' );?></p>

	<h2><span id="Q16">&#160;</span><?php _e( 'What are the minification and concatenation of files?', 'rocket' );?></h2>
	<p><?php _e( 'Minification is the process of removing all unnecessary characters in HTML, CSS or JavaScript files: spaces, comments, etc... This reduces the size of the files. Thus, browsers read these files faster.', 'rocket' );?></p>
	<p><?php _e( 'Concatenation is to consolidate a set of files into one. This has the effect of reducing the number of HTTP requests.', 'rocket' );?></p>

	<h2><span id="Q17">&#160;</span><?php _e( 'What should I do if WP Rocket distorts my site display?', 'rocket' );?></h2>
	<p><?php echo sprintf( __( 'There is a good chance that the deformation is caused by the minification of HTML, CSS or JavaScript files. To resolve the problem, we recommend watching the following video: <a href="%1$s" class="fancybox">%1$s</a>.', 'rocket' ), 'http://www.youtube.com/embed/iziXSvZgxLk' );?></p>

	<h2><span id="Q18">&#160;</span><?php _e( 'How often is the cache updated?', 'rocket' );?></h2>
	<p><?php _e( 'The cache is automatically refreshed at every update of your content (add/edit/delete of an article, publication of a comment, etc..).', 'rocket' );?></p>
	<p><?php _e( 'In the basic options, you can also specify a period of automatic cleaning of the cache.', 'rocket' );?></p>

	<h2><span id="Q19">&#160;</span><?php _e( 'How to exclude a particular page from caching?', 'rocket' );?></h2>
	<p><?php _e( 'In advanced options, it\'s possible to specify URLs to not cache. For this, you must add the URLs to exclude in the input field <code>Never cache the following pages</code>.', 'rocket' );?></p>

	<h2><span id="Q20">&#160;</span><?php _e( 'How do the robots preload files from the cache?', 'rocket' );?></h2>
	<p><?php _e( 'A page is only cached on its first visit. To bypass this, we have developed two robots (in Python) that crawl the pages of your website.', 'rocket' );?></p>
	<p><?php _e( 'The first visits your site when using the button "Preload the cache". The second will automatically visit your site as soon as you are going to create/edit/delete an article.', 'rocket' );?></p>
	<p><?php echo sprintf( __( 'For more information, please watch this video: <a href="%s" class="fancybox">%s</a>.', 'rocket' ), 'http://www.youtube.com/embed/9jDcg2f-9yM', 'http://www.youtube.com/embed/9jDcg2f-9yM' );?></p>
</div>