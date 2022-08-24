import { test, expect } from '@playwright/test';
import { WP_BASE_URL } from '../../wp.config';

import { pageUtils } from '../../utils/page.utils';

test.describe('WPR Deactivation Modal', () => {
    test('should pop up deactivation modal', async ( { page } ) => {

        const page_utils = new pageUtils( page );

        await page_utils.visitPage( 'wp-admin' );

        const plugin_menu = '#menu-plugins';

        const locator = {
            'plugin': page.locator( plugin_menu ),
            'deactivate': page.locator( '[aria-label="Deactivate WP Rocket"]' ),
            'safe_mode': page.locator( '#safe_mode' ),
        };

        await page.waitForSelector( plugin_menu )
        // Expect plugins link to be in view.
        await expect( locator.plugin ).toBeVisible();

        // Navigate to plugins page.
        await locator.plugin.click();
        await expect( page ).toHaveURL( WP_BASE_URL + '/wp-admin/plugins.php' );

        // Expect WPR to be active: Deactivate link to be visible.
        await expect( locator.deactivate ).toBeVisible();
        await locator.deactivate.click();
        
        // Expect Deactivation Modal to be visible
        await expect( locator.safe_mode ).toBeVisible();
    });
});