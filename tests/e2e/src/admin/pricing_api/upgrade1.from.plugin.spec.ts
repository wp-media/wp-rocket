import { expect, test } from '@playwright/test';
import { setTimeout } from "timers/promises";
/**
 * Local deps.
 */
import { banner } from '../../common/sections/banner';
import { pageUtils } from '../../../utils/page.utils';
import { write_to_file, read_file} from '../../../utils/helpers';

// These tests assume that Campaign is already active 
var filter:string = 'add_filter( \'pre_http_request\', function( $pre, $parsed_args, $url ) {\
    if ( false !== stripos( $url, \'https://wp-rocket.me/stat/1.0/wp-rocket/user.php\' ) ) {\
        return [\
            \'response\' => [ \'code\' => 200, \'message\' => \'OK\' ],\
            \'body\'     => json_encode( [\
                \'licence_account\'    =>  \'3\' ,\
                \'licence_expiration\' => 1893456000,\
                \'has_one-com_account\' => false,\
                \'date_created\'        => time() - ( DAY_IN_SECONDS * 100 ),\
            ] )\
        ];\
    }\
    return $pre;\
}, 10, 3 );\
';


const upgradeBanner = () => {
    var page;
    let bannerObj ;
    let isCampaignActive = true;

    test.beforeAll(async ({ browser }) => {
        const context = await browser.newContext();
        page = await context.newPage();
        const page_utils = new pageUtils(page);
        // Adjust permalinks 
        await page_utils.adjust_permalinks();
       // await  interceptLicense('single', filter);


       let theme_content = await read_file('wp-content/themes/twentytwentytwo/functions.php');
       // console.log('theme pure >>>>>>>>>' + theme_content)
        theme_content += filter;
        await write_to_file('wp-content/themes/twentytwentytwo/functions.php', theme_content);
       
       console.log('theme after filter>>>>>>>>>>'+theme_content)


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
        // Revert theme functions.php.
        let theme_content = await read_file('wp-content/themes/twentytwentytwo/functions.php');
        theme_content = theme_content.replace(filter, '');
        await write_to_file('wp-content/themes/twentytwentytwo/functions.php', theme_content);
        //console.log('theme after filter>>>>>>>>>>' +theme_content);
        browser.close;
    });

    


    if(isCampaignActive){

        test.only('Should display upgrade banner', async () => {

        await page.locator('#wpr-action-refresh_account').click();

        await setTimeout(10000);

        await expect(page.locator('text="Plus"')).toBeVisible();
        page.pause()
       
        await expect(bannerObj.locators.modal).toBeVisible();

        });

        test('Should display upgrade banner discount to Single license'  , async () => {


            // Assert that banner displayed with valid discount is displayed
            await expect(bannerObj.locators.promo_dicount).toBeVisible();
            await expect(bannerObj.locators.promo_dicount).toContainText(bannerObj.txt.sl_discount_percent);
            console.log('this is the objecct' + bannerObj.txt2[0].license)

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

        test('Should display upgrade popup when click upgrade', async () => {
        
         
            await page.locator('div[role="main"] >> text=Clear and preload cache').click();
            await page.locator('#wpr-action-refresh_account').click();
            //await expect(page.locator('wpr-account-data')).toContainText('Plus');
            await expect(page.locator('text="Plus"')).toBeVisible();
            // Assert that banner displayed with valid msg title
            bannerObj.locators.upgrade_btn.click();
            await expect(bannerObj.locators.pricing_popup.infinite).toBeVisible();
            await expect(bannerObj.locators.pricing_popup.in_original_price).toContainText(bannerObj.txt.pricing_popup.in_original_price);
            await expect(bannerObj.locators.pricing_popup.in_discount_price).toContainText(bannerObj.txt.pricing_popup.in_dicount_price);

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



// const interceptLicense = async (licenseType, filterString) => {
//     switch(licenseType) { 
//         case "single": { 
//             //await write_to_file('wp-content/themes/twentytwentytwo/functions.php', filter );
//             filter = await read_file('wp-content/themes/twentytwentytwo/functions.php');
//             filter += 'add_filter( \'pre_http_request\', function( $pre, $parsed_args, $url ) {\
//                 if ( false !== stripos( $url, \'https://wp-rocket.me/stat/1.0/wp-rocket/user.php\' ) ) {\
//                     return [\
//                         \'response\' => [ \'code\' => 200, \'message\' => \'OK\' ],\
//                         \'body\'     => json_encode( [\
//                             \'licence_account\'    =>  \'3\' ,\
//                             \'licence_expiration\' => 1893456000,\
//                             \'has_one-com_account\' => false,\
//                             \'date_created\'        => time() - ( DAY_IN_SECONDS * 100 ), \
//                         ] )\
//                     ];\
//                 }\
//                 return $pre;\
//             }, 10, 3 );\
//             ';
//             await write_to_file('wp-content/themes/twentytwentytwo/functions.php', filter);
//            break; 
//         } 
//         case "plus": { 
//            //statements; 
//            break; 
//         }    
//         case "infinite": { 
//             //statements; 
//             break; 
//          } 

//         default: { 
//            //statements; 
//            break; 
//         } 
//      } 

// }




