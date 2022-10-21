import { test, expect } from '@playwright/test';

/**
 * Local deps.
 */
import { pageUtils } from '../../../utils/page.utils';
import { fileOptimization } from '../../common/sections/file.optimization';

const deferJs = () => {
    test('Should display the Defer JS UI', async ( { page } ) => {

        const page_utils = new pageUtils( page );
        const fileOpt = new fileOptimization( page );

        let txtarea = '#exclude_defer_js';

        // Visit WPR settings
        await page_utils.goto_wpr();

        // Goto file optimization section
        await fileOpt.visit();

        // Assert that deferjs exclusion textarea is not visible.
        await expect(page.locator(txtarea)).not.toBeVisible();

        // Check defer js option
        await fileOpt.toggleDeferJs();

        // Assert that deferjs exclusion textarea is now visible.
        await expect(page.locator(txtarea)).toBeVisible();

        // Uncheck defer js option
        await fileOpt.toggleDeferJs();

        // Assert that deferjs exclusion textarea is no longer visible.
        await expect(page.locator(txtarea)).not.toBeVisible();
    });
}

export default deferJs;