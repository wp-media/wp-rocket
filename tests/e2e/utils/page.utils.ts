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
        await this.page.click('#user_login');
        await this.page.fill('#user_login', WP_USERNAME);
        await this.page.click('#user_pass');
        await this.page.fill('#user_pass', WP_PASSWORD);

        // Click login.
        await this.page.click('#wp-submit');
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
        if (! await this.page.locator('[aria-label="Close dialog"]').isVisible()) {
            return;
        }
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

    toggle_plugin_activation = async (plugin_slug: string, activate = true) => {
        var action = activate ? '#activate-' : '#deactivate-';
        await this.page.locator(action + plugin_slug).click();

        if (!activate) {
            if (await this.page.locator('a:has-text("Force deactivation")').isVisible()) {
                // Force deactivation - No .Htaccess file.
                await this.page.locator('a:has-text("Force deactivation")').click();
            }
        }
    }

    goto_themes = async () => {
        await this.page.goto(WP_BASE_URL + '/wp-admin/themes.php');
    }

    goto_site_health = async () => {
        await this.page.goto(WP_BASE_URL + '/wp-admin/site-health.php');
    }
}