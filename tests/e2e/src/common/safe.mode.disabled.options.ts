import { Page } from '@playwright/test';

/**
 * Local deps.
 */
import { fileOptimization } from '../common/sections/file.optimization';

export const enableSafeModeDisabledOptions = async ( page: Page ) => {
    const fileOpt = new fileOptimization( page );
    await fileOpt.visit();

    // Enable Minify Css.
    await fileOpt.enableMinifiyCss();
    // Enable Combine Css.
    await fileOpt.enableCombineCss();
    // Enable Minify Js.
    await fileOpt.enableMinifyJs();
    // Enable Combine Js.
    await fileOpt.enableCombineJs();
    // Enable Defer Js.
    await fileOpt.enableDeferJs();
    // Enable Delay Js.
    await fileOpt.enableDelayJs();
}









