import { expect, test } from '@playwright/test';

/**
 * Local deps.
 */
import { banner } from '../../common/sections/banner';
import { pageUtils } from '../../../utils/page.utils';



const upgradeBanner = () => {
    let page;
    let bannerObj;
  
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

        bannerObj = new banner(page);
    });

     test.afterAll(async ({ browser }) => {
        browser.close;
    });

    test('Should display upgrade banner', async () => {

        await expect(page.locator("#rocket-promo-banner")).toBeVisible();

    });

    test('Should display upgrade banner discount to single license', async () => {

        // Assert that banner displayed with valid discount
        await expect(bannerObj.locators.promo_dicount).toContainText(bannerObj.txt.sl_discount_percent);

    });


    test('Should display upgrade banner title to single license', async () => {

        // Assert that banner displayed with valid msg title
       await expect(page.locator('text=' + bannerObj.txt.promo_title)).toBeVisible();

    });

    test('Should display upgrade banner message to single license', async () => {

        // Assert that banner displayed with valid msg title
       await expect(page.locator('text=' + bannerObj.txt.sl_upgrade_txt)).toBeVisible();

    });
}

const displayExpectedBanner = (page, txt, locator) => {
    expect(page.locator(locator)).toContainText(txt);

}

export default upgradeBanner;