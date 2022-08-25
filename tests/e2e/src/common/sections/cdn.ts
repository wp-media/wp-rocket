import type { Page } from '@playwright/test';

export class cdn {
	readonly page: Page;
    readonly locators;

    constructor( page: Page ){
        this.page = page;
        this.locators = {
            'section': this.page.locator('#wpr-nav-page_cdn'),
            'cdn': this.page.locator('label[for=cdn]'),
        }
    }

    visit = async () => {
        await this.locators.section.click();
    }

    /**
     * Toggle CDN option. 
     */
    toggleCDN = async () => {
        await this.locators.cdn.click();
    }

    /**
     * Return default: false when no option in section is enabled
     * 
     * @returns bool
     */
     checAnyEnabledOption = async () => {
        if (await this.page.isChecked('#cdn')) {
            return true;
        }

        return false;
    }
}