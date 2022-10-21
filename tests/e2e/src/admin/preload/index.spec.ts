import { test, expect } from '@playwright/test';

/**
 * Local deps.
 */
import { preload } from '../../common/sections/preload';
import { dashboard } from '../../common/sections/dashboard';
import { save_settings } from '../../../utils/helpers';
import { pageUtils } from '../../../utils/page.utils';

const Preload = () => {
    test('Should display preload trigger msg', async ( { page } ) => {

        await page.goto('/wp-admin/options-general.php?page=wprocket#dashboard');

        const data = {
            'preload': new preload(page),
            'dashboard': new dashboard(page),
            'page_utils': new pageUtils(page),
            'locator': page.locator('#rocket-notice-preload-processing'),
            'expected': 'The preload service is now active'
        };

        await when_preload_enabled(page, data);
        await when_clear_cache_and_preload_admin_bar(page, data);
        await when_clear_cache_and_preload_settings(page, data);
    });

    test('Should display single option to preload and clear cache from admin bar / dashboard', async ( { page } ) => {
        const data = {
            'preload': new preload(page),
            'dashboard': new dashboard(page),
            'page_utils': new pageUtils( page ),
        };

        await data.page_utils.visit_page('wp-admin');
        await page.locator('#wp-admin-bar-wp-rocket').hover();

        // Assert that clear and preload cache option is in the admin bar dropdown.
        await expect(page.locator('#wp-admin-bar-purge-all a:has-text("Clear and preload cache")')).toBeVisible();

        await data.page_utils.goto_wpr();
        // Assert that clear and preload cache option is on WPR Dashboard.
        await expect(page.locator('.wpr-field a:has-text("Clear and preload cache")')).toBeVisible();

        // Disable preload
        await data.preload.visit();
        await data.preload.togglePreload();
        await save_settings(page);

        await page.locator('#wp-admin-bar-wp-rocket').hover();

        // Assert that clear cache option is in the admin bar dropdown.
        await expect(page.locator('#wp-admin-bar-purge-all a:has-text("Clear cache")')).toBeVisible();

        await data.page_utils.goto_wpr();
        // Assert that clear cache option is on WPR Dashboard.
        await expect(page.locator('.wpr-field a:has-text("Clear cache")')).toBeVisible();
    });
}

const when_preload_enabled = async (page, data) => {
    await data.preload.visit();
    // Disable preload
    await data.preload.togglePreload();
    await save_settings(page);

    // Re-enable
    await data.preload.togglePreload();
    await save_settings(page);

    await expect(data.locator).toContainText(data.expected);
}

const when_clear_cache_and_preload_admin_bar = async (page, data) => {
    await data.page_utils.wpr_dropdown();
    await page.locator('#wp-admin-bar-purge-all a').click();
    await expect(data.locator).toContainText(data.expected);
}

const when_clear_cache_and_preload_settings = async (page, data) => {
    await data.dashboard.visit();
    await data.dashboard.clearCacheAndPreload();

    await expect(data.locator).toContainText(data.expected);
}

export default Preload;
