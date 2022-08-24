import { test, expect } from '@playwright/test';

test.describe('Rocket License', () => {
    test( 'should validate rocket license', async ( { page } ) => {
        await page.goto( '/wp-admin/options-general.php?page=wprocket' );

        await page.waitForSelector( 'text=Validate License' )

        const locator = page.locator( 'text=Validate License' );
        await expect( locator ).toBeVisible();

        // Validate license
        await locator.click();
    });
});