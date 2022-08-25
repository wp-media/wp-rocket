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

    toggleLazyLoad = async () => {
        await this.locators.lazyload.click();
    }

    toggleLazyLoadIframes = async () => {
        await this.locators.lazyload_iframes.click();
    }

    enableLazyLoadyoutube = async () => {
        if (! await this.page.isChecked('#lazyload_iframes')) {
            return;
        }
        
        await this.locators.lazyload_youtube.click();
        
    }

    toggleImageDimension = async () => {
        await this.locators.image_dimensions.click();
    }
}