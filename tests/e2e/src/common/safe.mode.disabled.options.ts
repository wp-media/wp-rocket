import { Page } from '@playwright/test';

/**
 * Local deps.
 */
import { fileOptimization } from '../common/sections/file.optimization';
import { media as Media } from './sections/media';
import { cdn as CDN } from './sections/cdn';

export const toggleSafeModeDisabledOptions = async ( page: Page ) => {

    /**
     * File Optimization section
     */
    const fileOpt = new fileOptimization( page );
    await fileOpt.visit();

    // Enable Minify Css.
    await fileOpt.toggleMinifyCss();
    // Enable Combine Css.
    await fileOpt.enableCombineCss();
    // Enable Minify Js.
    await fileOpt.toggleMinifyJs();
    // Enable Combine Js.
    await fileOpt.enableCombineJs();
    // Enable Defer Js.
    await fileOpt.enableDeferJs();
    // Enable Delay Js.
    await fileOpt.enableDelayJs();

    /**
     * Media section.
     */
    const media = new Media( page );
    await media.visit();

    // Enable Lazy Load.
    await media.enableLazyLoad();
    // Enable Lazy Load for Iframes.
    await media.enableLazyLoadIframes();
    // Enable Lazy Load for Iframe Youtube.
    await media.enableLazyLoadyoutube();
    // Enable Image Dimension.
    await media.enableImageDimension();

    /**
     * CDN Section
     */
     const cdn = new CDN( page );
     await cdn.visit();
 
     // Enable CDN.
     await cdn.enableCDN();

     // save settings
     await page.locator('#wpr-options-submit').click();
}









