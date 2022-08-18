import { test, expect } from '@playwright/test';

test.describe('Rocket License', () => {
    test('should authenticate rocket license', async ({ page }) => {
        await page.goto('/wp-admin/options-general.php?page=wprocket');
    });
});