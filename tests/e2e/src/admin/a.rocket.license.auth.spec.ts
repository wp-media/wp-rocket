import { test, expect } from '@playwright/test';

test.describe('Rocket License', () => {
    test( 'should validate license if customer is key is correct', async ( { page } ) => {
        await page.goto( '/wp-admin/options-general.php?page=wprocket' );

        const validate_btn = 'text=Validate License';

        const locator = {
            'validate': page.locator( validate_btn ),
            'has_license': page.locator( 'span:has-text("License")' )
        };

        try {
            await page.waitForSelector( validate_btn, { timeout: 5000 } )
            await expect( locator.validate ).toBeVisible();
            // Validate license
            await locator.validate.click();

            // Expect validation to be successful
            await expect(locator.has_license).toBeVisible();
            
        } catch (err) {
            await expect(locator.has_license).toBeVisible();
        }
    });
});