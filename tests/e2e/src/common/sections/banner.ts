import type { Page } from '@playwright/test';

export class banner {
	readonly page: Page;
    readonly locators;
    readonly txt;

    constructor( page: Page ){
        this.page = page;
        this.locators = {
            'promo_title': this.page.locator('//*[@id="rocket-promo-banner"]/div[1]/h3'),
            'promo_message': this.page.locator('.rocket-promo-message'),
            'promo_dicount': this.page.locator('//*[@id="rocket-promo-banner"]/div[1]/h3/span'),
        }
        this.txt={
            'sl_upgrade_txt': 'Take advantage of Cyber Monday to speed up more websites: get a 30% off for upgrading your license to Plus or Infinite!',
            'sl_discount_percent': "30% off",
            'promo_title': "Cyber Monday promotion is live!",
            
        }
    }

}