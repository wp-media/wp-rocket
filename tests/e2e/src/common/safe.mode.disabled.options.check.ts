import { Page } from '@playwright/test';

/**
 * Local deps.
 */
import { fileOptimization } from '../common/sections/file.optimization';
import { media as Media } from './sections/media';
import { cdn as CDN } from './sections/cdn';

export const checkDisabledOptions = async ( page: Page ) => {

    const fileOpt = new fileOptimization( page );
    const media = new Media( page );
    const cdn = new CDN( page );

    if ( await fileOpt.checkAnyEnabledOption() ) {
        return true;
    }

    if ( await media.checkAnyEnabledOption() ) {
        return true;
    }

    if ( await cdn.checkAnyEnabledOption() ) {
        return true;
    }

    return false;
}









