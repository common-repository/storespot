<?php

if ( !class_exists( 'StoreSpot_Events') ) :

class StoreSpot_Events {
	public function __construct() {
		$this->facebook_pixel = new StoreSpot_Facebook_Pixel();
	}

	public function render_facebook_pixel() {
		echo $this->facebook_pixel->pixel_code();
	}

	public function render_facebook_pixel_noscript() {
		echo $this->facebook_pixel->pixel_code_noscript();
	}

	public function get_facebook_content_id($product) {
		return 'stsp_' . $product->get_id();
	}

	public function render_product_view_event() {
		global $post;
		$product = wc_get_product($post->ID);
		$content_type = 'product';

		if ( $product ) {
			if( $product->get_type() == 'variable' ) {
				$content_type = 'product_group';
			}
			$params = array();
			$params['content_ids'] = json_encode( [$this->get_facebook_content_id( $product )] );
			$params['content_type'] = $content_type;
			$params['content_name'] = $product->get_title();
			$params['currency'] = get_woocommerce_currency();
			$params['value'] = $product->get_price();

			$this->facebook_pixel->event_code( 'ViewContent', $params );
		} else {
			return;
		}
	}

	public function render_category_view_event() {
		global $wp_query;

		$products = array_values(array_map(function($item) {
			return wc_get_product($item->ID);
		}, $wp_query->posts));

		$content_type = 'product';
		$product_ids = array();
		foreach ($products as $product) {
			if (!$product) { continue; }
			if ( $product->get_type() == 'variable' ) { continue; }
			$product_ids[] = $this->get_facebook_content_id($product);
		}

		$category_path = wp_get_post_terms( get_the_ID(), 'product_cat' );
		$content_category = array_values( array_map( function($item) {
			return $item->name;
		}, $category_path));
		$content_category_slice = array_slice($content_category, -1);
		$categories = empty( $content_category ) ? '""' : implode( ', ', $content_category );
		$categories = array(
			'name' => array_pop($content_category_slice),
			'categories' => $categories
		);

		$params = array(
			'content_name' 		=> json_encode($categories['name']),
			'content_category' 	=> json_encode($categories['categories']),
			'content_ids' 		=> json_encode($product_ids),
			'content_type' 		=> $content_type
		);

		$this->facebook_pixel->event_code(
			'ViewCategory',
			$params,
			'trackCustom'
		);
	}

	public function render_search_event() {
		$query = get_search_query();
		if ( is_search() && $query !== '') {
			if ( $this->facebook_pixel->check_last_event( 'Search' ) ) {
				return;
			}

			$params = array();
			$params['search_string'] = $query;
			$this->facebook_pixel->event_code( 'Search', $params );
		}
	}

	private function stsp_get_cart_contents( $product, $qty=1 ) {
		$stsp_id = $this->get_facebook_content_id( $product );
		$price = number_format( $product->get_price(), 2) * $qty;

		return [
			'content_ids' 	=> json_encode([ $stsp_id ]),
			'content_type' 	=> 'product',
			'value' 		=> $price,
			'currency'		=> get_woocommerce_currency()
		];
	}

	public function render_add_to_cart_event($key, $product_id, $qty, $variation_id) {
		$item_id = $variation_id == 0 ? $product_id : $variation_id;
		$product = wc_get_product( $item_id );
		$params = $this->stsp_get_cart_contents( $product, $qty );
		$this->facebook_pixel->event_code( 'AddToCart', $params );
	}

	public function render_ajax_add_to_cart_event() {

		ob_start();

		$params = [];
		if( array_key_exists( 'item', $_POST ) ) {
			$product = wc_get_product( $_POST['item'] );
			$params = $this->stsp_get_cart_contents( $product );
		}

		echo $this->facebook_pixel->ajax_event_code( 'AddToCart', $params );

		$pixel = ob_get_clean();
		wp_send_json($pixel);
	}

	public function render_initiate_checkout_event() {
		if ( $this->facebook_pixel->check_last_event( 'InitiateCheckout' ) ) {
			return;
		}

		global $woocommerce;
		$products = $woocommerce->cart->get_cart();
		$product_ids = array();
		$prices = array();
		foreach ( $products as $item ) {
			$product = $item['data'];
			$product_ids[] = $this->get_facebook_content_id( $product );
			$prices[] = number_format( $product->get_price(), 2) * $item['quantity'];
		}

		$params = array();
		$params['content_ids'] = json_encode( $product_ids );
		$params['content_type'] = 'product';
		$params['value'] = array_sum( $prices );
		$params['currency'] = get_woocommerce_currency();
		$params['num_items'] = $woocommerce->cart->get_cart_contents_count();

		$this->facebook_pixel->event_code( 'InitiateCheckout', $params );
	}

	public function render_purchase_event( $order_id ) {
		if ( $this->facebook_pixel->check_last_event('Purchase') ) {
			return;
		}

		$order = wc_get_order( $order_id );
		$product_ids = array();
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			$product_ids[] = $this->get_facebook_content_id( $product );
		}

		$params = array();
		$params['content_ids'] = json_encode( $product_ids );
		$params['content_type'] = 'product';
		$params['value'] = $order->get_total();
		$params['currency'] = get_woocommerce_currency();

		$this->facebook_pixel->event_code( 'Purchase', $params );
	}

	public function custom_jquery_add_to_cart_script(){

		echo "
<!-- StoreSpot Facebook Listener Begin -->
<script type='text/javascript'>
document.addEventListener('DOMContentLoaded', function() {
  jQuery && jQuery(function($){
    $('body').on('adding_to_cart', function(event, data) {
      $.post(
        '?wc-ajax=stsp_add_to_cart_event',
        {item: data.context.dataset.product_id},
        function(fb) { $('head').append(fb); }
      );
    });
  });
}, false);
</script>
<!-- StoreSpot Facebook Listener End -->
		";

	}
}

endif;
