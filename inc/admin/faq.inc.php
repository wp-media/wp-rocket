<?php defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' ); ?>
<h1 class="hide-if-no-js"><?php _e( 'FAQ\'s Table of Content', 'rocket' ); ?></h1>
<ol class="hide-if-no-js">
	<li><h2><a href="#Q1"><?php _e( 'My key does not work', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q2"><?php _e( 'For how many websites my key is valid for?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q3"><?php _e( 'What exactly does WP Rocket do?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q4"><?php _e( 'I have turned any of the basic options on, does WP Rocket work?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q5"><?php _e( 'What should I do in case of a problem with WP Rocket that I can\'t solve?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q6"><?php _e( 'My license has expired, what should I do?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q7"><?php _e( 'I want to change the URL of my website associated with my license, what should I do?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q8"><?php _e( 'What tools should I use to measure the performance of my website?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q9"><?php _e( 'Does WP Rocket work with default permalinks?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q10"><?php _e( 'Which web servers WP Rocket is compatible with?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q11"><?php _e( 'Reports from PageSpeed and YSlow tells me that the content is not gziped and/or did not expire, what should I do?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q12"><?php _e( 'Is WP Rocket compatible with others cache plugins like WP Super Cache or W3 Total Cache?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q13"><?php _e( 'Is WP Rocket compatible with WP Touch, WordPress Mobile Pack and WP Mobile Detector?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q14"><?php _e( 'Is WP Rocket compatible with WooCommerce?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q15"><?php _e( 'Is WP Rocket compatible with WPML or qTranslate?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q16"><?php _e( 'What is the minification and concatenation of files?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q17"><?php _e( 'What should I do if WP Rocket distorts my website display?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q18"><?php _e( 'How often the cache is updated?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q19"><?php _e( 'How not to cache a particular page?', 'rocket' );?></a></h2></li>
	<li><h2><a href="#Q20"><?php _e( 'How does the robots of preloading files cache work?', 'rocket' );?></a></h2></li>
</ol>
<hr>
<div id="insidefaq">
	<h2><span id="Q1">&#160;</span><?php _e( 'My key does not work', 'rocket' );?></h2>
	<p><?php _e( 'Check the spelling of your website in your profile on the WP Rocket\'s site.', 'rocket' );?></p>
	<p><?php _e( 'If the spelling is correct, check if the entered website is the same as the one in the WordPress options and not a redirection.', 'rocket' );?></p>

	<h2><span id="Q2">&#160;</span><?php _e( 'For how many sites my key is valid for?', 'rocket' );?></h2>
	<p><?php _e( 'A key is valid for 1 domain name. If you have example.com, that makes 1 key.', 'rocket' );?></p>
	<p><?php _e( 'If you have example.com, www.example.com, dev.example.com, demo.example.com, example.com/dev/, example.com/demo/ all these sites depend on the same WP Rocket key.', 'rocket' );?></p>
	<p><?php _e( 'You will need an additional key for each other different example.com domain name', 'rocket' );?></p>

	<h2><span id="Q3">&#160;</span><?php _e( 'What exactly does WP Rocket?', 'rocket' );?></h2>
	<p><?php _e( 'WP Rocket is a full cache plugin that comes with many features:', 'rocket' );?></p>
	<ul>
		<li><?php _e( 'Caching of all the pages for quick viewing', 'rocket' );?></li>
		<li><?php _e( 'Preloading the cache of files using 2 bots in Python', 'rocket' );?></li>
		<li><?php _e( 'Reduction of numbers of HTTP requests to reduce the load time', 'rocket' );?></li>
		<li><?php _e( 'Decrease of the bandwidth with GZIP compression', 'rocket' );?></li>
		<li><?php _e( 'Management of the headers (expires, etags...)', 'rocket' );?></li>
		<li><?php _e( 'Minification and concatenation of the JS and CSS files', 'rocket' );?></li>
		<li><?php _e( 'Loading delay of images (LazyLoad)', 'rocket' );?></li>
		<li><?php _e( 'Loading deferred of JavaScript files', 'rocket' );?></li>
		<li><?php _e( 'Images Optimisation', 'rocket' );?></li>
	</ul>

	<h2><span id="Q4">&#160;</span><?php _e( 'I have turned any of the basic options on, does WP Rocket work?', 'rocket' );?></h2>
	<p><?php _e( 'Yes.', 'rocket' );?></p>
	<p><?php _e( 'The basic options are additional optimizations that could be described as bonuses. These options are not essential to improve the loading time of your website.', 'rocket' );?></p>
	<p><?php _e( 'Whatever is your WP Rocket configuration, the following features will be still active:', 'rocket' );?></p>
	<ul>
		<li><?php _e( 'Caching of all the pages for quick viewing', 'rocket' );?></li>
		<li><?php _e( 'Decrease of the bandwidth with our GZIP compression', 'rocket' );?></li>
		<li><?php _e( 'Management of the headers (expires, etags...)', 'rocket' );?></li>
		<li><?php _e( 'Images Optimisation', 'rocket' );?></li>
	</ul>

	<h2><span id="Q5">&#160;</span><?php _e( 'What should I do in case of problem with WP Rocket that I can\'t solve?', 'rocket' );?></h2>
	<p><?php _e( 'If none of the answers from our FAQ give an answer to your problem, you can let us know about your problem on our <a href="http://support.wp-rocket.me" target="_blank"> support</a>. We will answer you as soon as possible.', 'rocket' );?></p>

	<h2><span id="Q6">&#160;</span><?php _e( 'My license has expired, what should I do?', 'rocket' );?></h2>
	<p><?php _e( 'Don\'t panic, WP Rocket will continue to operate without a problem. You will receive an email telling you that your license will soon expire. You will find a renewal link which will be active even after the expiration.', 'rocket' );?></p>

	<h2><span id="Q7">&#160;</span><?php _e( 'I want to change the URL of my site associated with my license, what should I do?', 'rocket' );?></h2>
	<p><?php _e( 'You must contact us by email (<a href="mailto:contact@wp-rocket.me">contact@wp-rocket.me</a>) indicating the reason for your change. If accepted, the amendment will be carried out by the WP Rocket team.', 'rocket' );?></p>

	<h2><span id="Q8">&#160;</span><?php _e( 'What tools should I use to measure the performance of my site?', 'rocket' );?></h2>
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
	<p><?php _e( 'For tests closest to reality load time we recommend using <a href="http://tools.pingdom.com/fpt/" target="_blank">Pingdom Tools</a>.', 'rocket' );?></p>

	<h2><span id="Q9">&#160;</span><?php _e( 'WP Rocket work with default permalinks?', 'rocket' );?></h2>
	<p><?php _e( 'No.', 'rocket' );?></p>
	<p><?php _e( 'It\'s necessary to have custom permalinks of the type <code>http://example.com/mon-article/</code> rather than <code>http://example.com/?p=1234</code>.', 'rocket' );?></p>

	<h2><span id="Q10">&#160;</span><?php _e( 'Which web servers WP Rocket is compatible with?', 'rocket' );?></h2>
	<p><?php _e( 'WP Rocket is compatible with <strong>Apache, NGINX, Microsoft IIS et Litepseed</strong> web servers.', 'rocket' );?></p>

	<h2><span id="Q11">&#160;</span><?php _e( 'Reports from PageSpeed and YSlow tells me that the content is not gziped and/or did not expire, what should I do?', 'rocket' );?></h2>
	<p><?php _e( 'WP Rocket automatically adds good rules timeouts and gzip for static files. If they are not applied, it is possible that there is a plugin conflict (exemple: <a href="http://wordpress.org/plugins/wp-retina-2x/" target="_blank">WP Retina 2x</a>). Try temporarily disabling all plugins except WP Rocket and retest.', 'rocket' );?></p>
	<p><?php _e( 'If this is not conclusive, this means that the <code>mod_expire</code> and/or <code>mod_deflate</code> is not enabled on your server.', 'rocket' );?></p>

	<h2><span id="Q12">&#160;</span><?php _e( 'Is WP Rocket compatible with others cache plugins like WP Super Cache or W3 Total Cache?', 'rocket' );?></h2>
	<p><?php _e( 'No.', 'rocket' );?></p>
	<p><?php _e( 'It\'s imperative to <strong>remove all other optimization plugins</strong> (cache, minification, LazyLoad) PRIOR to the activation of WP Rocket.', 'rocket' );?></p>

	<h2><span id="Q13">&#160;</span><?php _e( 'Is WP Rocket compatible with WP Touch, WordPress Mobile Pack and WP Mobile Detector?', 'rocket' );?></h2>
	<p><?php _e( 'Yes.', 'rocket' );?></p>
	<p><?php _e( 'On the other hand, in the basic options, you must uncheck the <code>enable caching for mobile devices</code>.', 'rocket' );?></p>

	<h2><span id="Q14">&#160;</span><?php _e( 'Is WP Rocket compatible with WooCommerce?', 'rocket' );?></h2>
	<p><?php _e( 'Yes.', 'rocket' );?></p>
	<p><?php _e( 'However, you should exclude cart and checkout pages from caching. This is done from the advanced option <code>Never to cache pages</code> by adding the following values:', 'rocket' );?></p>
	<p><code><?php _e( '/cart/', 'rocket' ); ?></code></p>
	<p><code><?php _e( '/checkout/(.*)', 'rocket' );?></code></p>

	<h2><span id="Q15">&#160;</span><?php _e( 'Is WP Rocket compatible with WPML or qTranslate?', 'rocket' );?></h2>
	<p><?php _e( 'Yes.', 'rocket' );?></p>
	<p><?php _e( 'You have even the possibility of empty/preload the cache for a specific language or for all languages at the same time.', 'rocket' );?></p>

	<h2><span id="Q16">&#160;</span><?php _e( 'What is the minification and concatenation of files?', 'rocket' );?></h2>
	<p><?php _e( 'The minification is the process to remove all unnecessary items in a HTML, CSS or JavaScript file: spaces, comments, etc... This allows to reduce the size of the files. Thus, browsers read files faster.', 'rocket' );?></p>
	<p><?php _e( 'The concatenation is to consolidate into one, a set of files. This has the effect of reducing the number of HTTP requests.', 'rocket' );?></p>

	<h2><span id="Q17">&#160;</span><?php _e( 'What should I do if WP Rocket distorts my site display?', 'rocket' );?></h2>
	<p><?php _e( 'There are good chances that the deformation is caused by the minification of HTML, CSS or JavaScript files. To resolve the problem, we recommend watching the following video: <a href="http://www.youtube.com/embed/iziXSvZgxLk" class="fancybox">http://www.youtube.com/embed/iziXSvZgxLk</a>.', 'rocket' );?></p>

	<h2><span id="Q18">&#160;</span><?php _e( 'How often the cache is updated?', 'rocket' );?></h2>
	<p><?php _e( 'The cache is automatically refreshed at every update of your content (add/edit/delete of an article, publication of a comment, etc..).', 'rocket' );?></p>
	<p><?php _e( 'In the basic options, you can also specify a period of automatic cleaning of the cache.', 'rocket' );?></p>

	<h2><span id="Q19">&#160;</span><?php _e( 'How not to cache a particular page?', 'rocket' );?></h2>
	<p><?php _e( 'In advanced options, it\'s possible to specify URLs to not cache. For this, you must add in the input field <code>Should never be cached pages</code> the URLs to exclude.', 'rocket' );?></p>

	<h2><span id="Q20">&#160;</span><?php _e( 'How does work the robots of preloading files cache?', 'rocket' );?></h2>
	<p><?php _e( 'To cache a page, it must be a first visitor. To avoid that a first visitor to do so, we have developed two robots (in python) that crawl the pages of your website.', 'rocket' );?></p>
	<p><?php _e( 'The first visits your site when using the button "Preload the cache". The second will automatically visit your site as soon as you are going to create/edit/delete an article.', 'rocket' );?></p>
	<p><?php _e( 'For more information, please watch this video: <a href="http://www.youtube.com/embed/9jDcg2f-9yM" class="fancybox">http://www.youtube.com/embed/9jDcg2f-9yM</a>.', 'rocket' );?></p>
</div>