import { expect, test } from '@playwright/test';

/**
 * Local deps.
 */
//import { banner } from '../../common/sections/banner';
import { pageUtils } from '../../../utils/page.utils';


const upgradeBanner = () => {
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

    test('Should display upgrade banner', async () => {

        await expect(page.locator("#rocket-promo-banner")).toBeVisible();

    });

    // test('Should display upgrade banner discount to single license', async () => {

    //     const bannerObj = new banner(page);

    // // Assert that banner displayed with valid discount
    // const  txt = await page.locator('//*[@id="rocket-promo-banner"]/div[1]/h3/span').textContent();
    //  console.log("discounttttttttttttttttttt is "+txt);

    //  await  displayExpectedBanner(page, bannerObj.txt.sl_discount_percent, bannerObj.locators.promo_dicount);

    // });


    // test('Should display upgrade banner title to single license', async () => {

    //     const dash_board = new dashboard(page);

    // // Assert that banner displayed with valid msg

    //   await  displayExpectedBanner(page, bannerObj.txt.promo_title, bannerObj.locators.promo_title);

    // });
}

const displayExpectedBanner = (page, txt, locator) => {
    expect(page.locator(locator)).toContainText(txt);

}

export default upgradeBanner;