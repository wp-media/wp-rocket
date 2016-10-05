<?php

namespace Cloudflare\Zone;

use Cloudflare\Api;

/**
 * CloudFlare API wrapper
 *
 * Zone Settings
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Settings extends Api
{
    /**
     * Zone settings (permission needed: #zone_settings:read)
     * Available settings for your user in relation to a zone
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function settings($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings');
    }

    /**
     * Advanced DDOS setting (permission needed: #zone_settings:read)
     * Advanced protection from Distributed Denial of Service (DDoS) attacks on your website.
     * This is an uneditable value that is 'on' in the case of Business and Enterprise zones
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function advanced_ddos($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/advanced_ddos');
    }

    /**
     * Get Always Online setting (permission needed: #zone_settings:read)
     * When enabled, Always Online will serve pages from our cache if your server is offline
     * (https://support.cloudflare.com/hc/en-us/articles/200168006)
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function always_online($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/always_online');
    }

    /**
     * Get Browser Cache TTL setting (permission needed: #zone_settings:read)
     * Browser Cache TTL (in seconds) specifies how long CloudFlare-cached resources will remain on your visitors' computers.
     * CloudFlare will honor any larger times specified by your server.
     * (https://support.cloudflare.com/hc/en-us/articles/200168276)
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function browser_cache_ttl($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/browser_cache_ttl');
    }

    /**
     * Get Browser Check setting (permission needed: #zone_settings:read)
     * Browser Integrity Check is similar to Bad Behavior and looks for common HTTP headers abused most commonly by spammers and denies access to your page.
     * It will also challenge visitors that do not have a user agent or a non standard user agent (also commonly used by abuse bots, crawlers or visitors).
     * (https://support.cloudflare.com/hc/en-us/articles/200170086)
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function browser_check($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/browser_check');
    }

    /**
     * Get Cache Level setting (permission needed: #zone_settings:read)
     * Cache Level functions based off the setting level. The basic setting will cache most static resources (i.e., css, images, and JavaScript).
     * The simplified setting will ignore the query string when delivering a cached resource. The aggressive setting will cache all static resources, including ones with a query string.
     * (https://support.cloudflare.com/hc/en-us/articles/200168256)
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function cache_level($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/cache_level');
    }

    /**
     * Get Challenge TTL setting (permission needed: #zone_settings:read)
     * Specify how long a visitor is allowed access to your site after successfully completing a challenge (such as a CAPTCHA). After the TTL has expired the visitor will have to complete a new challenge.
     * We recommend a 15 - 45 minute setting and will attempt to honor any setting above 45 minutes.
     * (https://support.cloudflare.com/hc/en-us/articles/200170136)
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function challenge_ttl($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/challenge_ttl');
    }

    /**
     * Get Development Mode setting (permission needed: #zone_settings:read)
     * Development Mode temporarily allows you to enter development mode for your websites if you need to make changes to your site.
     * This will bypass CloudFlare's accelerated cache and slow down your site, but is useful if you are making changes to cacheable content (like images, css, or JavaScript) and would like to see those changes right away.
     * Once entered, development mode will last for 3 hours and then automatically toggle off.
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function development_mode($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/development_mode');
    }

    /**
     * Get Email Obfuscation setting (permission needed: #zone_settings:read)
     * Encrypt email adresses on your web page from bots, while keeping them visible to humans.
     * (https://support.cloudflare.com/hc/en-us/articles/200170016)
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function email_obfuscation($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/email_obfuscation');
    }

    /**
     * Get Hotlink Protection setting (permission needed: #zone_settings:read)
     * When enabled, the Hotlink Protection option ensures that other sites cannot suck up your bandwidth by building pages that use images hosted on your site.
     * Anytime a request for an image on your site hits CloudFlare, we check to ensure that it's not another site requesting them.
     * People will still be able to download and view images from your page, but other sites won't be able to steal them for use on their own pages.
     * (https://support.cloudflare.com/hc/en-us/articles/200170026)
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function hotlink_protection($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/hotlink_protection');
    }

    /**
     * Get IP Geolocation setting (permission needed: #zone_settings:read)
     * Enable IP Geolocation to have CloudFlare geolocate visitors to your website and pass the country code to you.
     * (https://support.cloudflare.com/hc/en-us/articles/200168236)
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function ip_geolocation($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/ip_geolocation');
    }

    /**
     * Get IP IPv6 setting (permission needed: #zone_settings:read)
     * Enable IPv6 on all subdomains that are CloudFlare enabled.
     * (https://support.cloudflare.com/hc/en-us/articles/200168586)
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function ipv6($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/ipv6');
    }

    /**
     * Get IP Minify setting (permission needed: #zone_settings:read)
     * Automatically minify certain assets for your website (https://support.cloudflare.com/hc/en-us/articles/200168196).
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function minify($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/minify');
    }

    /**
     * Get Mobile Redirect setting (permission needed: #zone_settings:read)
     * Automatically redirect visitors on mobile devices to a mobile-optimized subdomain (https://support.cloudflare.com/hc/en-us/articles/200168336).
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function mobile_redirect($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/mobile_redirect');
    }

    /**
     * Get Mirage setting (permission needed: #zone_settings:read)
     * Automatically optimize image loading for website visitors on mobile devices (http://blog.cloudflare.com/mirage2-solving-mobile-speed).
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function mirage($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/mirage');
    }

    /**
     * Get Enable Error Pages On setting (permission needed: #zone_settings:read)
     * CloudFlare will proxy customer error pages on any 502,504 errors on origin server instead of showing a default CloudFlare error page.
     * This does not apply to 522 errors and is limited to Enterprise Zones.
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function origin_error_page_pass_thru($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/origin_error_page_pass_thru');
    }

    /**
     * Get Polish setting (permission needed: #zone_settings:read)
     * Strips metadata and compresses your images for faster page load times. Basic (Lossless): Reduce the size of PNG, JPEG, and GIF files - no impact on visual quality.
     * Basic + JPEG (Lossy): Further reduce the size of JPEG files for faster image loading.
     * Larger JPEGs are converted to progressive images, loading a lower-resolution image first and ending in a higher-resolution version. Not recommended for hi-res photography sites.
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function polish($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/polish');
    }

    /**
     * Get Prefetch Preload setting (permission needed: #zone_settings:read)
     * CloudFlare will prefetch any URLs that are included in the response headers. This is limited to Enterprise Zones.
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function prefetch_preload($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/prefetch_preload');
    }

    /**
     * Get Response Buffering setting (permission needed: #zone_settings:read)
     * Enables or disables buffering of responses from the proxied server. CloudFlare may buffer the whole payload to deliver it at once to the client versus allowing it to be delivered in chunks.
     * By default, the proxied server streams directly and is not buffered by CloudFlare. This is limited to Enterprise Zones.
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function response_buffering($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/response_buffering');
    }

    /**
     * Get Rocket Loader setting (permission needed: #zone_settings:read)
     * Rocket Loader is a general-purpose asynchronous JavaScript loader coupled with a lightweight virtual browser which can safely run any JavaScript code after window.onload.
     * Turning on Rocket Loader will immediately improve a web page's window.onload time (assuming there is JavaScript on the page), which can have a positive impact on your Google search ranking.
     * Automatic Mode: Rocket Loader will automatically run on the JavaScript resources on your site, with no configuration required after turning on automatic mode. Manual Mode: In order to have Rocket Loader execute for a particular script, you must add the following attribute to the script tag: "data-cfasync='true'".
     * As your page passes through CloudFlare, we'll enable Rocket Loader for that particular script. All other JavaScript will continue to execute without CloudFlare touching the script. (https://support.cloudflare.com/hc/en-us/articles/200168056)
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function rocket_loader($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/rocket_loader');
    }

    /**
     * Get Security Header (HSTS) setting (permission needed: #zone_settings:read)
     * CloudFlare security header for a zone.
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function security_header($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/security_header');
    }

    /**
     * Get Security Level setting (permission needed: #zone_settings:read)
     * Choose the appropriate security profile for your website, which will automatically adjust each of the security settings. If you choose to customize an individual security setting, the profile will become Custom.
     * (https://support.cloudflare.com/hc/en-us/articles/200170056)
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function security_level($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/security_level');
    }

    /**
     * Get Server Side Exclude setting (permission needed: #zone_settings:read)
     * If there is sensitive content on your website that you want visible to real visitors, but that you want to hide from suspicious visitors, all you have to do is wrap the content with CloudFlare SSE tags. Wrap any content that you want to be excluded from suspicious visitors in the following SSE tags: .
     * For example: Bad visitors won't see my phone number, 555-555-5555 . Note: SSE only will work with HTML. If you have HTML minification enabled, you won't see the SSE tags in your HTML source when it's served through CloudFlare.
     * SSE will still function in this case, as CloudFlare's HTML minification and SSE functionality occur on-the-fly as the resource moves through our network to the visitor's computer.
     * (https://support.cloudflare.com/hc/en-us/articles/200170036)
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function server_side_exclude($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/server_side_exclude');
    }

    /**
     * Get Enable Query String Sort setting (permission needed: #zone_settings:read)
     * CloudFlare will treat files with the same query strings as the same file in cache, regardless of the order of the query strings. This is limited to Enterprise Zones.
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function sort_query_string_for_cache($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/sort_query_string_for_cache');
    }

    /**
     * Get SSL setting (permission needed: #zone_settings:read)
     * SSL encrypts your visitor's connection and safeguards credit card numbers and other personal data to and from your website. SSL can take up to 5 minutes to fully activate. Requires CloudFlare active on your root domain or www domain.
     * Off: no SSL between the visitor and CloudFlare, and no SSL between CloudFlare and your web server (all HTTP traffic).
     * Flexible: SSL between the visitor and CloudFlare -- visitor sees HTTPS on your site, but no SSL between CloudFlare and your web server. You don't need to have an SSL cert on your web server, but your vistors will still see the site as being HTTPS enabled.
     * Full: SSL between the visitor and CloudFlare -- visitor sees HTTPS on your site, and SSL between CloudFlare and your web server. You'll need to have your own SSL cert or self-signed cert at the very least.
     * Full (Strict): SSL between the visitor and CloudFlare -- visitor sees HTTPS on your site, and SSL between CloudFlare and your web server. You'll need to have a valid SSL certificate installed on your web server. This certificate must be signed by a certificate authority, have an expiration date in the future, and respond for the request domain name (hostname).
     * (https://support.cloudflare.com/hc/en-us/articles/200170416)
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function ssl($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/ssl');
    }

    /**
     * Get Zone Enable TLS 1.2 setting (permission needed: #zone_settings:read)
     * Enable Crypto TLS 1.2 feature for this zone and prevent use of previous versions. This is limited to Enterprise or Business Zones.
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function tls_1_2_only($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/tls_1_2_only');
    }

    /**
     * Get TLS Client Auth setting (permission needed: #zone_settings:read)
     * TLS Client Auth requires CloudFlare to connect to your origin server using a client certificate (Enterprise Only)
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function tls_client_auth($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/tls_client_auth');
    }

    /**
     * Get True Client IP setting (permission needed: #zone_settings:edit)
     * Allows customer to continue to use True Client IP (Akamai feature) in the headers we send to the origin. This is limited to Enterprise Zones.
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function true_client_ip_header($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/true_client_ip_header');
    }

    /**
     * Get Web Application Firewall (WAF) setting (permission needed: #zone_settings:read)
     * The WAF examines HTTP requests to your website. It inspects both GET and POST requests and applies rules to help filter out illegitimate traffic from legitimate website visitors. The CloudFlare WAF inspects website addresses or URLs to detect anything out of the ordinary. If the CloudFlare WAF determines suspicious user behavior, then the WAF will â€˜challengeâ€™ the web visitor with a page that asks them to submit a CAPTCHA successfully to continue their action. If the challenge is failed, the action will be stopped. What this means is that CloudFlareâ€™s WAF will block any traffic identified as illegitimate before it reaches your origin web server.
     * (https://support.cloudflare.com/hc/en-us/articles/200172016)
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function waf($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/settings/waf');
    }

    /**
     * Get Web Application Firewall (WAF) setting (permission needed: #zone_settings:edit)
     * Edit settings for a zone
     *
     * @param string $zone_identifier API item identifier tag
     * @param array  $items           One or more zone setting objects. Must contain an ID and a value.
     */
    public function edit($zone_identifier, array $items)
    {
        $data = [
            'items' => $items,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings', $data);
    }

    /**
     * Change Always Online setting (permission needed: #zone_settings:edit)
     * When enabled, Always Online will serve pages from our cache if your server is offline (https://support.cloudflare.com/hc/en-us/articles/200168006)
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: on)
     */
    public function change_always_on($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/always_online', $data);
    }

    /**
     * Change Browser Cache TTL setting (permission needed: #zone_settings:edit)
     * Browser Cache TTL (in seconds) specifies how long CloudFlare-cached resources will remain on your visitors' computers. CloudFlare will honor any larger times specified by your server.
     * (https://support.cloudflare.com/hc/en-us/articles/200168276)
     *
     * @param string   $zone_identifier API item identifier tag
     * @param int|null $value           Value of the zone setting (default: 14400)
     */
    public function change_browser_cache_ttl($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/browser_cache_ttl', $data);
    }

    /**
     * Change Browser Check setting (permission needed: #zone_settings:edit)
     * Browser Integrity Check is similar to Bad Behavior and looks for common HTTP headers abused most commonly by spammers and denies access to your page.
     * It will also challenge visitors that do not have a user agent or a non standard user agent (also commonly used by abuse bots, crawlers or visitors).
     * (https://support.cloudflare.com/hc/en-us/articles/200170086)
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: on)
     */
    public function change_browser_check($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/browser_check', $data);
    }

    /**
     * Change Cache Level setting (permission needed: #zone_settings:edit)
     * Cache Level functions based off the setting level. The basic setting will cache most static resources (i.e., css, images, and JavaScript).
     * The simplified setting will ignore the query string when delivering a cached resource. The aggressive setting will cache all static resources, including ones with a query string.
     * (https://support.cloudflare.com/hc/en-us/articles/200168256)
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: on)
     */
    public function change_cache_level($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/cache_level', $data);
    }

    /**
     * Change Challenge TTL setting (permission needed: #zone_settings:edit)
     * Specify how long a visitor is allowed access to your site after successfully completing a challenge (such as a CAPTCHA). After the TTL has expired the visitor will have to complete a new challenge.
     * We recommend a 15 - 45 minute setting and will attempt to honor any setting above 45 minutes.
     * (https://support.cloudflare.com/hc/en-us/articles/200170136)
     *
     * @param string   $zone_identifier API item identifier tag
     * @param int|null $value           Value of the zone setting (default: on)
     */
    public function change_challenge_ttl($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/challenge_ttl', $data);
    }

    /**
     * Change Development Mode setting (permission needed: #zone_settings:edit)
     * Development Mode temporarily allows you to enter development mode for your websites if you need to make changes to your site.
     * This will bypass CloudFlare's accelerated cache and slow down your site, but is useful if you are making changes to cacheable content (like images, css, or JavaScript) and would like to see those changes right away. Once entered, development mode will last for 3 hours and then automatically toggle off.
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: on)
     */
    public function change_development_mode($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/development_mode', $data);
    }

    /**
     * Change Enable Error Pages On setting (permission needed: #zone_settings:edit)
     * CloudFlare will proxy customer error pages on any 502,504 errors on origin server instead of showing a default CloudFlare error page. This does not apply to 522 errors and is limited to Enterprise Zones.
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: on)
     */
    public function change_origin_error_page_pass_thru($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/origin_error_page_pass_thru', $data);
    }

    /**
     * Change Enable Query String Sort setting (permission needed: #zone_settings:edit)
     * CloudFlare will treat files with the same query strings as the same file in cache, regardless of the order of the query strings. This is limited to Enterprise Zones.
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: on)
     */
    public function change_sort_query_string_for_cache($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/sort_query_string_for_cache', $data);
    }

    /**
     * Change Hotlink Protection setting (permission needed: #zone_settings:edit)
     * When enabled, the Hotlink Protection option ensures that other sites cannot suck up your bandwidth by building pages that use images hosted on your site. Anytime a request for an image on your site hits CloudFlare, we check to ensure that it's not another site requesting them.
     * People will still be able to download and view images from your page, but other sites won't be able to steal them for use on their own pages.
     * (https://support.cloudflare.com/hc/en-us/articles/200170026)
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: on)
     */
    public function change_hotlink_protection($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/hotlink_protection', $data);
    }

    /**
     * Change IP Geolocation setting (permission needed: #zone_settings:edit)
     * Enable IP Geolocation to have CloudFlare geolocate visitors to your website and pass the country code to you. (https://support.cloudflare.com/hc/en-us/articles/200168236)
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: on)
     */
    public function change_ip_geolocation($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/ip_geolocation', $data);
    }

    /**
     * Change IPv6 setting (permission needed: #zone_settings:edit)
     * Enable IPv6 on all subdomains that are CloudFlare enabled. (https://support.cloudflare.com/hc/en-us/articles/200168586)
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: on)
     */
    public function change_ipv6($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/ipv6', $data);
    }

    /**
     * Change Minify setting (permission needed: #zone_settings:edit)
     * Automatically minify certain assets for your website (https://support.cloudflare.com/hc/en-us/articles/200168196).
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting
     */
    public function change_minify($zone_identifier, $value)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/minify', $data);
    }

    /**
     * Change Mobile Redirect setting (permission needed: #zone_settings:edit)
     * Automatically redirect visitors on mobile devices to a mobile-optimized subdomain (https://support.cloudflare.com/hc/en-us/articles/200168336).
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: on)
     */
    public function change_mobile_redirect($zone_identifier, $value)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/mobile_redirect', $data);
    }

    /**
     * Change Mirage setting (permission needed: #zone_settings:edit)
     * Automatically optimize image loading for website visitors on mobile devices (http://blog.cloudflare.com/mirage2-solving-mobile-speed).
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: off)
     */
    public function change_mirage($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/mirage', $data);
    }

    /**
     * Change Polish setting (permission needed: #zone_settings:edit)
     * Strips metadata and compresses your images for faster page load times. Basic (Lossless): Reduce the size of PNG, JPEG, and GIF files - no impact on visual quality. Basic + JPEG (Lossy): Further reduce the size of JPEG files for faster image loading.
     * Larger JPEGs are converted to progressive images, loading a lower-resolution image first and ending in a higher-resolution version. Not recommended for hi-res photography sites.
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: off)
     */
    public function change_polish($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/polish', $data);
    }

    /**
     * Change Prefetch Preload setting (permission needed: #zone_settings:edit)
     * CloudFlare will prefetch any URLs that are included in the response headers. This is limited to Enterprise Zones.
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: off)
     */
    public function change_prefetch_preload($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/prefetch_preload', $data);
    }

    /**
     * Change Response Buffering setting (permission needed: #zone_settings:edit)
     * Enables or disables buffering of responses from the proxied server. CloudFlare may buffer the whole payload to deliver it at once to the client versus allowing it to be delivered in chunks.
     * By default, the proxied server streams directly and is not buffered by CloudFlare. This is limited to Enterprise Zones.
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: off)
     */
    public function change_response_buffering($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/response_buffering', $data);
    }

    /**
     * Change Rocket Loader setting (permission needed: #zone_settings:edit)
     * Rocket Loader is a general-purpose asynchronous JavaScript loader coupled with a lightweight virtual browser which can safely run any JavaScript code after window.onload. Turning on Rocket Loader will immediately improve a web page's window.onload time (assuming there is JavaScript on the page), which can have a positive impact on your Google search ranking.
     * Automatic Mode: Rocket Loader will automatically run on the JavaScript resources on your site, with no configuration required after turning on automatic mode.
     * Manual Mode: In order to have Rocket Loader execute for a particular script, you must add the following attribute to the script tag: "data-cfasync='true'". As your page passes through CloudFlare, we'll enable Rocket Loader for that particular script.
     * All other JavaScript will continue to execute without CloudFlare touching the script. (https://support.cloudflare.com/hc/en-us/articles/200168056)
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: off)
     */
    public function change_rocket_loader($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/rocket_loader', $data);
    }

    /**
     * Change Security Header (HSTS) setting (permission needed: #zone_settings:edit)
     * CloudFlare security header for a zone.
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: off)
     */
    public function change_security_header($zone_identifier, $value)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/security_header', $data);
    }

    /**
     * Change Security Level setting (permission needed: #zone_settings:edit)
     * Choose the appropriate security profile for your website, which will automatically adjust each of the security settings. If you choose to customize an individual security setting, the profile will become Custom.
     * (https://support.cloudflare.com/hc/en-us/articles/200170056)
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: medium)
     */
    public function change_security_level($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/security_level', $data);
    }

    /**
     * Change Server Side Exclude setting (permission needed: #zone_settings:edit)
     * If there is sensitive content on your website that you want visible to real visitors, but that you want to hide from suspicious visitors, all you have to do is wrap the content with CloudFlare SSE tags.
     * Wrap any content that you want to be excluded from suspicious visitors in the following SSE tags: . For example: Bad visitors won't see my phone number, 555-555-5555 . Note: SSE only will work with HTML.
     * If you have HTML minification enabled, you won't see the SSE tags in your HTML source when it's served through CloudFlare. SSE will still function in this case, as CloudFlare's HTML minification and SSE functionality occur on-the-fly as the resource moves through our network to the visitor's computer.
     * (https://support.cloudflare.com/hc/en-us/articles/200170036)
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: on)
     */
    public function change_server_side_exclude($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/server_side_exclude', $data);
    }

    /**
     * Change SSL setting (permission needed: #zone_settings:edit)
     * SSL encrypts your visitor's connection and safeguards credit card numbers and other personal data to and from your website. SSL can take up to 5 minutes to fully activate.
     * Requires CloudFlare active on your root domain or www domain.
     * Off: no SSL between the visitor and CloudFlare, and no SSL between CloudFlare and your web server (all HTTP traffic).
     * Flexible: SSL between the visitor and CloudFlare -- visitor sees HTTPS on your site, but no SSL between CloudFlare and your web server. You don't need to have an SSL cert on your web server, but your vistors will still see the site as being HTTPS enabled.
     * Full: SSL between the visitor and CloudFlare -- visitor sees HTTPS on your site, and SSL between CloudFlare and your web server. You'll need to have your own SSL cert or self-signed cert at the very least.
     * Full (Strict): SSL between the visitor and CloudFlare -- visitor sees HTTPS on your site, and SSL between CloudFlare and your web server. You'll need to have a valid SSL certificate installed on your web server. This certificate must be signed by a certificate authority, have an expiration date in the future, and respond for the request domain name (hostname).
     * (https://support.cloudflare.com/hc/en-us/articles/200170416)
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: off)
     */
    public function change_ssl($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/ssl', $data);
    }

    /**
     * Change TLS Client Auth setting (permission needed: #zone_settings:edit)
     * TLS Client Auth requires CloudFlare to connect to your origin server using a client certificate (Enterprise Only)
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: off)
     */
    public function change_tls_client_auth($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/tls_client_auth', $data);
    }

    /**
     * Change True Client IP setting (permission needed: #zone_settings:edit)
     * Allows customer to continue to use True Client IP (Akamai feature) in the headers we send to the origin. This is limited to Enterprise Zones.
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: off)
     */
    public function change_true_client_ip_header($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/true_client_ip_header', $data);
    }

    /**
     * Change TLS 1.2 setting (permission needed: #zone_settings:edit)
     * Enable Crypto TLS 1.2 feature for this zone and prevent use of previous versions. This is limited to Enterprise or Business Zones.
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: off)
     */
    public function change_tls_1_2_only($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/true_client_ip_header', $data);
    }

    /**
     * Change Web Application Firewall (WAF) (permission needed: #zone_settings:edit)
     * The WAF examines HTTP requests to your website. It inspects both GET and POST requests and applies rules to help filter out illegitimate traffic from legitimate website visitors.
     * The CloudFlare WAF inspects website addresses or URLs to detect anything out of the ordinary. If the CloudFlare WAF determines suspicious user behavior, then the WAF will "challenge" the web visitor with a page that asks them to submit a CAPTCHA successfully to continue their action.
     * If the challenge is failed, the action will be stopped. What this means is that CloudFlareâ€™s WAF will block any traffic identified as illegitimate before it reaches your origin web server.
     * (https://support.cloudflare.com/hc/en-us/articles/200172016)
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $value           Value of the zone setting (default: off)
     */
    public function change_waf($zone_identifier, $value = null)
    {
        $data = [
            'value' => $value,
        ];

        return $this->patch('zones/'.$zone_identifier.'/settings/waf', $data);
    }
}
