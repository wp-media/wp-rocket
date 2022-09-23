import type { Page } from '@playwright/test';

export class preload {
	readonly page: Page;
    readonly locators;

    constructor( page: Page ){
        this.page = page;
        this.locators = {
            'section': this.page.locator('#wpr-nav-preload'),
            'preload': this.page.locator('label[for=manual_preload]'),
            'preload_link': this.page.locator('label[for=preload_link]'),
        }
    }

    visit = async () => {
        await this.locators.section.click();
    }

    /**
     * Toggle Preload option. 
     */
    togglePreload = async () => {
        await this.locators.preload.click();
    }

    /**
     * Toggle Preload link option. 
     */
    togglePreloadLinks = async () => {
        await this.locators.preload_link.click();
    }
}