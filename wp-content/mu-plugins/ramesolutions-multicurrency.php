<?php
/**
 * Plugin Name: Rame Solutions Multi-Currency
 * Description: IP-based multi-currency with live rates via AJAX (to bypass LiteSpeed cache).
 */

defined( 'ABSPATH' ) || exit;

class Rame_Multi_Currency {
	
	private static $base_currency = 'USD';
	
	public static function init() {
		// AJAX endpoint for getting user currency data
		add_action( 'wc_ajax_get_user_currency_data', [ __CLASS__, 'ajax_get_user_currency_data' ] );
		add_action( 'wp_ajax_nopriv_get_user_currency_data', [ __CLASS__, 'ajax_get_user_currency_data' ] );
		add_action( 'wp_ajax_get_user_currency_data', [ __CLASS__, 'ajax_get_user_currency_data' ] );

		// Modify wc_price output to include data attributes on the frontend
		add_filter( 'wc_price', [ __CLASS__, 'add_raw_price_data' ], 99, 4 );

		// Frontend JS to update prices
		add_action( 'wp_footer', [ __CLASS__, 'frontend_js' ] );

		// Change currency dynamically for backend/cart/checkout
		add_filter( 'woocommerce_currency', [ __CLASS__, 'modify_currency' ], 999 );
		
		// Change product prices for cart/checkout based on currency
		add_filter( 'woocommerce_product_get_price', [ __CLASS__, 'modify_product_price' ], 99, 2 );
		add_filter( 'woocommerce_product_get_regular_price', [ __CLASS__, 'modify_product_price' ], 99, 2 );
		add_filter( 'woocommerce_product_get_sale_price', [ __CLASS__, 'modify_product_price' ], 99, 2 );
		add_filter( 'woocommerce_product_variation_get_price', [ __CLASS__, 'modify_product_price' ], 99, 2 );
		add_filter( 'woocommerce_product_variation_get_regular_price', [ __CLASS__, 'modify_product_price' ], 99, 2 );
		add_filter( 'woocommerce_product_variation_get_sale_price', [ __CLASS__, 'modify_product_price' ], 99, 2 );

		// Adjust shipping rates
		add_filter( 'woocommerce_package_rates', [ __CLASS__, 'modify_shipping_rates' ], 99, 2 );
	}

	private static function is_backend_pricing_context() {
		if ( is_admin() && ! wp_doing_ajax() ) return false;
		
		// If we are in cart, checkout, or mini-cart fragment refresh
		if ( is_cart() || is_checkout() || is_checkout_pay_page() ) {
			return true;
		}

		if ( wp_doing_ajax() ) {
			$action = isset( $_REQUEST['wc-ajax'] ) ? $_REQUEST['wc-ajax'] : ( isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '' );
			if ( in_array( $action, [ 'add_to_cart', 'get_refreshed_fragments', 'update_order_review', 'checkout' ] ) ) {
				return true;
			}
		}

		return false;
	}

	public static function modify_currency( $currency ) {
		if ( self::is_backend_pricing_context() ) {
			$user_curr = WC()->session ? WC()->session->get( 'rame_currency' ) : null;
			if ( $user_curr && $user_curr !== self::$base_currency ) {
				return $user_curr;
			}
		}
		return $currency;
	}

	public static function modify_product_price( $price, $product ) {
		if ( ! $price ) return $price;
		if ( self::is_backend_pricing_context() ) {
			$user_curr = WC()->session ? WC()->session->get( 'rame_currency' ) : null;
			if ( $user_curr && $user_curr !== self::$base_currency ) {
				$rate = self::get_exchange_rate( $user_curr );
				if ( $rate ) {
					return (float) $price * $rate;
				}
			}
		}
		return $price;
	}

	public static function modify_shipping_rates( $rates, $package ) {
		if ( self::is_backend_pricing_context() ) {
			$user_curr = WC()->session ? WC()->session->get( 'rame_currency' ) : null;
			if ( $user_curr && $user_curr !== self::$base_currency ) {
				$rate = self::get_exchange_rate( $user_curr );
				if ( $rate ) {
					foreach ( $rates as $rate_id => $shipping_rate ) {
						$rates[ $rate_id ]->cost = $shipping_rate->cost * $rate;
						// Also convert taxes if any
						$taxes = [];
						foreach ( $shipping_rate->taxes as $key => $tax ) {
							$taxes[ $key ] = $tax * $rate;
						}
						$rates[ $rate_id ]->taxes = $taxes;
					}
				}
			}
		}
		return $rates;
	}

	public static function add_raw_price_data( $return, $price, $args, $unformatted_price ) {
		if ( ! self::is_backend_pricing_context() ) {
			if ( ! empty( $unformatted_price ) && is_numeric( $unformatted_price ) ) {
				$return = str_replace( 'class="woocommerce-Price-amount amount"', 'class="woocommerce-Price-amount amount rame-price-convert" data-base-price="' . esc_attr( $unformatted_price ) . '"', $return );
			}
		}
		return $return;
	}

