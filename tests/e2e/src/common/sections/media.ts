import type { Page } from '@playwright/test';

export class media {
	readonly page: Page;
    readonly selectors;
    readonly locators;

    constructor( page: Page ){
        this.page = page;

        this.selectors = {
            'lazyload': 'label[for=lazyload]',
            'lazyload_iframes': 'label[for=lazyload_iframes]',
            'lazyload_youtube': 'label[for=lazyload_youtube]',
            'image_dimensions': 'label[for=image_dimensions]'
        };

        this.locators = {
            'section': this.page.locator('#wpr-nav-media'),
            'lazyload': this.page.locator(this.selectors.lazyload),
            'lazyload_iframes': this.page.locator(this.selectors.lazyload_iframes),
            'lazyload_youtube': this.page.locator(this.selectors.lazyload_youtube),
            'image_dimensions': this.page.locator(this.selectors.image_dimensions)
        };
    }

    visit = async () => {
        await this.locators.section.click();
    }

    /**
     * Toggle lazyload option.
     */
    toggleLazyLoad = async () => {
        await this.locators.lazyload.click();
    }

    /**
     * Toggle lazyload iframes option.
     */
    toggleLazyLoadIframes = async () => {
        await this.locators.lazyload_iframes.click();
    }

    /**
     * Toggle replace youtube preview option.
     */
    toggleLazyLoadyoutube = async () => {
        if (! await this.page.isChecked('#lazyload_iframes')) {
            return;
        }
        
        await this.locators.lazyload_youtube.click();
    }

    /**
     * Toggle image dimension option.
     */
    toggleImageDimension = async () => {
        await this.locators.image_dimensions.click();
    }

    /**
     * Return default: false when no option in section is enabled
     * 
     * @returns bool
     */
     checkAnyEnabledOption = async () => {
        if (await this.page.isChecked('#cdn')) {
            return true;
        }

        return false;
    }
}