import type { Page } from '@playwright/test';
import { WP_BASE_URL, WP_USERNAME, WP_PASSWORD } from '../wp.config';

export class pageUtils {
	readonly page: Page;

    constructor( page: Page ){
        this.page = page;
    }

    wpAdminLogin = async () => {
        // Fill username & password.
        await this.page.locator('input[name="log"]').fill(WP_USERNAME);
        await this.page.locator('input[name="pwd"]').fill(WP_PASSWORD);

        // Click login.
        await this.page.locator('text=Log In').click();
    }

    visitPage = async ( page_url: String ) => {
        await this.page.goto(WP_BASE_URL + '/' + page_url);
    }
}