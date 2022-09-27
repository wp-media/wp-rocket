import type { Page } from '@playwright/test';
import { WP_BASE_URL, WP_USERNAME, WP_PASSWORD } from '../wp.config';

export class pageUtils {
	readonly page: Page;

    constructor( page: Page ){
        this.page = page;
    }

    wp_admin_login = async () => {
        // Fill username & password.
        await this.page.locator('#user_login').waitFor({ state: "attached" });
        await this.page.locator('#user_login').fill(WP_USERNAME);
        await this.page.locator('#user_pass').waitFor({ state: "attached" });
        await this.page.locator('#user_pass').fill(WP_PASSWORD);

        // Click login.
        await this.page.locator('text=Log In').click();
    }

    visit_page = async ( page_url: String ) => {
        await this.page.goto(WP_BASE_URL + '/' + page_url);
    }
}