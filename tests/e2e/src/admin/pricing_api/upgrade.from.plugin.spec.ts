import { expect, test } from '@playwright/test';

/**
 * Local deps.
 */
import { banner } from '../../common/sections/banner';
import { pageUtils } from '../../../utils/page.utils';
import { write_to_file, read_file, sleep} from '../../../utils/helpers';

var filter:string;


const upgradeBanner = () => {
    var page;
    let bannerObj;
    let isCampaignActive = true;

    if(isCampaignActive){

        test.describe.only('Checking Single License', () => {

            test.beforeAll(async ({ browser }) => {
                let context = await browser.newContext();
                //setUp( 1);
                page = await context.newPage();
                bannerObj = new banner(page);
                const page_utils = new pageUtils(page);
                //Adjust permalinks 
                await page_utils.adjust_permalinks();

                addFilterToTheme (1);

                // Activate WPR if not active.
                await page_utils.goto_plugin();
    
                if (await page.locator('#activate-wp-rocket').isVisible()) {
                    await page_utils.toggle_plugin_activation('wp-rocket');
                }
        
                // Goto WPR settings.
                await page_utils.goto_wpr();
                await page.locator('#wpr-action-refresh_account').click();
                await expect(page.locator('text="Single"')).toBeVisible();
                // refresh to update banner
                await page_utils.goto_wpr();
            });
        
            test.afterAll(async ({ browser }) => {
                removefilterFromTheme();
                browser.close;
            });

            test('Should display upgrade banner with correct data', async () => {
                await expect(bannerObj.locators.modal).toBeVisible();
                // Assert that banner displayed with valid msg title
                await expect(bannerObj.locators.promo_title).toBeVisible();
                await expect(bannerObj.locators.promo_title).toContainText(bannerObj.txt.promo_title);
                // Assert that banner displayed with valid msg title
                await expect(bannerObj.locators.promo_message).toBeVisible();
                await expect(bannerObj.locators.promo_message).toContainText(bannerObj.txt.sl_upgrade_msg);
                // Assert that banner displayed with valid discount is displayed
                await expect(bannerObj.locators.promo_dicount).toBeVisible();
                await expect(bannerObj.locators.promo_dicount).toContainText(bannerObj.txt.sl_discount_percent);    
            });


            test('Should display upgrade popup when click upgrade', async () => {
                // Assert that banner displayed with valid msg title
                bannerObj.locators.upgrade_btn.click();
                await expect(bannerObj.locators.pricing_popup.infinite).toBeVisible();
                await expect(bannerObj.locators.pricing_popup.in_original_price).toContainText(bannerObj.txt.pricing_popup.in_original_price);
                await expect(bannerObj.locators.pricing_popup.in_discount_price).toContainText(bannerObj.txt.pricing_popup.in_dicount_price);
                await expect(bannerObj.locators.pricing_popup.plus).toBeVisible();
                await expect(bannerObj.locators.pricing_popup.p_original_price).toContainText(bannerObj.txt.pricing_popup.p_original_price);
                await expect(bannerObj.locators.pricing_popup.p_discount_price).toContainText(bannerObj.txt.pricing_popup.p_dicount_price);
            });
        
        });


        test.describe('Checking Plus License', () => {

            test.beforeAll(async ({ browser }) => {
                const context = await browser.newContext();
                page = await context.newPage();
                bannerObj = new banner(page);
                const page_utils = new pageUtils(page);

                //Adjust permalinks 
                await page_utils.adjust_permalinks();

                addFilterToTheme (3);
        
                // Activate WPR if not active.
                await page_utils.goto_plugin();
                
                if (await page.locator('#activate-wp-rocket').isVisible()) {
                    await page_utils.toggle_plugin_activation('wp-rocket');
                }
        
                // Goto WPR settings.
                await page_utils.goto_wpr();
                await page.locator('#wpr-action-refresh_account').click();
                await expect(page.locator('text="Plus')).toBeVisible();
                await page_utils.goto_wpr();
        
            });
        
             test.afterAll(async ({ browser }) => {
                removefilterFromTheme();
                browser.close;
            });

            test('Should display upgrade banner with correct data', async () => {

                await expect(bannerObj.locators.modal).toBeVisible();
                // Assert that banner displayed with valid msg title
                await expect(bannerObj.locators.promo_title).toBeVisible();
                await expect(bannerObj.locators.promo_title).toContainText(bannerObj.txt.promo_title);
                // Assert that banner displayed with valid msg title
                await expect(bannerObj.locators.promo_message).toBeVisible();
                await expect(bannerObj.locators.promo_message).toContainText(bannerObj.txt.sl_upgrade_msg);
                // Assert that banner displayed with valid discount is displayed
                await expect(bannerObj.locators.promo_dicount).toBeVisible();
                await expect(bannerObj.locators.promo_dicount).toContainText(bannerObj.txt.sl_discount_percent);

            });


            test('Should display upgrade popup when click upgrade', async () => {

                // Assert that banner displayed with valid msg title
                bannerObj.locators.upgrade_btn.click();
                await expect(bannerObj.locators.pricing_popup.infinite).toBeVisible();
                await expect(bannerObj.locators.pricing_popup.in_original_price).toContainText(bannerObj.txt.pricing_popup.in_original_price);
                await expect(bannerObj.locators.pricing_popup.in_discount_price).toContainText(bannerObj.txt.pricing_popup.in_dicount_price);

            });
            
        
        });

            test.describe('Checking Infinite License', () => {

                test.beforeAll(async ({ browser }) => {
                    const context = await browser.newContext();
                    page = await context.newPage();
                    bannerObj = new banner(page);
                    const page_utils = new pageUtils(page);
    
                    //Adjust permalinks 
                    await page_utils.adjust_permalinks();
    
                    addFilterToTheme (-1);
            
                    // Activate WPR if not active.
                    await page_utils.goto_plugin();
                    
                    if (await page.locator('#activate-wp-rocket').isVisible()) {
                        await page_utils.toggle_plugin_activation('wp-rocket');
                    }
            
                    // Goto WPR settings.
                    await page_utils.goto_wpr();
                    await page.locator('#wpr-action-refresh_account').click();
                    sleep(2000);
                    await page_utils.goto_wpr();
                    await expect(page.locator('text="Infinite"')).toBeVisible();
                });

                test.afterAll(async ({ browser }) => {
                    removefilterFromTheme();
                    browser.close;
                });

                test('Shouldnot display upgrade banner', async () => {
    
                    await expect(page.locator("#rocket-promo-banner")).toBeHidden();
            
                });
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


const setLicense = async (licenseType) => {
    filter = 'add_filter( \'pre_http_request\', function( $pre, $parsed_args, $url ) {\
        if ( false !== stripos( $url, \'https://wp-rocket.me/stat/1.0/wp-rocket/user.php\' ) ) {\
            return [\
                \'response\' => [ \'code\' => 200, \'message\' => \'OK\' ],\
                \'body\'     => json_encode( [\
                    \'licence_account\'    =>  \'' + licenseType + '\' ,\
                    \'licence_expiration\' => 1893456000,\
                    \'has_one-com_account\' => false,\
                    \'date_created\'        => time() - ( DAY_IN_SECONDS * 100 ),\
                ] )\
            ];\
        }\
        return $pre;\
    }, 10, 3 );\
    ';
    return filter;
}

const addFilterToTheme = async (licenseType) => {
    let theme_content = await read_file('wp-content/themes/twentytwentytwo/functions.php');
    setLicense(licenseType);
    console.log('This is a filter Single" >>>>' + filter)
    theme_content += filter;
    await write_to_file('wp-content/themes/twentytwentytwo/functions.php', theme_content);

    return filter;
}

const removefilterFromTheme = async () => {
   // Revert theme functions.php.
   let theme_content = await read_file('wp-content/themes/twentytwentytwo/functions.php');
   theme_content = theme_content.replace(filter, '');
   await write_to_file('wp-content/themes/twentytwentytwo/functions.php', theme_content);
   console.log('theme after filter>>>>>>>>>>' +theme_content);
    return ;
}

// const setUp = (async (license) => {
//     page = await context.newPage();
//     const page_utils = new pageUtils(page);
//     //Adjust permalinks 
//     await page_utils.adjust_permalinks();
//     addFilterToTheme(license);
//     // Activate WPR if not active.
//     await page_utils.goto_plugin();

//     if (await page.locator('#activate-wp-rocket').isVisible()) {
//         await page_utils.toggle_plugin_activation('wp-rocket');
//     }

//     // Goto WPR settings.
//     await page_utils.goto_wpr();
//     await page.locator('#wpr-action-refresh_account').click();
//     await expect(page.locator('text="Single"')).toBeVisible();
//     bannerObj = new banner(page);

// return {page, bannerObj};
    

// });




