<?php

if ( !class_exists( 'StoreSpot_Facebook_Pixel' ) ) :

class StoreSpot_Facebook_Pixel {
	private $last_event;

	public function __construct() {
		$this->last_event = '';
	}

	public function pixel_code() {
		return sprintf("
<!-- StoreSpot Facebook Integration Begin --->
<script type='text/javascript'>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', %s);
fbq('track', 'PageView');
</script>
<!-- StoreSpot Facebook Integration End --->
",
		self::get_pixel_id()
		);
	}

	public function pixel_code_noscript() {
		return sprintf("
<noscript>
<img height=\"1\" width=\"1\" style=\"display:none\" alt=\"fbpx\"
src=\"https://www.facebook.com/tr?id=%s&ev=PageView&noscript=1\"/>
</noscript>
",
		esc_js(self::get_pixel_id()));
	}

	public function get_pixel_id() {
		$stsp_settings = get_option( 'storespot_settings' );
		if(
			$stsp_settings
			&& array_key_exists( 'pixel_id', $stsp_settings )
			&& array_key_exists( 'pixel_enabled', $stsp_settings )
			&& $stsp_settings['pixel_enabled'] === true
			&& $stsp_settings['pixel_id'] !== null
		) {
			return $stsp_settings['pixel_id'];
		}
		return false;
	}

	public function event_code( $event, $parameters, $method='track' ) {
		$code = sprintf("fbq('%s', '%s', %s)", $method, $event, json_encode( $parameters, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT ) );
		$this->last_event = $event;
		wc_enqueue_js( $code );
	}

	public function ajax_event_code( $event, $parameters, $method='track' ) {
		$code = sprintf("<script>fbq('%s', '%s', %s)</script>", $method, $event, json_encode( $parameters, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT ) );
		$this->last_event = $event;
		return $code;
	}

	public function check_last_event( $event ) {
		return $event === $this->last_event;
	}
}

endif;
