<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;

use WC_Product_Simple;

trait WooTrait {

	private function create_product( $gallery_image_ids = [] ) {
		$product       = new WC_Product_Simple();
		$product_data =
			array(
				'name'          => 'Dummy Product',
				'regular_price' => 10,
				'price'         => 10,
				'sku'           => 'DUMMY SKU',
				'manage_stock'  => false,
				'tax_status'    => 'taxable',
				'downloadable'  => false,
				'virtual'       => false,
				'stock_status'  => 'instock',
				'weight'        => '1.1',
				'gallery_image_ids' => $gallery_image_ids,
			);

		$product->set_props( $product_data );

		$product->save();
		return wc_get_product( $product->get_id() );
	}

}
