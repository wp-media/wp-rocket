import { test, expect, Page } from '@playwright/test';

/**
 * Local deps.
 */
import { read_file, write_to_file, file_exist } from '../../utils/helpers';
import { pageUtils } from '../../utils/page.utils';

let page: Page, page_utils: Object, wp_config: String, file_content: String, reg: RegExp, debug_log_content: String;
const debug_log = 'wp-content/debug.log';

const wpCache = async () => {
    
    test.beforeAll(async ({ browser }) => {
        const context = await browser.newContext();
        page = await context.newPage();

        page_utils = new pageUtils(page);
        wp_config = await read_file('wp-config.php');
    });

    test.afterAll(async ({ browser }) => {
        wp_config = await read_file('wp-config.php');
        file_content = wp_config.replace('?>\n<?php', '');
        await write_to_file('wp-config.php', file_content);

        browser.close;
    });

    
    test('Should add WP_CACHE in wp-config.php single time while having multiple active php tags ', async () => {
    
        /**
         * WPR already deactivated.
         * Remove the define( 'WP_CACHE', true ); // Added by WP Rocket line from the wp-config.php
         */
        reg = /define\(\s(\')WP_CACHE\1.*\s\).+Rocket/im;
        file_content = wp_config.replace(reg, '');

        // Add multiple php tag.
        file_content = file_content.replace('<?php', '<?php\n?>\n<?php');
        await write_to_file('wp-config.php', file_content);
        
        // Activate WPR
        await activateWPR(page_utils)

        // Deactivate WPR
        await deactivateWPR(page, page_utils);
    });
}

const activateWPR = async (page_utils) => {

    // Activate WPR
    await page_utils.goto_plugin();
    await page_utils.toggle_plugin_activation('wp-rocket');

    // Assert that WPR config file is created.
    expect(await file_exist('wp-content/wp-rocket-config/localhost.php')).toBeTruthy();

    // Asserts that WP Rocket writes to wp-content/advanced-cache.php.
    expect(await read_file('wp-content/advanced-cache.php')).not.toBe('');
    
    // WP Rocket write WP_CACHE=true to wp-config.php once
    wp_config = await read_file('wp-config.php');
    reg = /define\(\s(\')WP_CACHE\1+\,\strue.*\s\).+Rocket/mg;
    expect((wp_config.match(reg) || []).length).toBe(1); 

    // Check the /wp-content/wp-debug.log file
    if (await file_exist(debug_log)) {
        debug_log_content = await read_file(debug_log);
        expect((debug_log_content.match(/wp-rocket/mg) || []).length).toBe(0);
    }
}

const deactivateWPR = async (page, page_utils) => {
    // Remove the define( 'WP_CACHE', true ); // Added by WP Rocket line from the wp-config.php and save then deactivate plugin.
    wp_config = await read_file('wp-config.php');
    reg = /define\(\s(\')WP_CACHE\1.*\s\).+Rocket/im;
    file_content = wp_config.replace(reg, '');
    await write_to_file('wp-config.php', file_content);

    // Deactivate WPR
    await page_utils.goto_plugin();
    await page_utils.toggle_plugin_activation('wp-rocket', false);

    // check deactivation notification
    await expect(page.locator('text=Plugin deactivated.')).toBeVisible();

    // domain.php file under wp-content/wp-rocket-config/ removed.
    expect(await file_exist('wp-content/wp-rocket-config/localhost.php')).toBeFalsy();

    // wp-content/advanced-cache.php cleared
    expect(await read_file('wp-content/advanced-cache.php')).toBe('');

    // WP Rocket write WP_CACHE=false to wp-config.php once
    wp_config = await read_file('wp-config.php');
    reg = /define\(\s(\')WP_CACHE\1+\,\sfalse.*\s\).+Rocket/mg;
    expect((wp_config.match(reg) || []).length).toBe(1); 
}

export default wpCache;

