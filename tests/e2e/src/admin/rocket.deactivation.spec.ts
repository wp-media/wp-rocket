import { test, expect } from '@playwright/test';

/**
 * Local deps.
 */
import { pageUtils } from '../../utils/page.utils';

test.describe('WPR Deactivation', () => {
    test('should deactivate WP Rocket successfully', async ( { page } ) => {
    
        // force deactivation
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
        
        // Force deactivation - No .Htaccess file.
        await page.locator('a:has-text("Force deactivation")').click();

        // check deactivation notification
        await expect(page.locator('text=Plugin deactivated.')).toBeVisible();

        // check wp-rocket-config does not exist
        const allowed_status = [404];

        page.on('response', async (response) => {
            expect(allowed_status).toContain(response.status());
        });

        await page.goto('/wp-content/wp-rocket-config/localhost.php');
    });
});