import type { Page } from '@playwright/test';

export class fileOptimization {
	readonly page: Page;
    readonly locators;

    constructor( page: Page ){
        this.page = page;
        
        this.locators = {
            'section': this.page.locator('#wpr-nav-file_optimization'),
            'minify_css': {
                'enable': this.page.locator('label[for=minify_css]'),
                'activate': this.page.locator('text=Activate minify CSS')
            },
            'combine_css': {
                'enable': this.page.locator('label[for=minify_concatenate_css]'),
                'activate': this.page.locator('text=Activate combine CSS')
            },
            'minify_js': {
                'enable': this.page.locator('label[for=minify_js]'),
                'activate': this.page.locator('text=Activate minify JavaScript')
            },
            'combine_js': {
                'enable': this.page.locator('label[for=minify_concatenate_js]'),
                'activate': this.page.locator('text=Activate combine JavaScript')
            },
            'defer_js': {
                'enable': this.page.locator('label[for=defer_all_js]'),
            },
            'delay_js': {
                'enable': this.page.locator('label[for=delay_js]'),
            }
        }
    }

    visit = async () => {
        await this.locators.section.click();
    }

    enableMinifiyCss = async () => {
        await this.locators.minify_css.enable.click();
        await this.locators.minify_css.activate.click();
    }

    enableCombineCss = async () => {
        await this.locators.combine_css.enable.click();
        await this.locators.combine_css.activate.click();
    }

    enableMinifyJs = async () => {
        await this.locators.minify_js.enable.click();
        await this.locators.minify_js.activate.click();
    }

    enableCombineJs = async () => {
        await this.locators.combine_js.enable.click();
        await this.locators.combine_js.activate.click();
    }

    enableDeferJs = async () => {
        await this.locators.defer_js.enable.click();
    }

    enableDelayJs = async () => {
        await this.locators.delay_js.enable.click();
    }
}