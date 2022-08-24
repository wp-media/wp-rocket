import { Page } from '@playwright/test';

/**
 * Local deps.
 */
import { fileOptimization } from '../common/sections/file.optimization';
import { media as Media } from './sections/media';

export const enableSafeModeDisabledOptions = async ( page: Page ) => {

    /**
     * File Optimization section
     */
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
}









