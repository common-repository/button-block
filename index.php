<?php

/**
 * Plugin Name: Button Block
 * Description: Implement multi-functional button
 * Version: 1.1.4
 * Author: bPlugins LLC 
 * Author URI: http://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: button-block
 */
// ABS PATH
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'btn_fs' ) ) {
    register_activation_hook( __FILE__, function () {
        if ( is_plugin_active( 'button-block/index.php' ) ) {
            deactivate_plugins( 'button-block/index.php' );
        }
        if ( is_plugin_active( 'button-block-pro/index.php' ) ) {
            deactivate_plugins( 'button-block-pro/index.php' );
        }
    } );
} else {
    // Constant
    define( 'BTN_VERSION', ( isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.1.3' ) );
    define( 'BTN_DIR_URL', plugin_dir_url( __FILE__ ) );
    define( 'BTN_DIR_PATH', plugin_dir_path( __FILE__ ) );
    define( 'BTN_HAS_FREE', 'button-block/index.php' === plugin_basename( __FILE__ ) );
    define( 'BTN_HAS_PRO', 'button-block-pro/index.php' === plugin_basename( __FILE__ ) );
    if ( !function_exists( 'btn_fs' ) ) {
        function btn_fs() {
            global $btn_fs;
            if ( !isset( $btn_fs ) ) {
                $fsStartPath = dirname( __FILE__ ) . '/freemius/start.php';
                $bSDKInitPath = dirname( __FILE__ ) . '/bplugins_sdk/init.php';
                if ( file_exists( $fsStartPath ) ) {
                    require_once $fsStartPath;
                } else {
                    if ( file_exists( $bSDKInitPath ) ) {
                        require_once $bSDKInitPath;
                    }
                }
                $btnConfig = [
                    'id'                  => '13491',
                    'slug'                => 'button-block',
                    'premium_slug'        => 'button-block-pro',
                    'type'                => 'plugin',
                    'public_key'          => 'pk_8fb5be7805414bb29e5b06c24566a',
                    'is_premium'          => true,
                    'premium_suffix'      => 'Pro',
                    'has_premium_version' => true,
                    'has_addons'          => false,
                    'has_paid_plans'      => true,
                    'trial'               => [
                        'days'               => 7,
                        'is_require_payment' => true,
                    ],
                    'has_affiliation'     => 'all',
                    'menu'                => [
                        'slug'    => 'edit.php?post_type=button-block',
                        'contact' => false,
                        'support' => false,
                    ],
                ];
                $btn_fs = ( file_exists( $fsStartPath ) ? fs_dynamic_init( $btnConfig ) : fs_lite_dynamic_init( $btnConfig ) );
            }
            return $btn_fs;
        }

        btn_fs();
        do_action( 'btn_fs_loaded' );
    }
    require_once BTN_DIR_PATH . 'inc/block.php';
    if ( BTN_HAS_PRO ) {
        if ( btn_fs()->can_use_premium_code() ) {
            require_once BTN_DIR_PATH . 'inc/class-button-block-pro.php';
        }
        if ( function_exists( 'btn_fs' ) ) {
            btn_fs()->add_filter( 'freemius_pricing_js_path', function () {
                return BTN_DIR_PATH . 'inc/freemius-pricing/freemius-pricing.js';
            } );
        }
    }
    require_once BTN_DIR_PATH . 'inc/class-button-block-common.php';
    // if( BTN_HAS_FREE ){
    // 	require_once BTN_DIR_PATH . '/inc/UpgradePage.php';
    // }
    function btnIsPremium() {
        return ( BTN_HAS_PRO ? btn_fs()->can_use_premium_code() : false );
    }

    // Button Block
    if ( !class_exists( 'BTNPlugin' ) ) {
        class BTNPlugin {
            function __construct() {
                add_action( 'wp_ajax_btnPipeChecker', [$this, 'btnPipeChecker'] );
                add_action( 'wp_ajax_nopriv_btnPipeChecker', [$this, 'btnPipeChecker'] );
                add_action( 'admin_init', [$this, 'registerSettings'] );
                add_action( 'rest_api_init', [$this, 'registerSettings'] );
            }

            function btnPipeChecker() {
                $nonce = $_POST['_wpnonce'];
                if ( !wp_verify_nonce( $nonce, 'wp_ajax' ) ) {
                    wp_send_json_error( 'Invalid Request' );
                }
                wp_send_json_success( [
                    'isPipe' => btnIsPremium(),
                ] );
            }

            function registerSettings() {
                register_setting( 'btnUtils', 'btnUtils', [
                    'show_in_rest'      => [
                        'name'   => 'btnUtils',
                        'schema' => [
                            'type' => 'string',
                        ],
                    ],
                    'type'              => 'string',
                    'default'           => wp_json_encode( [
                        'nonce' => wp_create_nonce( 'wp_ajax' ),
                    ] ),
                    'sanitize_callback' => 'sanitize_text_field',
                ] );
            }

        }

        new BTNPlugin();
    }
}