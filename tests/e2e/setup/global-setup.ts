// global-setup.ts
import { chromium, FullConfig } from '@playwright/test';
import { WP_BASE_URL, WP_USERNAME, WP_PASSWORD } from './../wp.config';

async function globalSetup(config: FullConfig) {
  const browser = await chromium.launch();
  const page = await browser.newPage();
  await page.goto(WP_BASE_URL + '/wp-login.php?redirect_to=http%3A%2F%2Flocalhost%3A8889%2Fwp-admin%2F&reauth=1');
  // Fill username & password.
  await page.locator('input[name="log"]').fill(WP_USERNAME, { timeout: 120000 });
  await page.locator('input[name="pwd"]').fill(WP_PASSWORD, { timeout: 120000 });
  // Click login.
  await page.locator('text=Log In').click();

  // Save signed-in state to 'storageState.json'.
  await page.context().storageState({ path: 'tests/e2e/storageState.json' });
  await browser.close();
}

export default globalSetup;