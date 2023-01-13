import { test, expect } from '@playwright/test';

/**
 * Local deps.
 */
import { file_exist } from '../../utils/helpers';
import { pageUtils } from '../../utils/page.utils';

let config_file_exist:boolean = false;

const deactivation = () => {
    test('should deactivate WP Rocket successfully', async ( { page } ) => {
    
        const page_utils = new pageUtils( page );
 
        await page_utils.visit_page('wp-admin');
        await page_utils.goto_plugin();

        const locator = {
            'deactivate': page.locator( '[aria-label="Deactivate WP Rocket"]' ),
            'select_deactivate': page.locator( 'label[for=deactivate]' ),
        };

        // Deactivate WP Rocket.
        await locator.deactivate.click();

        await locator.select_deactivate.click();
        await page.locator('text=Confirm').click();
        
        if (await page.locator('a:has-text("Force deactivation")').isVisible()) {
            // Force deactivation - No .Htaccess file.
            await page.locator('a:has-text("Force deactivation")').click();
        }

        // check deactivation notification
        await expect(page.locator('text=Plugin deactivated.')).toBeVisible();

        config_file_exist = await file_exist('wp-content/wp-rocket-config/localhost.php');
        expect(config_file_exist).toBeFalsy();
    });
}

export default deactivation;