import { test, expect } from '@playwright/test';

/**
 * Local deps.
 */
import { toggleSafeModeDisabledOptions } from '../common/safe.mode.disabled.options';
import { deactivationModal } from '../common/deactivation.modal';
import { checkDisabledOptions } from '../common/safe.mode.disabled.options.check';

const safeMode = () => {
    test('should disable specific options on safe mode', async ( { page } ) => {

        await page.goto('/wp-admin/options-general.php?page=wprocket#dashboard');

        // Enable safe mode disabled options.
        await toggleSafeModeDisabledOptions( page );

        const locator = page.locator('#setting-error-settings_updated');
        await expect(locator).toContainText('Settings saved');

        // Engage Deactivation Modal
        await deactivationModal( page );

        // Check #safe_mode
        await page.locator('#safe_mode').check();
        await page.locator( 'label[for=export_settings]' ).click();
        await page.locator('text=Confirm').click();

        await page.goto('/wp-admin/options-general.php?page=wprocket#dashboard');

        const check_options = await checkDisabledOptions(page);
        expect(check_options).toBeFalsy();
    });
}

export default safeMode;