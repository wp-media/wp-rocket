// global-setup.ts
import { chromium } from '@playwright/test';

import { pageUtils } from '../utils/page.utils';

async function globalSetup() {
  const browser = await chromium.launch();
  const page = await browser.newPage();

  const page_utils = new pageUtils( page );

  await page_utils.visit_page('wp-admin');
  await page_utils.wp_admin_login();

  // Save signed-in state to 'storageState.json'.
  await page.context().storageState({ path: 'tests/e2e/storageState.json' });
  await browser.close();
}

export default globalSetup;