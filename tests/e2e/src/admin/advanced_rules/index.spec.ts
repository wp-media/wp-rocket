import { expect, test } from '@playwright/test';

/**
 * Local deps.
 */
import { advancedRules } from '../../common/sections/advanced.rules';
import { pageUtils } from '../../../utils/page.utils';
import { save_settings } from '../../../utils/helpers';

test.describe('Advanced Rules', () => {
    test('Should display notice if the global exclusion pattern is used in never cache URLs field', async ( { page } ) => {
        const page_utils = new pageUtils(page);
        const advanced_rules = new advancedRules(page);

        // Goto WPR settings.
        await page_utils.goto_wpr();

        // Navigate to Advanced Rules section.
        await advanced_rules.visit();

        // Add pattern
        await advanced_rules.addRule('cache_reject_uri', '/(.*)');
        await save_settings(page);

        // Assert that notice was displayed.
        displayExpectedNotice(page);

        // Add multiple pattern.
        await advanced_rules.addRule('cache_reject_uri', '/test\n/.*\n/test2');
        await save_settings(page);

        // Assert that notice was displayed after adding multiple lines.
        displayExpectedNotice(page);
    });
});

const displayExpectedNotice = (page) => {
    let string = 'Sorry! Adding /(.*) in Advanced Rules > Never Cache URL(s) was not saved';
    expect(page.locator('#setting-error-reject_uri_global_exclusion p')).toContainText(string);
}