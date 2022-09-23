import type { Page } from '@playwright/test';

export class dashboard {
	readonly page: Page;
    readonly locators;

    constructor( page: Page ){
        this.page = page;
        this.locators = {
            'section': this.page.locator('#wpr-nav-dashboard'),
            'clear_cache': this.page.locator('div[role="main"] >> text=Clear and preload cache'),
        }
    }

    visit = async () => {
        await this.locators.section.click();
    }

    /**
     * Clear cache and preload. 
     */
    clearCacheAndPreload = async () => {
        await this.locators.clear_cache.click();
    }
}