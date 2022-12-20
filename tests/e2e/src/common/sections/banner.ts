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
            'upgrade_btn': this.page.locator('.rocket-promo-cta'),
            'pricing_popup': {
                infinite: this.page.locator('.wpr-Upgrade-Infinite'),
                in_original_price: this.page.locator('.wpr-Upgrade-Infinite .wpr-upgrade-price-regular'),
                in_discount_price:this.page.locator('.wpr-Upgrade-Infinite .wpr-upgrade-prices'),

                plus: this.page.locator('.wpr-Upgrade-Plus'),
                p_original_price: this.page.locator('.wpr-Upgrade-Plus .wpr-upgrade-price-regular'),
                p_discount_price: this.page.locator('.wpr-Upgrade-Plus .wpr-upgrade-prices'),
            }
        };

        this.txt={
            'sl_upgrade_msg': 'Take advantage of Cyber Monday to speed up more websites: get a 30% off for upgrading your license to Plus or Infinite!',
            'sl_discount_percent': "30% off",
            'promo_title': "Cyber Monday promotion is live!",
            pricing_popup:{
                in_original_price:'200',
                in_dicount_price:'140',

                p_original_price:'50',
                p_dicount_price:'35',
            }
            
        };

    }

}