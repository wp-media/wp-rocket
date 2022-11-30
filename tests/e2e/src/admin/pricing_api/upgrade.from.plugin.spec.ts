import { expect, test } from '@playwright/test';

/**
 * Local deps.
 */
import { banner } from '../../common/sections/banner';
import { pageUtils } from '../../../utils/page.utils';

// These tests assume that Campaign is already active 

const upgradeBanner = () => {
    let page;
    let bannerObj;
    let isCampaignActive = true;
 
   

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


    if(isCampaignActive){
        test('Should display upgrade banner', async () => {

            await expect(bannerObj.locators.modal).toBeVisible();

        });

        test('Should display upgrade banner discount to single license', async () => {

            // Assert that banner displayed with valid discount is displayed
            await expect(bannerObj.locators.promo_dicount).toBeVisible();
            await expect(bannerObj.locators.promo_dicount).toContainText(bannerObj.txt.sl_discount_percent);

        });


        test('Should display upgrade banner title to single license', async () => {

            // Assert that banner displayed with valid msg title
            await expect(bannerObj.locators.promo_title).toBeVisible();
            await expect(bannerObj.locators.promo_title).toContainText(bannerObj.txt.promo_title);


        });

        test('Should display upgrade banner message to single license', async () => {

            // Assert that banner displayed with valid msg title
            await expect(bannerObj.locators.promo_message).toBeVisible();
            await expect(bannerObj.locators.promo_message).toContainText(bannerObj.txt.sl_upgrade_msg);

        });
    }


// Banner isnot displayed as campaign isnot active
    else{
         test('Shouldnot display upgrade banner while no active campaign', async () => {

         await expect(page.locator("#rocket-promo-banner")).toBeHidden();

        });
    }

}
export default upgradeBanner;
