import { test, expect } from '@playwright/test';

/**
 * Local deps.
 */
import { pageUtils } from '../../utils/page.utils';

const miscellaneous = () => {
    test('Should not display clear cache option for non public post', async ( { page } ) => {

        const page_utils = new pageUtils( page );

        // Visit new post page.
        await page_utils.goto_new_post();

        // Assert that clear cache option is not displayed.
        expect(page.locator('#purge-action a:has-text("Clear cache")')).not.toBeVisible();

        // Close Gutenberg tour.
        await page_utils.close_gutenberg_dialog();

        await shouldNotDisplayClearCacheOptionInDraftPostList(page, page_utils);

        await shouldNotDisplayWPROptionInDraftPostDetails(page);

        await shouldNotDisplayPurgeCacheOptionInAdminBarOnDraftPostPreview(page);
    });

    test('Should not display clear cache option for non public post on classic editor', async ( { page } ) => {

        const page_utils = new pageUtils( page );

        // Visit plugins page
        await page_utils.goto_plugin();

        // Activate classic editor
        await page_utils.activate_plugin('classic-editor');
        await expect(page.locator('text=Plugin activated.')).toBeVisible();


        // Visit new post page.
        await page_utils.goto_new_post();

        // Assert that clear cache option is not displayed.
        expect(page.locator('#purge-action a:has-text("Clear cache")')).not.toBeVisible();

        await shouldNotDisplayClearCacheOptionInDraftPostList(page, page_utils, false);

        await shouldNotDisplayWPROptionInDraftPostDetails(page);

        await shouldNotDisplayPurgeCacheOptionInAdminBarOnDraftPostPreview(page, false);
    });
}

const shouldNotDisplayClearCacheOptionInDraftPostList = async (page, page_utils, is_gutenberg = true) => {

    if (is_gutenberg) {
        // Add post title
        await page_utils.add_post_title('Draft Post', is_gutenberg);

        // Save the post as a draft.
        await page_utils.save_draft(is_gutenberg);

        await page.waitForSelector( '[aria-label="Dismiss this notice"]', { timeout: 15000 } );   
    }

    // goto draft posts
    await page_utils.draft_posts();

    // Mouseover draft list item.
    await page.locator('.iedit').hover();

    // Assert that `Clear this cache` option is not visible.
    expect(page.locator('.rocket_purge:has-text("Clear this cache")')).not.toBeVisible();
}

const shouldNotDisplayWPROptionInDraftPostDetails = async (page) => {
    // View draft post details.
    await page.locator('.iedit strong a').click();

    // Assert that WPR option is not visible.
    expect(page.locator('#rocket_post_exclude')).not.toBeVisible();
}

const shouldNotDisplayPurgeCacheOptionInAdminBarOnDraftPostPreview = async (page, is_gutenberg = true) => {
    // Preview draft post
    if (is_gutenberg) {
        await page.locator('button:has-text("Preview")').click();
    }
    const [page1] = await Promise.all([
        page.waitForEvent('popup'),
        page.locator(is_gutenberg ? 'text=Preview in new tab' : '#post-preview').click()
    ]);

    await page1.waitForSelector( '#wp-admin-bar-wp-rocket', { timeout: 5000 } );
    await page1.locator('#wp-admin-bar-wp-rocket').hover();

    // Assert that `Purge this URL` option is not visible.
    expect(page1.locator('text=Purge this URL')).not.toBeVisible();
}

export default miscellaneous;