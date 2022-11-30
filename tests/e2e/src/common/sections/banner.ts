import type { Page } from '@playwright/test';

export class banner {
	readonly page: Page;
    readonly locators;
    readonly txt;

    constructor( page: Page ){
        this.page = page;

        this.locators = {
            'promo_title': this.page.locator('.rocket-promo-title'),
            'promo_message': this.page.locator('.rocket-promo-message'),
            'promo_dicount': this.page.locator('.rocket-promo-discount'),
            'modal': this.page.locator('#rocket-promo-banner'),
            'upgrade_btn': this.page.locator('.rocket-promo-cta .wpr-popin-upgrade-toggle')
        };

        this.txt={
            'sl_upgrade_msg': 'Take advantage of Cyber Monday to speed up more websites: get a 30% off for upgrading your license to Plus or Infinite!',
            'sl_discount_percent': "30% off",
            'promo_title': "Cyber Monday promotion is live!",
            
        };

    }

}