import type { Page } from '@playwright/test';
import { WP_BASE_URL, WP_USERNAME, WP_PASSWORD } from '../config/wp.config';

export class pageUtils {
	readonly page: Page;
	readonly selectors;
	readonly locators;

    constructor( page: Page ){
        this.page = page;

        this.selectors = {
            'plugins': '#menu-plugins',
        };

        this.locators = {
            'plugin': page.locator( this.selectors.plugins )
        };
    }

    wp_admin_login = async () => {
        // Fill username & password.
        await this.page.waitForSelector( 'text=Log In' );
        await this.page.locator('#user_login').fill(WP_USERNAME);
        await this.page.locator('#user_pass').fill(WP_PASSWORD);

        // Click login.
        await this.page.locator('text=Log In').click();
    }

    visit_page = async ( page_url: String ) => {
        await this.page.goto(WP_BASE_URL + '/' + page_url);
    }

    goto_plugin = async () => {
        await this.page.goto(WP_BASE_URL + '/wp-admin/plugins.php');
    }

    goto_wpr = async () => {
        await this.page.goto(WP_BASE_URL + '/wp-admin/options-general.php?page=wprocket#dashboard');
    }
}