	public static function ajax_get_user_currency_data() {
		// 1. Geolocate
		$country_code = '';
		if ( class_exists( 'WC_Geolocation' ) ) {
			// Get IP
			$ip = WC_Geolocation::get_ip_address();
			$location = WC_Geolocation::geolocate_ip( $ip );
			$country_code = $location['country'] ?? '';
			
			// Fallback to IP-API if WC Geolocation failed or returned empty
			if ( empty( $country_code ) || $country_code === 'A1' || $country_code === 'A2' ) {
				$response = wp_remote_get( 'http://ip-api.com/json/' . $ip );
				if ( ! is_wp_error( $response ) ) {
					$body = json_decode( wp_remote_retrieve_body( $response ), true );
					if ( isset( $body['countryCode'] ) ) {
						$country_code = $body['countryCode'];
					}
				}
			}
		}

		if ( empty( $country_code ) && isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
			$country_code = $_SERVER['HTTP_CF_IPCOUNTRY'];
		}

		$currency = self::get_currency_from_country( $country_code );
		$rate = self::get_exchange_rate( $currency );
		$symbol = get_woocommerce_currency_symbol( $currency );

		// Set in session
		if ( WC()->session ) {
			WC()->session->set( 'rame_currency', $currency );
			WC()->session->set( 'rame_currency_rate', $rate );
		}

		wp_send_json( [
			'country'  => $country_code,
			'currency' => $currency,
			'rate'     => $rate,
			'symbol'   => html_entity_decode( $symbol )
		] );
	}

	private static function get_currency_from_country( $country_code ) {
		$map = [
			'PK' => 'PKR',
			'GB' => 'GBP',
			'US' => 'USD',
			'CA' => 'CAD',
			'AU' => 'AUD',
			'IN' => 'INR',
			'AE' => 'AED',
			'SA' => 'SAR',
			'EU' => 'EUR', // Fallback for Europe
			'DE' => 'EUR',
			'FR' => 'EUR',
			'IT' => 'EUR',
			'ES' => 'EUR',
		];
		return isset( $map[ $country_code ] ) ? $map[ $country_code ] : self::$base_currency;
	}

	private static function get_exchange_rate( $currency ) {
		if ( $currency === self::$base_currency ) return 1;

		$transient_key = 'rame_rates_' . self::$base_currency;
		$rates = get_transient( $transient_key );

		if ( false === $rates ) {
			$response = wp_remote_get( 'https://api.exchangerate-api.com/v4/latest/' . self::$base_currency );
			if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
				$body = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( isset( $body['rates'] ) ) {
					$rates = $body['rates'];
					set_transient( $transient_key, $rates, 12 * HOUR_IN_SECONDS );
				}
			}
		}

		if ( is_array( $rates ) && isset( $rates[ $currency ] ) ) {
			return $rates[ $currency ];
		}

		return 1;
	}

	public static function frontend_js() {
		if ( self::is_backend_pricing_context() ) return; // Cart/checkout rendered in PHP
		?>
		<script>
			document.addEventListener("DOMContentLoaded", function() {
				fetch('/?wc-ajax=get_user_currency_data', {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
				})
				.then(response => response.json())
				.then(data => {
					if (data.currency && data.currency !== '<?php echo esc_js( self::$base_currency ); ?>') {
						const rate = parseFloat(data.rate);
						const symbol = data.symbol;
						
						function convertPrices() {
							document.querySelectorAll('.woocommerce-Price-amount').forEach(function(el) {
								if (el.hasAttribute('data-price-converted')) return;
								
								// If it doesn't have data-base-price
								if (!el.hasAttribute('data-base-price')) {
									let text = el.textContent || el.innerText;
									let rawStr = text.replace(/[^0-9.]/g, '');
									let parsedPrice = parseFloat(rawStr);
									el.setAttribute('data-base-price', parsedPrice);
								}
								
								const basePrice = parseFloat(el.getAttribute('data-base-price'));
								if (!isNaN(basePrice)) {
									const converted = basePrice * rate;
									const formatted = converted.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
									
									let inner = '<bdi><span class="woocommerce-Price-currencySymbol">' + symbol + '</span>' + formatted + '</bdi>';
									el.innerHTML = inner;
									el.setAttribute('data-price-converted', 'true');
								}
							});
						}

						// Run initially
						convertPrices();

						// Watch for dynamically added products (e.g. WooCommerce Blocks on Homepage)
						const observer = new MutationObserver(function(mutations) {
							let shouldConvert = false;
							for (let i = 0; i < mutations.length; i++) {
								if (mutations[i].addedNodes.length > 0) {
									shouldConvert = true;
									break;
								}
							}
							if (shouldConvert) {
								convertPrices();
							}
						});
						observer.observe(document.body, { childList: true, subtree: true });
					}
				})
				.catch(err => console.error('Error fetching currency:', err));
			});
		</script>
		<?php
	}
}

add_action( 'plugins_loaded', [ 'Rame_Multi_Currency', 'init' ] );
