import { test, expect } from '@playwright/test';

/**
 * Local deps.
 */
 import { pageUtils } from '../../utils/page.utils';

const rocketLicense = () => {
    let page;
    
    test.beforeAll(async ({ browser }) => {
        const context = await browser.newContext();
        page = await context.newPage();
        const page_utils = new pageUtils(page);

        // Activate WPR if not active.
        await page_utils.goto_plugin();
        
        if (await page.locator('#activate-wp-rocket').isVisible()) {
            await page_utils.toggle_plugin_activation('wp-rocket');
        }

        // Goto WPR settings.
        await page_utils.goto_wpr();
    });

    test.afterAll(async ({ browser }) => {
        browser.close;
    });

    test( 'should validate license if customer key is correct', async () => {

        const validate_btn = 'text=Validate License';

        const locator = {
            'validate': page.locator( validate_btn ),
            'has_license': page.locator( 'span:has-text("License")' )
        };

        if (await locator.validate.isVisible()) {
            await page.waitForSelector( validate_btn, { timeout: 5000 } )
            await expect( locator.validate ).toBeVisible();
            // Validate license
            await locator.validate.click();

            // Expect validation to be successful
            await expect(locator.has_license).toBeVisible();
            return;
        }

        // Expect validation to be successful
        await expect(locator.has_license).toBeVisible();
    });

    test( 'Should display preload trigger message on first activation', async () => {
        await expect(page.locator('#rocket-notice-preload-processing')).toContainText('The preload service is now active');
    });
}

export default rocketLicense;