<?php
/**
 * Plugin Name: Stellarwise Optimize
 * Plugin URI: https://example.com
 * Description: A simple must-use plugin for WordPress with optimized asset loading.
 * Author: Your Name
 * Version: 1.0
 * Author URI: https://example.com
 */


if (!defined('ABSPATH')) {
    exit;
}


class Prefix_Asset_Optimizer {

    private  $css_priority;
    private  $css_turn_off;
    private  $js_priority;
    private  $js_turn_off;
    private  $preload_assets;

    public function __construct($css_priority = [], $css_turn_off = [], $js_priority = [], $js_turn_off = [], $preload_assets = []) {
        $this->css_priority = $css_priority;
        $this->css_turn_off = $css_turn_off;
        $this->js_priority = $js_priority;
        $this->js_turn_off = $js_turn_off;
        $this->preload_assets = $preload_assets;

        add_filter( 'style_loader_tag', [ $this, 'defer_css_rel_preload' ], 20, 4 );
        add_filter( 'script_loader_tag', [ $this, 'add_defer_to_js' ], 10, 3 );
        add_action( 'wp_head', [ $this, 'add_preload_links' ], 5 );
    }


    public  function defer_css_rel_preload( $html, $handle, $href, $media ) {
        
        if ( ! is_admin() && !empty($this->css_priority )) {

            if ( in_array( $handle, $this->css_priority ) ) {
                $html = '<link fetchpriority="high" class="test-link-2" rel="stylesheet" href="' . esc_url( $href ) . '" as="style" id="' . esc_attr( $handle ) . '" media="' . esc_attr( $media ) . '">';
            } elseif ( !in_array( $handle, $this->css_turn_off, true )){
                $html = '<link class="test-link" rel="stylesheet" href="' . esc_url( $href ) . '" as="style" id="' . esc_attr( $handle ) . '" media="print" onload="this.media=\'all\';this.onload=null;this.rel=\'stylesheet\';">';
            } else {
                $html = '<script>console.log("css - '. $handle . '")</script>';
            }
        }
        return $html;
    }

    public  function add_defer_to_js( $tag, $handle, $src ) {
        


        if ( ! is_admin() && !empty($this->js_priority ) ) {

            $tag = 'wyłączono' . $handle;
            if ( in_array( $handle, $this->js_priority ) ) {
                $tag = '<script fetchpriority="high" src="' . esc_url( $src ) . '" id="' . esc_attr( $handle ) . '"></script>';
            } elseif ( ! in_array( $handle, $this->js_turn_off, true ) ) {
                if ( strpos( $tag, 'defer' ) === false ) {
                    $tag = '<script src="' . esc_url( $src ) . '" id="' . esc_attr( $handle ) . '" defer></script>';
                }
            } elseif ( in_array( $handle, $this->js_turn_off, true )){
                $tag = '<script>console.log("{js - '. $handle . '}")</script>';
            }
        }
        return $tag;
    }

    public  function add_preload_links() {
        if(!empty($this->preload_assets)){
            foreach ( $this->preload_assets as $url => $type ) {
                echo '<link rel="preload" href="' . esc_url( $url ) . '" as="' . esc_attr( $type ) . '" crossorigin="anonymous">' . "\n";
            }
        }
    }
}


// Globalne, dla wszystkich podstron


$css_priority = [];
$css_turn_off = [];
$js_priority = [];
$js_turn_off = [];
$preload_assets = [
    'https://dev.zapachnisci.com.pl/wp-content/uploads/2023/07/logo-zapachnisci-464x154.png' => 'image',
];




$url = $_SERVER['REQUEST_URI'];

if (preg_match('#^/$#', $url) || isset($_GET['woo-share']) && $_GET['woo-share'] === 'Fn9MwFb2QDZIRyAvHdc0hQwBRpTiypC3') {
    
    $css_priority = array_merge($css_priority, [
        'rbit-menu-css',
        'minimog-style',
        'minimog-child-style',
        'zap-clas-hero-css',
        'zap-class-css',
        'minimog-wc-frontend',
        'google-font-jost',
    ]);
    $css_turn_off = array_merge($css_turn_off, [
        'wc-points-and-rewards-blocks-integration',
        'wp-components',
        'woocommerce-paczkomaty-inpost-blocks-integration-frontend',
        'wp-block-library',
        'rank-math',
        'wp-pagenavi',
        'inpost_additional_style',
   
    ]);

    $js_priority = array_merge($js_priority, [
        'cmplz-cookiebanner',
        'jquery', 'jquery-core', 'jquery-migrate'
    ]);
    $js_turn_off = array_merge($js_turn_off, [
        'wc-country-select-js',
        'woo-conditional-payments-js',
        'woocommerce-js-extra',
        'flexible_shipping_notices',
        'perfect-scrollbar',
        // 'minimog-grid-layout',
        'isotope-packery',
        'minimog-swiper-wrapper-js-extra',
        'prdctfltr',
        'selectWoo',
        'minimog-wc-coupon',
        'wc-order-attribution',
        'cwginstock_popup',
        'cwginstock_js',
        'sweetalert2',
        'wc-country-select',
        'wc-country-select-js-extra',
        'minimog-nice-select',
        'minimog-countdown-timer',
        'woo-conditional-payments-js-js-extra',
        'minimog-quantity-button',
        'wc-order-attribution-js-extra',
        'validate'

    ]);

    $preload_assets = array_merge($preload_assets, [
        '/wp-content/uploads/2024/11/perfume-bottle-in-organza-pink-fabric-2023-11-27-05-29-57-utc-1.webp' => 'image',
        '/wp-content/uploads/2024/11/perfume-bottle-in-organza-pink-fabric-2023-11-27-05-29-57-utc-1-mobile-1-1.webp' => 'image'
    ]);

    new Prefix_Asset_Optimizer($css_priority, $css_turn_off, $js_priority, $js_turn_off, $preload_assets);


} elseif (preg_match('#kategoria#', $url)) {
    echo "This is a category page";
} elseif (preg_match('#produkt#', $url)) {
    echo "This is a product page";
} else {
    echo "This is another page";
}


add_action( 'wp_head', 'stellarwise_head', 5 );

function stellarwise_head() {
    
    echo '<link fetchpriority="high" rel="stylesheet" href="/wp-content/uploads/complianz/css/banner-3-optin.css">';
    echo '    
	 <style>

	#cmplz-cookiebanner-container:has(.cmplz-hidden){
		position: fixed;
		width: 100%;
		height: 100%;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: rgba(0, 0, 0, 0.7);
        z-index: 10000;

	}

	html body .cmplz-hidden{
		display: block !important;
         z-index: 10000;
	}
        html body .cmplz-links {
display: none !important;
}

	</style> ';
}