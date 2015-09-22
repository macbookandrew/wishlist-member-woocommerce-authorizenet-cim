<?php
/*
Plugin Name: Wishlist Member + Woocommerce + Authorize.net CIM
Version: 1.0
Description: PLUGIN DESCRIPTION HERE
Author: AndrewRMinion Design
Author URI: https://andrewrminion.com
Plugin URI: http://code.andrewrminion.com/wishlist-member-woocommerce-subscriptions-authorize-net-cim/
Text Domain: wishlist-member-woocommerce-authorizenet-cim
Domain Path: /languages
*/

/**
 * Prevent direct access to this file
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add settings page to menu
 */
add_action( 'admin_menu', 'wlmwac_add_admin_menu' );
add_action( 'admin_init', 'wlmwac_settings_init' );


/**
 * Add options page
 */
function wlmwac_add_admin_menu() {
    add_options_page( 'wishlist-member-woocommerce-subscriptions-authorize-net-cim', 'wishlist-member-woocommerce-subscriptions-authorize-net-cim', 'manage_options', 'wishlist-member-woocommerce-subscriptions-authorize-net-cim', 'wishlist-member-woocommerce-subscriptions-authorize-net-cim_options_page' );
}

/**
 * Add settings section and fields
 */
function wlmwac_settings_init() {

    register_setting( 'pluginPage', 'wlmwac_settings' );

    add_settings_section(
        'wlmwac_pluginPage_section',
        __( 'WishList Member + Woocommerce Subscriptions + Authorize.net CIM', 'wishlist-member-woocommerce-subscriptions-authorize-net-cim' ),
        'wlmwac_settings_section_callback',
        'pluginPage'
    );

    add_settings_field(
        'postToUrl',
        __( 'Settings field description', 'wishlist-member-woocommerce-subscriptions-authorize-net-cim' ),
        'postToUrl_render',
        'pluginPage',
        'wlmwac_pluginPage_section'
    );

    add_settings_field(
        'secretWord',
        __( 'Settings field description', 'wishlist-member-woocommerce-subscriptions-authorize-net-cim' ),
        'secretWord_render',
        'pluginPage',
        'wlmwac_pluginPage_section'
    );

}

/**
 * Print Post To URL field
 */
function postToUrl_render() {
    $options = get_option( 'wlmwac_settings' );
    ?>
    <input type='text' name='wlmwac_settings[postToUrl]' value='<?php echo $options['postToUrl']; ?>'>
    <?php
}

/**
 * Print Secret Word field
 */
function secretWord_render() {
    $options = get_option( 'wlmwac_settings' );
    ?>
    <input type='text' name='wlmwac_settings[secretWord]' value='<?php echo $options['secretWord']; ?>'>
    <?php
}

/**
 * Print settings description
 */
function wlmwac_settings_section_callback() {
    echo __( 'Enter the “Post To URL” and “Secret Word” from the WishList Integrations page', 'wishlist-member-woocommerce-subscriptions-authorize-net-cim' );
}


/**
 * Print form
 */
function wlmwac_options_page() {
    ?>
    <form action="options.php" method="post">

        <h2>Wishlist Member + Woocommerce Subscriptions + Authorize.net CIM</h2>

        <?php
        settings_fields( 'pluginPage' );
        do_settings_sections( 'pluginPage' );
        submit_button();
        ?>

    </form>
    <?php
}

