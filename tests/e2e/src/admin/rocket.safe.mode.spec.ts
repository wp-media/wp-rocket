import { test } from '@playwright/test';

/**
 * Local deps.
 */
import { enableSafeModeDisabledOptions } from '../common/safe.mode.disabled.options';
import { deactivationModal } from '../common/deactivation.modal';

test.describe( 'Safe Mode', () => {
    test('should disable specific options on safe mode', async ( { page } ) => {

        await page.goto('/wp-admin/options-general.php?page=wprocket#dashboard');

        // Enable safe mode disabled options.
        await enableSafeModeDisabledOptions( page );

        // Engage Deactivation Modal
        await deactivationModal( page );

        // Check #safe_mode
        await page.locator('#safe_mode').check();

        await page.locator('text=Confirm').click();
    });
});