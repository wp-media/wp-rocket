import type { Page } from '@playwright/test';

export class fileOptimization {
	readonly page: Page;
    readonly wpr_page: String;
    readonly locators;

    constructor( page: Page ){
        this.page = page;
        this.wpr_page = 'wp-admin/options-general.php?page=wprocket#file_optimization';
        this.locators = {
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
        await this.page.goto( '/' + this.wpr_page );
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