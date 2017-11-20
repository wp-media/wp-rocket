# Change Log

## [v2.10.9](https://github.com/wp-media/wp-rocket/tree/v2.10.9) (2017-09-20)
## [v.2.10.8](https://github.com/wp-media/wp-rocket/tree/v.2.10.8) (2017-09-19)
**Implemented enhancements:**

- Add ?usqp to list of query strings that can receive the default cache [\#501](https://github.com/wp-media/wp-rocket/issues/501)
- Change user-agent sent by preload requests [\#494](https://github.com/wp-media/wp-rocket/issues/494)

**Fixed bugs:**

- Too few arguments for sprintf\(\) [\#518](https://github.com/wp-media/wp-rocket/issues/518)
- Conflict between Elementor and Combine Google Fonts  [\#514](https://github.com/wp-media/wp-rocket/issues/514)
- Exclude WooCommerce shipping class URLs from purge [\#513](https://github.com/wp-media/wp-rocket/issues/513)
- Custom Polylang flags [\#510](https://github.com/wp-media/wp-rocket/issues/510)
- Incorrect parsing when the subdomain URL is like https://subdomain-www.example.com [\#493](https://github.com/wp-media/wp-rocket/issues/493)

## [v2.10.7](https://github.com/wp-media/wp-rocket/tree/v2.10.7) (2017-08-02)
**Implemented enhancements:**

- Footprint appearing for non-HTML pages in Gzipped cached file [\#483](https://github.com/wp-media/wp-rocket/issues/483)
- WP offload S3 auto-detection [\#443](https://github.com/wp-media/wp-rocket/issues/443)
- Cache doesn't clear when ACF Options page is updated [\#404](https://github.com/wp-media/wp-rocket/issues/404)
- Apply CDN URL on internal images links [\#369](https://github.com/wp-media/wp-rocket/issues/369)

**Fixed bugs:**

- JSON import settings doesn't work for IE/Edge [\#464](https://github.com/wp-media/wp-rocket/issues/464)
- Relative CDN URL rewriting breaks BuddyPress 'Change Avatar' [\#395](https://github.com/wp-media/wp-rocket/issues/395)

**Closed issues:**

- rocket\_minify\_process regex bug [\#485](https://github.com/wp-media/wp-rocket/issues/485)

## [v2.10.6](https://github.com/wp-media/wp-rocket/tree/v2.10.6) (2017-07-13)
**Fixed bugs:**

- CDN URL should not be applied when in admin [\#465](https://github.com/wp-media/wp-rocket/issues/465)
- When WooCommerce pages aren't specified it can prevent caching [\#463](https://github.com/wp-media/wp-rocket/issues/463)
- Cache doesn't work on Windows server [\#462](https://github.com/wp-media/wp-rocket/issues/462)

## [v2.10.5](https://github.com/wp-media/wp-rocket/tree/v2.10.5) (2017-06-28)
**Implemented enhancements:**

- Don't cache feed by default [\#456](https://github.com/wp-media/wp-rocket/issues/456)
- Auto-exclude purchase confirmation page from EDD [\#452](https://github.com/wp-media/wp-rocket/issues/452)

**Fixed bugs:**

- path is not correct in certain cases in process.php [\#458](https://github.com/wp-media/wp-rocket/issues/458)
- Remove query strings option breaks when request is not returning a 200 response code [\#441](https://github.com/wp-media/wp-rocket/issues/441)

**Closed issues:**

- Correct typo in explanatory text for Preload bot option [\#454](https://github.com/wp-media/wp-rocket/issues/454)

## [v2.10.4](https://github.com/wp-media/wp-rocket/tree/v2.10.4) (2017-06-22)
**Implemented enhancements:**

- Elementor auto-compatibility not needed anymore [\#437](https://github.com/wp-media/wp-rocket/issues/437)
- Replace admin\_iclflag class by icl\_als\_iclflag in admin menu when WPML is active [\#430](https://github.com/wp-media/wp-rocket/issues/430)
- Compatibility with GeotargetingWP [\#419](https://github.com/wp-media/wp-rocket/issues/419)

**Fixed bugs:**

- Improved CSS & JS minifier are not used [\#449](https://github.com/wp-media/wp-rocket/issues/449)
- Incorrect path rewriting in CSS when CDN & remove query strings are active [\#447](https://github.com/wp-media/wp-rocket/issues/447)
- Don't manually add WPML js file in the footer anymore [\#444](https://github.com/wp-media/wp-rocket/issues/444)

## [v2.10.3](https://github.com/wp-media/wp-rocket/tree/v2.10.3) (2017-06-08)
**Fixed bugs:**

- Purge cloudflare cache admin menu link is missing [\#429](https://github.com/wp-media/wp-rocket/issues/429)

## [v2.10.2](https://github.com/wp-media/wp-rocket/tree/v2.10.2) (2017-06-08)
## [v2.10.1](https://github.com/wp-media/wp-rocket/tree/v2.10.1) (2017-06-07)
**Implemented enhancements:**

- SECURITY: sslverify / ssl\_verify. Developers: Stop Using sslverify = false - WordPress has you covered. [\#382](https://github.com/wp-media/wp-rocket/issues/382)
- Add troubleshooting info to API key page  [\#259](https://github.com/wp-media/wp-rocket/issues/259)
- Deferred JS option ignores conditional enqueing [\#139](https://github.com/wp-media/wp-rocket/issues/139)
-  Minification doesn't work with some Varnish configs [\#71](https://github.com/wp-media/wp-rocket/issues/71)
- Better message when the API key / license does not validate [\#43](https://github.com/wp-media/wp-rocket/issues/43)

**Fixed bugs:**

- 2.10 alpha3 - Async CSS isn't apply on some CSS files [\#412](https://github.com/wp-media/wp-rocket/issues/412)
- Issues with CDN on WP Engine [\#402](https://github.com/wp-media/wp-rocket/issues/402)
-  Moving JS in footer removes attributes [\#66](https://github.com/wp-media/wp-rocket/issues/66)

## [v2.10](https://github.com/wp-media/wp-rocket/tree/v2.10) (2017-06-07)
**Implemented enhancements:**

- Clarify language for mobile caching settings [\#256](https://github.com/wp-media/wp-rocket/issues/256)
- GoDaddy Managed hosting compatibility [\#131](https://github.com/wp-media/wp-rocket/issues/131)

**Fixed bugs:**

- Remove beta option until it applies per site only [\#422](https://github.com/wp-media/wp-rocket/issues/422)
- Use of stripslashes hurts CSS [\#417](https://github.com/wp-media/wp-rocket/issues/417)
- 2.10 alpha-3 - critical css still present on un-cached page [\#411](https://github.com/wp-media/wp-rocket/issues/411)
- 2.10 alpha-3 - saving critical css escapes some characters [\#410](https://github.com/wp-media/wp-rocket/issues/410)
- 2.10 alpha 3 - jquery deferred when static resources enabled [\#409](https://github.com/wp-media/wp-rocket/issues/409)
- Full path is incorrect when using Remove query strings from static resources with WP in a subdirectory [\#399](https://github.com/wp-media/wp-rocket/issues/399)
- style\_loader\_src / script\_loader\_src and front-end editors [\#385](https://github.com/wp-media/wp-rocket/issues/385)

## [v2.9.11](https://github.com/wp-media/wp-rocket/tree/v2.9.11) (2017-04-04)
## [v2.9.10](https://github.com/wp-media/wp-rocket/tree/v2.9.10) (2017-03-29)
## [v2.9.9](https://github.com/wp-media/wp-rocket/tree/v2.9.9) (2017-03-20)
**Implemented enhancements:**

- Add compatibility when Autoptimize is activate [\#376](https://github.com/wp-media/wp-rocket/issues/376)
- .htaccess BasicAuth and Minification [\#373](https://github.com/wp-media/wp-rocket/issues/373)
- Update Mobile\_Detect class to recent version [\#365](https://github.com/wp-media/wp-rocket/issues/365)

**Fixed bugs:**

- CDN URL is applied to SVG reference [\#378](https://github.com/wp-media/wp-rocket/issues/378)
- Remove query string not applied when minification disabled on a single post [\#367](https://github.com/wp-media/wp-rocket/issues/367)
- Imagify install button doesn't work since WP 4.6 [\#156](https://github.com/wp-media/wp-rocket/issues/156)
-  LazyLoad Iframes & Videos crashes Android Facebook browser [\#75](https://github.com/wp-media/wp-rocket/issues/75)

## [v2.9.8](https://github.com/wp-media/wp-rocket/tree/v2.9.8) (2017-03-02)
**Implemented enhancements:**

- Use PHP port of the YUI Compressor's CSSmin [\#339](https://github.com/wp-media/wp-rocket/issues/339)

**Fixed bugs:**

- CDN breaks Envira gallery lightbox [\#360](https://github.com/wp-media/wp-rocket/issues/360)
- PHP notice when saving WP Rocket settings since 2.9.7 [\#359](https://github.com/wp-media/wp-rocket/issues/359)
- Issue in process.php when $rocket\_cache\_reject\_ua is empty [\#358](https://github.com/wp-media/wp-rocket/issues/358)
- JS & CSS minification should reflect uncached position of enqueued scripts & styles and not $wp\_scripts-\>in\_footer array [\#304](https://github.com/wp-media/wp-rocket/issues/304)
- Compatibility between CDN & Hide My WP [\#56](https://github.com/wp-media/wp-rocket/issues/56)
-  If seach pages are cached with the filter, Js files added in the footer with minification are not added [\#47](https://github.com/wp-media/wp-rocket/issues/47)

## [v2.9.7](https://github.com/wp-media/wp-rocket/tree/v2.9.7) (2017-02-27)
## [v2.9.6](https://github.com/wp-media/wp-rocket/tree/v2.9.6) (2017-02-22)
**Implemented enhancements:**

- Save WP Engine CDN value in transient [\#346](https://github.com/wp-media/wp-rocket/issues/346)
- Remove labJS and use the defer attribute instead [\#335](https://github.com/wp-media/wp-rocket/issues/335)

**Fixed bugs:**

- Remove query strings not applied on page excluded from cache when CSS/JS minification enabled [\#347](https://github.com/wp-media/wp-rocket/issues/347)
- Gravatar file duplicated with JS Minification [\#344](https://github.com/wp-media/wp-rocket/issues/344)
- Prevent CDN URL replacement on images posted via XMLRPC.php [\#337](https://github.com/wp-media/wp-rocket/issues/337)

## [v2.9.5](https://github.com/wp-media/wp-rocket/tree/v2.9.5) (2017-02-09)
**Implemented enhancements:**

- Improve compatibility with Autoptimize [\#329](https://github.com/wp-media/wp-rocket/issues/329)
- Add version or timestamp to Simple Custom CSS filename [\#325](https://github.com/wp-media/wp-rocket/issues/325)
- Auto-exclude "http://www.industriejobs.de" external domain from the JS minification  [\#307](https://github.com/wp-media/wp-rocket/issues/307)
- Export options as JSON instead of gzip [\#246](https://github.com/wp-media/wp-rocket/issues/246)

**Fixed bugs:**

- Fix PHP Notice: Undefined index: HTTP\_CF\_CONNECTING\_IP in ../inc/common/cloudflare.php on line 31 [\#338](https://github.com/wp-media/wp-rocket/issues/338)
- Add Minify Key on dynamic CSS & JS files to avoid browser caching issue  [\#327](https://github.com/wp-media/wp-rocket/issues/327)
- Import no longer works on WordPress 4.7.1 [\#315](https://github.com/wp-media/wp-rocket/issues/315)
-  PHP Parse error:  syntax error, unexpected ':' since 2.9.4 [\#311](https://github.com/wp-media/wp-rocket/issues/311)
- URI rewriter passes through empty URLs [\#308](https://github.com/wp-media/wp-rocket/issues/308)
- PHP Warning filemtime\(\) [\#238](https://github.com/wp-media/wp-rocket/issues/238)

## [v2.9.4](https://github.com/wp-media/wp-rocket/tree/v2.9.4) (2017-01-22)
## [v2.9.3](https://github.com/wp-media/wp-rocket/tree/v2.9.3) (2017-01-18)
**Implemented enhancements:**

- 132 PHP Warmings [\#303](https://github.com/wp-media/wp-rocket/issues/303)
- Add filters to remove query string and static file creation [\#288](https://github.com/wp-media/wp-rocket/issues/288)

**Fixed bugs:**

- CloudFlare limits listing zones at 50 at a time [\#282](https://github.com/wp-media/wp-rocket/issues/282)
- Remove query string fails when “?ver=” doesn’t start the query string [\#279](https://github.com/wp-media/wp-rocket/issues/279)

## [v2.9.2](https://github.com/wp-media/wp-rocket/tree/v2.9.2) (2017-01-10)
**Implemented enhancements:**

- Comments for Yandex are removed [\#272](https://github.com/wp-media/wp-rocket/issues/272)
- wp\_get\_attachment\_image\_src CDN [\#271](https://github.com/wp-media/wp-rocket/issues/271)

**Fixed bugs:**

- Static Resources + Deferred JS + CDN Loads Files Twice [\#292](https://github.com/wp-media/wp-rocket/issues/292)
- CDN for background image in HTML is breaking if URL is surrounded by &quot; [\#289](https://github.com/wp-media/wp-rocket/issues/289)
- Fatal error with Move Login 2.4 [\#286](https://github.com/wp-media/wp-rocket/issues/286)
- Save CloudFlare IPs in transient even if they're the hardcoded ones [\#284](https://github.com/wp-media/wp-rocket/issues/284)
- Check for get\_post\_type\_object\(\) = null when purging [\#278](https://github.com/wp-media/wp-rocket/issues/278)
- The busting folder is not removed with others during uninstall [\#273](https://github.com/wp-media/wp-rocket/issues/273)
- Cache Busting adds a "?" adds the end for filename which contains a number [\#270](https://github.com/wp-media/wp-rocket/issues/270)

**Closed issues:**

- Move rocket\_get\_dns\_prefetch\_domains into inc/functions/options.php [\#280](https://github.com/wp-media/wp-rocket/issues/280)

## [v2.9.1](https://github.com/wp-media/wp-rocket/tree/v2.9.1) (2016-12-27)
**Implemented enhancements:**

- Improve compatibility with WP Retina [\#264](https://github.com/wp-media/wp-rocket/issues/264)
- WeePie compatibility [\#262](https://github.com/wp-media/wp-rocket/issues/262)
- Improve l18n in faq.php [\#251](https://github.com/wp-media/wp-rocket/issues/251)
- Make FAQ tab translatable [\#200](https://github.com/wp-media/wp-rocket/issues/200)

**Fixed bugs:**

- Minification + cache busting = file exclusions don't work [\#230](https://github.com/wp-media/wp-rocket/issues/230)
- Fatal error: Uncaught Error: Call to undefined function get\_rocket\_cloudflare\_api\_instance\(\) in /inc/admin/compat/cf-upgrader-5.4.php on line 3 [\#225](https://github.com/wp-media/wp-rocket/issues/225)

**Closed issues:**

- missing l10n [\#228](https://github.com/wp-media/wp-rocket/issues/228)
- Typo in the settings - an extra "n": [\#174](https://github.com/wp-media/wp-rocket/issues/174)

## [v2.9](https://github.com/wp-media/wp-rocket/tree/v2.9) (2016-12-19)
**Implemented enhancements:**

- Comments for ESI tags are removed [\#253](https://github.com/wp-media/wp-rocket/issues/253)
- Auto-purge the "Posts page" when a post is added / updated / deleted [\#244](https://github.com/wp-media/wp-rocket/issues/244)
- Automatic Compatibility with Divi Blog Module [\#243](https://github.com/wp-media/wp-rocket/issues/243)
- Clear product cache when new variation is added [\#234](https://github.com/wp-media/wp-rocket/issues/234)
- auto-exclude Disqus Comment System JS [\#204](https://github.com/wp-media/wp-rocket/issues/204)
- Compatibility with WP-AppKit [\#197](https://github.com/wp-media/wp-rocket/issues/197)
- Add parents urls to the purge list [\#169](https://github.com/wp-media/wp-rocket/issues/169)
- Compatibility with WooCommerce Multilingual \(Currency Switcher option\) [\#165](https://github.com/wp-media/wp-rocket/issues/165)

**Fixed bugs:**

- Everything under CPT archive is cleared when updating a post of this CPT [\#249](https://github.com/wp-media/wp-rocket/issues/249)
- Vulnerability in rocket\_valid\_key\(\) [\#226](https://github.com/wp-media/wp-rocket/issues/226)
- Prevent empty value for files to be minified [\#224](https://github.com/wp-media/wp-rocket/issues/224)
- Fatal error: Call to undefined function Cloudflare\curl\_init\(\) in /inc/vendors/CloudFlare/Api.php on line 206 [\#222](https://github.com/wp-media/wp-rocket/issues/222)
- rocket\_add\_url\_protocol\(\) might return a bad formatting [\#220](https://github.com/wp-media/wp-rocket/issues/220)
- SessionStorage in admin settings causes a JS error when on private mode [\#212](https://github.com/wp-media/wp-rocket/issues/212)
- RegEx syntax stripped from user agent exclusion field [\#171](https://github.com/wp-media/wp-rocket/issues/171)

**Closed issues:**

- \[L10n\] Typo in de\_DE [\#173](https://github.com/wp-media/wp-rocket/issues/173)

## [v2.8.23](https://github.com/wp-media/wp-rocket/tree/v2.8.23) (2016-10-24)
**Fixed bugs:**

- CloudFlare namespacing breaks sites under PHP 5.2 [\#207](https://github.com/wp-media/wp-rocket/issues/207)

## [v2.8.22](https://github.com/wp-media/wp-rocket/tree/v2.8.22) (2016-10-18)
## [v2.8.21](https://github.com/wp-media/wp-rocket/tree/v2.8.21) (2016-10-17)
## [v2.8.20](https://github.com/wp-media/wp-rocket/tree/v2.8.20) (2016-10-13)
## [v2.8.19](https://github.com/wp-media/wp-rocket/tree/v2.8.19) (2016-10-12)
**Implemented enhancements:**

- Auto exclude ads.investingchannel.com from JS minification  [\#199](https://github.com/wp-media/wp-rocket/issues/199)

## [v2.8.18](https://github.com/wp-media/wp-rocket/tree/v2.8.18) (2016-10-12)
## [v2.8.17](https://github.com/wp-media/wp-rocket/tree/v2.8.17) (2016-10-11)
**Implemented enhancements:**

- Update CloudFlare integration to API v4 [\#104](https://github.com/wp-media/wp-rocket/issues/104)

**Fixed bugs:**

- Typo in API Key message [\#102](https://github.com/wp-media/wp-rocket/issues/102)
- Typo on Imagify banner text [\#101](https://github.com/wp-media/wp-rocket/issues/101)

## [v2.8.16](https://github.com/wp-media/wp-rocket/tree/v2.8.16) (2016-10-10)
**Implemented enhancements:**

- Update Minify Library to version 2.3 [\#195](https://github.com/wp-media/wp-rocket/issues/195)
- Common cache for logged in users [\#193](https://github.com/wp-media/wp-rocket/issues/193)
- Fix nonce issue with Visual Composer Post Grid [\#72](https://github.com/wp-media/wp-rocket/issues/72)

**Fixed bugs:**

- CSS minification breaks calc\(\), vendor update required [\#86](https://github.com/wp-media/wp-rocket/issues/86)
-  Minify adds path to css rules with url\(\) even if not needed [\#70](https://github.com/wp-media/wp-rocket/issues/70)
- Sucuri SiteCheck malware warning about error in JSMin.php [\#68](https://github.com/wp-media/wp-rocket/issues/68)

## [v2.8.15](https://github.com/wp-media/wp-rocket/tree/v2.8.15) (2016-10-05)
**Implemented enhancements:**

- Automatic compatibility with Visual Composer Grid [\#186](https://github.com/wp-media/wp-rocket/issues/186)
- Remove WP Mobile Detector from mobile cache plugins list [\#172](https://github.com/wp-media/wp-rocket/issues/172)
- LazyLoad compatibility with BuddyPress change avatar page [\#155](https://github.com/wp-media/wp-rocket/issues/155)
- Add Compatibility for Aelia Prices by Country and others [\#82](https://github.com/wp-media/wp-rocket/issues/82)

**Fixed bugs:**

- Translated URLs are not returned with qTranslate-X [\#183](https://github.com/wp-media/wp-rocket/issues/183)
- gzipped cache file not cleared for date URL [\#179](https://github.com/wp-media/wp-rocket/issues/179)
- Untrailing slash CDN URL in options [\#176](https://github.com/wp-media/wp-rocket/issues/176)
- File\(/…/wp-content/cache/wp-rocket/\[sitename\]/index.html\_gzip/.mobile-active\) is not within the allowed path\(s\) [\#175](https://github.com/wp-media/wp-rocket/issues/175)
- Varnish Purge Compatibility with proxies [\#170](https://github.com/wp-media/wp-rocket/issues/170)
- Warning on enfold theme [\#167](https://github.com/wp-media/wp-rocket/issues/167)

**Closed issues:**

- Warning: Unexpected character in input: '' \(ASCII=28\) state=0 in /wp-content/plugins/wp-rocket/inc/common/purge.php on line 464 [\#189](https://github.com/wp-media/wp-rocket/issues/189)
- Export file contains "wp-rocket" even under white label [\#168](https://github.com/wp-media/wp-rocket/issues/168)

## [v2.8.14](https://github.com/wp-media/wp-rocket/tree/v2.8.14) (2016-09-17)
## [v2.8.13](https://github.com/wp-media/wp-rocket/tree/v2.8.13) (2016-09-14)
**Fixed bugs:**

- Auto-exclude Facebook User-Agent from the cache to avoid issues with LazyLoad [\#159](https://github.com/wp-media/wp-rocket/issues/159)
- X-CF-Powered-By: WP Rocket not removed when White label is active [\#154](https://github.com/wp-media/wp-rocket/issues/154)

## [v2.8.12](https://github.com/wp-media/wp-rocket/tree/v2.8.12) (2016-09-08)
**Fixed bugs:**

- WPML domain mapping and JS minification issue [\#145](https://github.com/wp-media/wp-rocket/issues/145)
- Excluding the homepage automatically excludes all future pages [\#143](https://github.com/wp-media/wp-rocket/issues/143)
- Non-public CPT urls are added to cache.json [\#142](https://github.com/wp-media/wp-rocket/issues/142)
- Files with + in filename do not get excluded from minification [\#138](https://github.com/wp-media/wp-rocket/issues/138)

## [v2.8.11](https://github.com/wp-media/wp-rocket/tree/v2.8.11) (2016-08-25)
**Implemented enhancements:**

- Implement compatibility with Thrive Visual Editor [\#136](https://github.com/wp-media/wp-rocket/issues/136)
- Automatically exclude buddypress/bp-core/js/bp-plupload.min.js from JS minification [\#128](https://github.com/wp-media/wp-rocket/issues/128)
- Purge OPCache when updating [\#125](https://github.com/wp-media/wp-rocket/issues/125)

**Fixed bugs:**

- JS variables for SWAL are not defined in the success confirm support action [\#130](https://github.com/wp-media/wp-rocket/issues/130)
- WPML: home\_url\(\) returns the wrong URL when used to create the URL for the always purge pages [\#129](https://github.com/wp-media/wp-rocket/issues/129)
- DNS prefetch is added to AMP page for WP \< 4.5 [\#124](https://github.com/wp-media/wp-rocket/issues/124)

## [v2.8.10](https://github.com/wp-media/wp-rocket/tree/v2.8.10) (2016-08-01)
## [v2.8.9](https://github.com/wp-media/wp-rocket/tree/v2.8.9) (2016-07-29)
**Implemented enhancements:**

- Improve DNS Prefetch option to be hooked on WP 4.6 resource hints [\#109](https://github.com/wp-media/wp-rocket/issues/109)
- Update swal to swal v2 [\#106](https://github.com/wp-media/wp-rocket/issues/106)

**Fixed bugs:**

- Weekly and Monthly Database Optimizations Don't Run [\#120](https://github.com/wp-media/wp-rocket/issues/120)
- Illegal offset type in WPML [\#117](https://github.com/wp-media/wp-rocket/issues/117)
- mod\_deflate rules conflict with video in Safari [\#114](https://github.com/wp-media/wp-rocket/issues/114)
- Wrong hook name in admin/ajax.php for capacity [\#105](https://github.com/wp-media/wp-rocket/issues/105)
- Purge this URL clears everything when processed from the Homepage [\#103](https://github.com/wp-media/wp-rocket/issues/103)
- Broken Beaver Builder compatibility [\#100](https://github.com/wp-media/wp-rocket/issues/100)

## [2.8.8](https://github.com/wp-media/wp-rocket/tree/2.8.8) (2016-07-09)
## [2.8.7](https://github.com/wp-media/wp-rocket/tree/2.8.7) (2016-07-06)
**Implemented enhancements:**

- Prevent customer that we automatically remove cart/checkout from the cache below the "Never cache the following pages:" [\#41](https://github.com/wp-media/wp-rocket/issues/41)
- Rollback Pretty URL sur minification CSS/JS [\#9](https://github.com/wp-media/wp-rocket/issues/9)
- Améliorer la détection des mobiles avec HTTP:X-Wap-Profile dans le fichier .htaccess [\#8](https://github.com/wp-media/wp-rocket/issues/8)

**Fixed bugs:**

- Page is cached when there are the 3 utm query strings and even if there are another query string [\#42](https://github.com/wp-media/wp-rocket/issues/42)
- Detect if iThemes Security is active, remove their filter before updating WP Rocket, so htaccess doesn't get messed up. [\#40](https://github.com/wp-media/wp-rocket/issues/40)
- Reset White Label values doesn't work [\#38](https://github.com/wp-media/wp-rocket/issues/38)
- Don't apply LazyLoad on images served by WP Retina x2 [\#37](https://github.com/wp-media/wp-rocket/issues/37)
- Undefined offsets leading to PHP Warning [\#36](https://github.com/wp-media/wp-rocket/issues/36)
- Var content check missing, leading to a PHP Warning, maybe, sometime. [\#35](https://github.com/wp-media/wp-rocket/issues/35)
- Bad type check on a var leading to a PHP Warning [\#34](https://github.com/wp-media/wp-rocket/issues/34)
- Autoupdate fails on old \(\<3.8\) WP Versions [\#33](https://github.com/wp-media/wp-rocket/issues/33)
- WP Rocket update fail from Update Core Network \(Multi-Site\) [\#32](https://github.com/wp-media/wp-rocket/issues/32)
- JS and CSS files are not added to the CDN [\#31](https://github.com/wp-media/wp-rocket/issues/31)
- PHP Notice: Undefined offset: 1 in ../inc/functions/formatting.php on line 211 [\#30](https://github.com/wp-media/wp-rocket/issues/30)
- Trim to remove space from the url minified if added in the footer [\#29](https://github.com/wp-media/wp-rocket/issues/29)
- Don't apply LazyLoad on Soliloquy [\#28](https://github.com/wp-media/wp-rocket/issues/28)
- The "yes" response to the question "do you want autoupdate" do not check the related option [\#27](https://github.com/wp-media/wp-rocket/issues/27)
- The rollback button do not uncheck the autoupdate box [\#26](https://github.com/wp-media/wp-rocket/issues/26)
- Double slash / / on images from CDN [\#25](https://github.com/wp-media/wp-rocket/issues/25)
- Auto-deactivation during an auto-update [\#24](https://github.com/wp-media/wp-rocket/issues/24)
- Don't apply LazyLoad on Media Grid plugin [\#23](https://github.com/wp-media/wp-rocket/issues/23)
- No cache when WooCommerce or Jigoshop checkout page is empty [\#22](https://github.com/wp-media/wp-rocket/issues/22)
- Empty Google Fonts causes an 404 error [\#21](https://github.com/wp-media/wp-rocket/issues/21)
- The whole cache files are deleted after updating a product with WooCommerce [\#20](https://github.com/wp-media/wp-rocket/issues/20)
- Utiliser sprintf\(\) sur la faq [\#19](https://github.com/wp-media/wp-rocket/issues/19)
- Fatal error: Call to undefined function \_\_\(\) in /wp-rocket.php on line 17 [\#18](https://github.com/wp-media/wp-rocket/issues/18)
- Le nom du plugin en WL remplace le nom du plugin a installer [\#17](https://github.com/wp-media/wp-rocket/issues/17)
- WP Rocket 2.1 requiert WP 3.5 et non 3.1 [\#16](https://github.com/wp-media/wp-rocket/issues/16)
- "Afficher les détails" affichera les infos ROCKET même en WL [\#15](https://github.com/wp-media/wp-rocket/issues/15)
- Erreurs de traductions [\#14](https://github.com/wp-media/wp-rocket/issues/14)
- Déplacer Rocketter dans l'onglet "Support" [\#13](https://github.com/wp-media/wp-rocket/issues/13)
- La clé se supprime si notre serveur est HS [\#12](https://github.com/wp-media/wp-rocket/issues/12)
- WHITE LABEL toujours actif [\#11](https://github.com/wp-media/wp-rocket/issues/11)
- Lors de l'enregistrement de la clé, NOTICES [\#10](https://github.com/wp-media/wp-rocket/issues/10)
- Pas de cache avec le plugin qTranslate [\#7](https://github.com/wp-media/wp-rocket/issues/7)
- Suppression du fichier de configuration à la désactivation du plugin [\#6](https://github.com/wp-media/wp-rocket/issues/6)
- Lien vers la vidéo Youtube de Minification CSS/JS incorrect [\#5](https://github.com/wp-media/wp-rocket/issues/5)
- Bug des images avec le plugin LayerSlider [\#4](https://github.com/wp-media/wp-rocket/issues/4)
- Copie des droits d'écriture sur le fichier .htaccess [\#3](https://github.com/wp-media/wp-rocket/issues/3)
- Notice: Use of undefined constant DOING\_AJAX - assumed 'DOING\_AJAX' in ../wp-content/plugins/wp-rocket/inc/admin/admin.php on line 167 [\#2](https://github.com/wp-media/wp-rocket/issues/2)
- Notice: Undefined index: Domain Path in wp-rocket/inc/admin/admin.php on line 328 [\#1](https://github.com/wp-media/wp-rocket/issues/1)
- Assets from theme have the wrong schema when site is http and CDN is https [\#96](https://github.com/wp-media/wp-rocket/issues/96)
- WP Rocket 2.8.6 Breaks WP Engine CDN [\#93](https://github.com/wp-media/wp-rocket/issues/93)
-  Replace escaped single quotes in .htaccess comments with real apostrophes [\#91](https://github.com/wp-media/wp-rocket/issues/91)



\* *This Change Log was automatically generated by [github_changelog_generator](https://github.com/skywinder/Github-Changelog-Generator)*