import type { Page } from '@playwright/test';

export class fileOptimization {
	readonly page: Page;
    readonly selectors;
    readonly locators;

    constructor( page: Page ){
        this.page = page;

        this.selectors = {
            'minify_css': {
                'checkbox': '#minify_css',
                'enable': 'label[for=minify_css]',
                'activate': 'text=Activate minify CSS'
            },
            'combine_css': {
                'checkbox': '#minify_concatenate_css',
                'enable': 'label[for=minify_concatenate_css]',
                'activate': 'text=Activate combine CSS'
            },
            'minify_js': {
                'checkbox': '#minify_js',
                'enable': 'label[for=minify_js]',
                'activate': 'text=Activate minify JavaScript'
            },
            'combine_js': {
                'checkbox': '#minify_concatenate_js',
                'enable': 'label[for=minify_concatenate_js]',
                'activate': 'text=Activate combine JavaScript'
            },
            'defer_js': {
                'checkbox': '#defer_all_js',
                'enable': 'label[for=defer_all_js]',
            },
            'delay_js': {
                'checkbox': '#delay_js',
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

    /**
     * Visit section.
     */
    visit = async () => {
        await this.locators.section.click();
    }

    /**
     * Enable Minify css option.
     */
    enableMinifiyCss = async () => {
        await this.locators.minify_css.enable.click();
        await this.locators.minify_css.activate.click();
    }

    /**
     * Toggle minify css option.
     */
    toggleMinifyCss = async () => {
        if(await this.page.isEnabled(this.selectors.combine_css.checkbox)) {
            await this.locators.minify_css.enable.click();
            return;
        }

        await this.enableMinifiyCss();
    }

    /**
     * Enable combine css option.
     */
    enableCombineCss = async () => {
        if(!await this.page.isEnabled(this.selectors.combine_css.checkbox)) {
            return;
        }

        await this.locators.combine_css.enable.click();
        await this.locators.combine_css.activate.click();
    }


    /**
     * Enable minify js option.
     */
    enableMinifyJs = async () => {
        await this.locators.minify_js.enable.click();
        await this.locators.minify_js.activate.click();
    }

    /**
     * Toggle minify js option.
     */
    toggleMinifyJs = async () => {
        if(await this.page.isEnabled(this.selectors.combine_js.checkbox)) {
            await this.locators.minify_js.enable.click();
            return;
        }

        if(await this.page.isChecked(this.selectors.delay_js.checkbox)) {
            return;
        }

        await this.enableMinifyJs();
    }

    /**
     * Enable combine js option.
     */
    enableCombineJs = async () => {
        if(!await this.page.isEnabled(this.selectors.combine_js.checkbox)) {
            return;
        }
        
        await this.locators.combine_js.enable.click();
        await this.locators.combine_js.activate.click();
    }

    /**
     * Toggle defer js option.
     */
    enableDeferJs = async () => {
        await this.locators.defer_js.enable.click();
    }

    /**
     * Toggle delay js option.
     */
    enableDelayJs = async () => {
        await this.locators.delay_js.enable.click();
    }
}