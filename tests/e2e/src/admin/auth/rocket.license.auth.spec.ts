import { test, expect } from '@playwright/test';

test.describe('Rocket License', () => {
    test( 'should validate rocket license', async ( { page } ) => {
        await page.goto( '/wp-admin/options-general.php?page=wprocket' );

        const validate_btn = 'text=Validate License';

        const locator = {
            'validate': page.locator( validate_btn ),
            'has_license': page.locator( 'span:has-text("License")' )
        };

        try {
            await page.waitForSelector( validate_btn )
            await expect( locator.validate ).toBeVisible();
            // Validate license
            await locator.validate.click();
            
        } catch (err) {
            await expect(locator.has_license).toBeVisible();
        }
    });
});