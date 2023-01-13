import { test, expect, Page } from '@playwright/test';

/**
 * Local deps.
 */
import { read_file, write_to_file, file_exist } from '../../utils/helpers';
import { pageUtils } from '../../utils/page.utils';

let page: Page;
let page_utils: pageUtils;
let wp_config:string = '';
let file_content:string = '';
let reg: RegExp;
let debug_log_content:string = '';
let config_file_exist:boolean = false;
let advanced_cache:string = '';
let theme:string = '';
let match:boolean = false;

const debug_log = 'wp-content/debug.log';

const wpCache = async () => {
    
    test.beforeAll(async ({ browser }) => {
        const context = await browser.newContext();
        page = await context.newPage();

        page_utils = new pageUtils(page);
    });

    test.afterAll(async ({ browser }) => {

        browser.close;
    });

    
    test('Should add WP_CACHE in wp-config.php single time while having multiple active php tags', async () => {
    
        /**
         * WPR already deactivated.
         * Remove the define( 'WP_CACHE', true ); // Added by WP Rocket line from the wp-config.php
         */
        await editWpConfig('<?php', '<?php\n?>\n<?php');
        
        // Activate WPR
        await activateWPR()

        // Deactivate WPR
        await deactivateWPR();

        // Revert wp-config file to original state
        await editWpConfig('?>\n<?php', '', true);
    });

    test('Should add WP_CACHE in wp-config .php single time while having active and commented php tags', async () => {
        /**
         * PRECONDITIONS
         * 
         * WPR deactivated
         * Remove the define( 'WP_CACHE', true ); // Added by WP Rocket line from the wp-config.php
         * Add in wp-config.php instead of 1st PHP tag:
         * //<?php
         * // echo 'test <?php';
         */
        await editWpConfig('<?php', '<?php\n//<?php\n// echo \'test <?php\';');

        // Plugin activated successfully
        await activateWPR();

        // Deactivate WPR
        await deactivateWPR();

        // Revert wp-config file to original state
        await editWpConfig('//<?php\n// echo \'test <?php\';', '', true);
    });

    test('Should add/update WP_CACHE in wp-config while having single php tag', async () => {
        /**
        * PRECONDITIONS
        * 
        * WPR deactivated
        * Remove the define( 'WP_CACHE', true ); // Added by WP Rocket line from the wp-config.php
        * no other PHP tag mentioned in wp-config
        */
        await editWpConfig(false, false);

        // Plugin activated successfully
        await activateWPR();

        // Deactivate WPR
        await deactivateWPR();
    });

    test('Should not display warning in site health for those hosts where we set wp-cache to false', async () => {
        /**
         * PRECONDITIONS
         * 
         * Add this filter in functions.php of the active theme add_filter( 'rocket_set_wp_cache_constant', '__return_false' );
         * WPR is installed
         */
        theme = await read_file('wp-content/themes/twentytwentytwo/functions.php');
        theme += '\nadd_filter( \'rocket_set_wp_cache_constant\', \'__return_false\' );';
        await write_to_file('wp-content/themes/twentytwentytwo/functions.php', theme);

        // Activate WPR
        await page_utils.goto_plugin();
        await expect(page.locator("#activate-wp-rocket")).toContainText("Activate");
        await page_utils.toggle_plugin_activation('wp-rocket');

        // WP_cache in wp-config.php is false
        wp_config = await read_file('wp-config.php');
        reg = /define\(\s(\')WP_CACHE\1+\,\sfalse.*\s\).+Rocket/mg;
        match = reg.test(wp_config);
        expect(match).toBeTruthy();

        // Go to site health
        await page_utils.goto_site_health();
        
        // no error related to wp_cache set to false
        await expect(page.locator('span:has-text("WP_CACHE is set to false")')).not.toBeVisible();

        // Revert theme functions.php.
        theme = await read_file('wp-content/themes/twentytwentytwo/functions.php');
        theme = theme.replace('\nadd_filter( \'rocket_set_wp_cache_constant\', \'__return_false\' );', '');
        await write_to_file('wp-content/themes/twentytwentytwo/functions.php', theme);

        // Remove whitespace from wp-config.php.
        reg = /define\(\s(\')WP_CACHE\1.*\s\).*\n+$/im;
        await editWpConfig(reg, 'define( \'WP_CACHE\', false ); // Added by WP Rocket', true);
    });
}

const editWpConfig = async (needle, replacement, revert = false) => {
    wp_config = await read_file('wp-config.php');

    if (!revert) {
        reg = /define\(\s(\')WP_CACHE\1.*\s\).+Rocket/im;
        wp_config = wp_config.replace(reg, '');   
    }

    if (!needle && !replacement) {
        return;
    }

    wp_config = wp_config.replace(needle, replacement);
    await write_to_file('wp-config.php', wp_config);
}

const activateWPR = async () => {

    // Activate WPR
    await page.goto('wp-admin');
    await page_utils.goto_plugin();
    await expect(page.locator("#activate-wp-rocket")).toContainText("Activate");
    await page_utils.toggle_plugin_activation('wp-rocket');

    // Assert that WPR config file is created.
    await page.waitForSelector('#deactivate-wp-rocket');
    config_file_exist = await file_exist('wp-content/wp-rocket-config/localhost.php')
    expect(config_file_exist).toBeTruthy();

    // Asserts that WP Rocket writes to wp-content/advanced-cache.php.
    advanced_cache = await read_file('wp-content/advanced-cache.php')
    expect(advanced_cache).not.toBe('');
    
    // WP Rocket write WP_CACHE=true to wp-config.php once
    wp_config = await read_file('wp-config.php');
    reg = /define\(\s(\')WP_CACHE\1+\,\strue.*\s\).+Rocket/mg;
    match = reg.test(wp_config);
    expect(match).toBeTruthy();

    // Check the /wp-content/wp-debug.log file
    if (await file_exist(debug_log)) {
        debug_log_content = await read_file(debug_log);
        reg = /wp-rocket/mg;
        match = reg.test(debug_log_content);
        expect(match).toBeTruthy();
    }
}

const deactivateWPR = async () => {
    // Remove the define( 'WP_CACHE', true ); // Added by WP Rocket line from the wp-config.php and save then deactivate plugin.
    wp_config = await read_file('wp-config.php');
    reg = /define\(\s(\')WP_CACHE\1.*\s\).+Rocket/im;
    file_content = wp_config.replace(reg, '');
    await write_to_file('wp-config.php', file_content);

    // Deactivate WPR
    await page.goto('wp-admin');
    await page_utils.goto_plugin();
    await expect(page.locator("#deactivate-wp-rocket")).toContainText("Deactivate");
    await page_utils.toggle_plugin_activation('wp-rocket', false);

    // check deactivation notification
    await expect(page.locator('text=Plugin deactivated.')).toBeVisible();

    // domain.php file under wp-content/wp-rocket-config/ removed.
    await page.waitForSelector('#activate-wp-rocket');
    config_file_exist = await file_exist('wp-content/wp-rocket-config/localhost.php');
    expect(config_file_exist).toBeFalsy();

    // wp-content/advanced-cache.php cleared
    advanced_cache = await read_file('wp-content/advanced-cache.php');
    expect(advanced_cache).toBe('');

    // WP Rocket write WP_CACHE=false to wp-config.php once
    wp_config = await read_file('wp-config.php');
    reg = /define\(\s(\')WP_CACHE\1+\,\sfalse.*\s\).+Rocket/mg;
    match = reg.test(wp_config);
    expect(match).toBeTruthy();
}

export default wpCache;