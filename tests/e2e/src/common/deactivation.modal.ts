import { expect, Page } from '@playwright/test';

/**
 * Local deps.
 */
import { WP_BASE_URL } from '../../config/wp.config';
import { pageUtils } from '../../utils/page.utils';

export const deactivationModal = async ( page: Page ) => {
  const page_utils = new pageUtils( page );

  const locator = {
      'deactivate': page.locator( '[aria-label="Deactivate WP Rocket"]' ),
      'safe_mode': page.locator( '#safe_mode' ),
  };

  await page_utils.visit_page( 'wp-admin' );

  await page.waitForSelector( page_utils.selectors.plugins )
  // Expect plugins link to be in view.
  await expect( page_utils.locators.plugin ).toBeVisible();

  // Navigate to plugins page.
  await page_utils.goto_plugin();
  await expect( page ).toHaveURL( WP_BASE_URL + '/wp-admin/plugins.php' );

  // Expect WPR to be active: Deactivate link to be visible.
  await expect( locator.deactivate ).toBeVisible();
  await locator.deactivate.click();

  // Expect Deactivation Modal to be visible
  await expect( locator.safe_mode ).toBeVisible();
}









