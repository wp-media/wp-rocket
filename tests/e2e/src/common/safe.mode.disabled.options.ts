import { Page } from '@playwright/test';

/**
 * Local deps.
 */
import { fileOptimization } from '../common/sections/file.optimization';
import { media as Media } from './sections/media';
import { cdn as CDN } from './sections/cdn';
import { save_settings } from '../../utils/helpers';

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
    await fileOpt.toggleDeferJs();
    // Enable Delay Js.
    await fileOpt.toggleDelayJs();

    /**
     * Media section.
     */
    const media = new Media( page );
    await media.visit();

    // Enable Lazy Load.
    await media.toggleLazyLoad();
    // Enable Lazy Load for Iframes.
    await media.toggleLazyLoadIframes();
    // Enable Lazy Load for Iframe Youtube.
    await media.toggleLazyLoadyoutube();
    // Enable Image Dimension.
    await media.toggleImageDimension();

    /**
     * CDN Section
     */
     const cdn = new CDN( page );
     await cdn.visit();
 
     // Enable CDN.
     await cdn.toggleCDN();

     // save settings
     await save_settings(page);
}









