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

    goto_new_post = async () => {
        await this.page.goto(WP_BASE_URL + '/wp-admin/post-new.php');
    }

    add_post_title = async (title: string, is_gutenberg = true) => {
        if (!is_gutenberg) {
            await this.page.locator('#title').fill(title);
            return;
        }

        await this.page.locator('[aria-label="Add title"]').fill(title);
    }

    save_draft = async (is_gutenberg = true) => {
        if (!is_gutenberg) {
            await this.page.locator('#save-post').click();
            return;
        }

        await this.page.locator('[aria-label="Save draft"]').click();
    }

    close_gutenberg_dialog = async () => {
        await this.page.locator('[aria-label="Close dialog"]').click();
    }

    draft_posts = async () => {
        await this.page.goto(WP_BASE_URL + '/wp-admin/edit.php?post_status=draft&post_type=post');
    }

    post_preview = async () => {
        await this.page.locator('button:has-text("Preview")').click();
        await this.page.locator('text=Preview in new tab').click();
    }

    wpr_dropdown = async () => {
        await this.page.locator('#wp-admin-bar-wp-rocket').hover();
    }

    activate_plugin = async (plugin_slug: string) => {
        await this.page.locator('#activate-' + plugin_slug).click();
    }
}