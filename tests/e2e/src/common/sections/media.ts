import type { Locator, Page } from '@playwright/test';

export class media {
	readonly page: Page;
    readonly locators;

    constructor( page: Page ){
        this.page = page;
        this.locators = {
            'section': this.page.locator('#wpr-nav-media'),
            'lazyload': this.page.locator('label[for=lazyload]'),
            'lazyload_iframes': this.page.locator('label[for=lazyload_iframes]'),
            'lazyload_youtube': this.page.locator('label[for=lazyload_youtube]'),
            'image_dimensions': this.page.locator('label[for=image_dimensions]')
        }
    }

    visit = async () => {
        await this.locators.section.click();
    }

    enableLazyLoad = async () => {
        await this.locators.lazyload.click();
    }

    enableLazyLoadIframes = async () => {
        await this.locators.lazyload_iframes.click();
    }

    enableLazyLoadyoutube = async () => {
        await this.locators.lazyload_youtube.click();
    }

    enableImageDimension = async () => {
        await this.locators.image_dimensions.click();
    }
}