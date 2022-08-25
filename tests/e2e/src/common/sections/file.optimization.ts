import type { Page } from '@playwright/test';

export class fileOptimization {
	readonly page: Page;
    readonly selectors;
    readonly locators;

    constructor( page: Page ){
        this.page = page;

        this.selectors = {
            'minify_css': {
                'enable': 'label[for=minify_css]',
                'activate': 'text=Activate minify CSS'
            },
            'combine_css': {
                'enable': 'label[for=minify_concatenate_css]',
                'activate': 'text=Activate combine CSS'
            },
            'minify_js': {
                'enable': 'label[for=minify_js]',
                'activate': 'text=Activate minify JavaScript'
            },
            'combine_js': {
                'enable': 'label[for=minify_concatenate_js]',
                'activate': 'text=Activate combine JavaScript'
            },
            'defer_js': {
                'enable': 'label[for=defer_all_js]',
            },
            'delay_js': {
                'enable': 'label[for=delay_js]',
            }
        };
        
        this.locators = {
            'section': this.page.locator('#wpr-nav-file_optimization'),
            'minify_css': {
                'enable': this.page.locator(this.selectors.minify_css.enable),
                'activate': this.page.locator(this.selectors.minify_css.activate)
            },
            'combine_css': {
                'enable': this.page.locator(this.selectors.combine_css.enable),
                'activate': this.page.locator(this.selectors.combine_css.activate)
            },
            'minify_js': {
                'enable': this.page.locator(this.selectors.minify_js.enable),
                'activate': this.page.locator(this.selectors.minify_js.activate)
            },
            'combine_js': {
                'enable': this.page.locator(this.selectors.combine_js.enable),
                'activate': this.page.locator(this.selectors.combine_js.activate)
            },
            'defer_js': {
                'enable': this.page.locator(this.selectors.defer_js.enable),
            },
            'delay_js': {
                'enable': this.page.locator(this.selectors.delay_js.enable),
            }
        }
    }

    visit = async () => {
        await this.locators.section.click();
    }

    enableMinifiyCss = async () => {
        await this.locators.minify_css.enable.click();

        try{
            await this.page.waitForSelector( this.selectors.minify_css.activate, { timeout: 5000 } );
            await this.locators.minify_css.activate.click();
        } catch(error) {
            console.log('Minify CSS is already enabled - Unchecked this option');
            return false;
        }
        
    }

    enableCombineCss = async () => {
        // Bail out when combine css option is unchecked.
        if(!this.enableMinifiyCss()){
            return;
        }

        await this.locators.combine_css.enable.click();
        await this.locators.combine_css.activate.click();
    }

    enableMinifyJs = async () => {
        await this.locators.minify_js.enable.click();

        try{
            await this.page.waitForSelector( this.selectors.minify_js.activate, { timeout: 5000 } );
            await this.locators.minify_js.activate.click();
        } catch(error) {
            console.log('Minify JS is already enabled - Unchecked this option');
            return false;
        }
        
    }

    enableCombineJs = async () => {

        // Bail out when minify js option is unchecked.
        if(!this.enableMinifyJs()){
            return;
        }
        
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