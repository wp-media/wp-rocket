import { test, expect } from '@playwright/test';

/**
 * Local deps.
 */
import { pageUtils } from '../../../utils/page.utils';
import { save_settings } from '../../../utils/helpers';
import { fileOptimization } from '../../common/sections/file.optimization';

const delayJs = () => {
    test('Should not activate DelayJS automatically and not exclude any patterns for the new installs', async ( { page } ) => {

        const page_utils = new pageUtils( page );
        const fileOpt = new fileOptimization( page );

        // Visit WPR settings
        await page_utils.goto_wpr();

        // Goto file optimization section
        await fileOpt.visit();

        // Assert that delayjs is not enabled.
        expect(await page.isChecked(fileOpt.selectors.delay_js.checkbox)).toBeFalsy();

        // Assert that there is no exclusion.
        await checkForNoExclusion(page, fileOpt);

        // Reload page
        await page.reload();

        // Assert that delayjs is not enabled after refresh.
        expect(await page.isChecked(fileOpt.selectors.delay_js.checkbox)).toBeFalsy();

        // Assert that there is still no exclusion after refresh.
        await checkForNoExclusion(page, fileOpt);
    });
    
    test('Should not display explanatory text about default exclusion if it was already there', async ( { page } ) => {

        const config = {
            'default_exclusion': '/jquery-?[0-9.](.*)(.min|.slim|.slim.min)?.js\njs-(before|after)\n(?:/wp-content/|/wp-includes/)(.*)',
            'texts': {
                'default': 'If you have problems after activating this option, copy and paste the default exclusions to quickly resolve issues:',
                'after_exclusion': 'Internal scripts are excluded by default to prevent issues.',
            },
        };
    
        const page_utils = new pageUtils( page );
        const fileOpt = new fileOptimization( page );
 
        // Visit WPR settings
        await page_utils.goto_wpr();

        // Goto file optimization section
        await fileOpt.visit();

        // Check delayjs
        await fileOpt.toggleDelayJs();

        // Test with default exclusion.
        await addDefaultExclusions(page, config);

        // Test with Added exclusion to default.
        await addToDefaultExclusions(page, config);

        // Test with page reload.
        await reloadpage(page, config);

        // Test with exclusions removed.
        await removeExclusion(page, config);
    });

    test('Should handle the field sanitization correctly', async ( { page } ) => {
        const config = {
            'exclusions': 'https://www.wp-media.me \nhttps://newer.rocketlabsqa.ovh/ \nhttps://newer.rocketlabsqa.ovh/wp-content/js.css \nc \n\n/wp-content/js.css',
            'expected': 'https://www.wp-media.me\nhttps://newer.rocketlabsqa.ovh/\nhttps://newer.rocketlabsqa.ovh/wp-content/js.css\nc\n/wp-content/js.css',
        };

        const page_utils = new pageUtils( page );
        const fileOpt = new fileOptimization( page );
 
        // Visit WPR settings
        await page_utils.goto_wpr();

        // Goto file optimization section
        await fileOpt.visit();

        // Add new exclusions
        await page.locator('#delay_js_exclusions').fill(config.exclusions);
        await save_settings(page);

        // Assert that empty line is removed
        const exclusions = await page.inputValue('#delay_js_exclusions');
        expect(exclusions).toBe(config.expected);
    });
}

/**
 * Add default exclusions.
 * @param page Object
 * @param config Object
 */
const addDefaultExclusions = async (page, config) => {
    // Add default exclusions
    await page.locator('#delay_js_exclusions').fill(config.default_exclusion);

    // Save settings
    await save_settings(page);

    // Perform Assertion
    await expect(page.locator('text=' + config.texts.after_exclusion)).toBeVisible();
}

/**
 * Add to the default exclusions.
 * @param page Object
 * @param config Object
 */
const addToDefaultExclusions = async (page, config) => {
    // Add default exclusions
    await page.locator('#delay_js_exclusions').fill(config.default_exclusion + '\n/custom.js');

    // Save settings
    await save_settings(page);

    // Perform Assertion
    await expect(page.locator('text=' + config.texts.after_exclusion)).toBeVisible();
}

/**
 * Reload page.
 * @param page Object
 * @param config Object
 */
const reloadpage = async (page, config) => {
    await page.reload();

    // Perform Assertion
    await expect(page.locator('text=' + config.texts.after_exclusion)).toBeVisible();
}

/**
 * Remove exclusions.
 * @param page Object
 * @param config Object
 */
const removeExclusion = async (page, config) => {

    // Remove exclusions.
    await page.locator('#delay_js_exclusions').fill('');

    // Save settings
    await save_settings(page);

    // Perform Assertion
    await expect(page.locator('text=' + config.texts.default)).toBeVisible();
}

/**
 * Assert that there is no exclusion
 * @param page Object
 * @param fileOpt Object
 */
const checkForNoExclusion = async (page, fileOpt) => {
    // Check delayjs
    await fileOpt.toggleDelayJs();

    const exclusions = await page.inputValue('#delay_js_exclusions');
    expect(exclusions).toBe('');
}

export default delayJs;