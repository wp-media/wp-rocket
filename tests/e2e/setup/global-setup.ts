// global-setup.ts
import { chromium, FullConfig } from '@playwright/test';

import { pageUtils } from '../utils/page.utils';

export async function globalSetup(config: FullConfig) {
  const browser = await chromium.launch();
  const page = await browser.newPage();

  const page_utils = new pageUtils( page );

  await page_utils.visitPage('wp-login.php');

  await page_utils.wpAdminLogin();

  // Save signed-in state to 'storageState.json'.
  await page.context().storageState({ path: 'tests/e2e/storageState.json' });
  await browser.close();
}

export default globalSetup;