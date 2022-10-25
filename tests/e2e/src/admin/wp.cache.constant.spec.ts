import { test, expect, BrowserContext, Page } from '@playwright/test';

/**
 * Local deps.
 */
import { read_file, write_to_file, file_exist } from '../../utils/helpers';
import { pageUtils } from '../../utils/page.utils';

let page: Page, page_utils: Object, wp_config: String, file_content: String, reg: RegExp;

const wpCache = async () => {
    
    test.beforeAll(async ({ browser }) => {
        const context = await browser.newContext();
        page = await context.newPage();

        page_utils = new pageUtils(page);
    });

    test.afterAll(async ({ browser }) => {
        browser.close;
    });

    
    test('Should add WP_CACHE in wp-config.php single time while having multiple active php tags ', async () => {
    
        /**
         * WPR already deactivated.
         * Remove the define( 'WP_CACHE', true ); // Added by WP Rocket line from the wp-config.php
         */
        wp_config = await read_file('wp-config.php');
        reg = /define\(\s(\')WP_CACHE\1.*\s\).+Rocket/im;
        file_content = wp_config.replace(reg, '');

        // Add multiple php tag.
        file_content = file_content.replace('<?php', '<?php\n?>\n<?php');
        await write_to_file('wp-config.php', file_content);
        
        // Activate WPR
        activateWPR(page, page_utils)
    });
}

const activateWPR = async (page, page_utils) => {
    // Activate WPR
    await page_utils.goto_plugin();
    await page.locator('#activate-wp-rocket').click();

    // Assert that WPR config file is created.
    expect(await file_exist('wp-content/wp-rocket-config/localhost.php')).toBeTruthy();

    // Check Advanced Cache
    
}

export default wpCache;

