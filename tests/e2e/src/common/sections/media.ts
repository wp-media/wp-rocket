import type { Page } from '@playwright/test';

export class media {
	readonly page: Page;
    readonly selectors;
    readonly locators;

    constructor( page: Page ){
        this.page = page;

        this.selectors = {
            'section': '#wpr-nav-media',
            'lazyload': {
                'checkbox': '#lazyload',
                'enable': 'label[for=lazyload]',
            },
            'lazyload_iframes': {
                'checkbox': '#lazyload_iframes',
                'enable': 'label[for=lazyload_iframes]'
            },
            'lazyload_youtube': {
                'checkbox': '#lazyload_youtube',
                'enable': 'label[for=lazyload_youtube]',
            },
            'image_dimensions': {
                'checkbox': '#image_dimensions',
                'enable': 'label[for=image_dimensions]'
            }
        };

        this.locators = {
            'section': this.page.locator(this.selectors.section),
            'lazyload': this.page.locator(this.selectors.lazyload.enable),
            'lazyload_iframes': this.page.locator(this.selectors.lazyload_iframes.enable),
            'lazyload_youtube': this.page.locator(this.selectors.lazyload_youtube.enable),
            'image_dimensions': this.page.locator(this.selectors.image_dimensions.enable)
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
        if (! await this.page.isChecked(this.selectors.lazyload_iframes.checkbox)) {
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
        if (await this.page.isChecked(this.selectors.lazyload.checkbox)) {
            return true;
        }

        if (await this.page.isChecked(this.selectors.lazyload_iframes.checkbox)) {
            return true;
        }

        if (await this.page.isChecked(this.selectors.lazyload_youtube.checkbox)) {
            return true;
        }

        return false;
    }
}