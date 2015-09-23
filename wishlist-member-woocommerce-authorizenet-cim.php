<?php
/*
Plugin Name: Wishlist Member + Woocommerce + Authorize.net CIM
Version: 1.0
Description: Integrates Wishlist Member, Woocommerce Subscriptions, and the Authorize.net CIM gateway
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
    add_options_page( 'WishList Member + Woocommerce Subscriptions + Authorize.net CIM', 'WLM+WC+Auth.net', 'manage_options', 'wishlist-member-woocommerce-subscriptions-authorize-net-cim', 'wlmwac_options_page' );
}

/**
 * Add settings section and fields
 */
function wlmwac_settings_init() {

    register_setting( 'pluginPage', 'wlmwac_settings' );

    add_settings_section(
        'wlmwac_pluginPage_section',
        __( 'WishList Member Settings', 'wishlist-member-woocommerce-subscriptions-authorize-net-cim' ),
        'wlmwac_settings_section_callback',
        'pluginPage'
    );

    add_settings_field(
        'postToUrl',
        __( 'Post To URL', 'wishlist-member-woocommerce-subscriptions-authorize-net-cim' ),
        'postToUrl_render',
        'pluginPage',
        'wlmwac_pluginPage_section'
    );

    add_settings_field(
        'secretWord',
        __( 'Secret Word', 'wishlist-member-woocommerce-subscriptions-authorize-net-cim' ),
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
    <input type="url" name="wlmwac_settings[postToUrl]" value="<?php echo $options['postToUrl']; ?>" size="40">
    <?php
}

/**
 * Print Secret Word field
 */
function secretWord_render() {
    $options = get_option( 'wlmwac_settings' );
    ?>
    <input type="text" name="wlmwac_settings[secretWord]" value="<?php echo $options['secretWord']; ?>">
    <?php
}

/**
 * Print settings description
 */
function wlmwac_settings_section_callback() {
    printf ( __( 'Enter the “Post To URL” and “Secret Word” from the <a href="%s">WishList Integrations page</a>.', 'wishlist-member-woocommerce-subscriptions-authorize-net-cim' ),
        get_admin_url( get_current_blog_id, 'admin.php?page=WishListMember&wl=integration' )
    );
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

/**
 * Hook into the completed order action
 */
add_action( 'woocommerce_thankyou', 'wlmwac_post_to_wlm' );

/**
 * Post the customer information to WishList Member
 */
function wlmwac_post_to_wlm( $order_id ) {

    // get options
    $wlmwac_options = get_option( 'wlmwac_settings' );

    // set the post URL
    $post_URL = $wlmwac_options['postToUrl'];

    // set the secret key
    $secret_word = $wlmwac_options['secretWord'];

    // get order info
    $order = new WC_Order( $order_id );

    // set up levels array
    $new_levels = array();

    // loop through items post each to the postToURL
    foreach ( $order->get_items() as $item ) {
        // get product data
        $product = $order->get_product_from_item( $item );

        // assign to array
        $new_levels[] = $product->sku;
    }

    // check for existing user by email address
    $this_user = get_user_by( 'email', $order->billing_email );

    // add or update members
    if ( ! $this_user ) {
        // check for username conflicts
        if ( username_exists( $order->billing_first_name . $order->billing_last_name ) ) {
            $username = $order->billing_first_name . $order->billing_last_name . $order_id;
        } else {
            $username = $order->billing_first_name . $order->billing_last_name;
        }

        // add new member with these levels
        $member = wlmapi_add_member( array(
            'user_login'        => $username,
            'user_email'        => $order->billing_email,
            'address1'          => $order->billing_address_1,
            'address2'          => $order->billing_address_2,
            'city'              => $order->billing_city,
            'state'             => $order->billing_state,
            'zip'               => $order->billing_postcode,
            'country'           => $order->billing_country,
            'Levels'            => $new_levels
        ));
    } else {
        // add these levels to an existing member
        $member = wlmapi_update_member( $this_user->ID, array(
            'user_email'        => $order->billing_email,
            'Levels'            => $new_levels
        ));
    }

    if ( $member['success'] == 1 ) {
        echo apply_filters( 'wlmwac_success', '<p>You&rsquo;ve been successfully registered. <a href="' . wp_login_url() . '">Log in</a> to access your content.</p>' );
    } else {
        echo '<p>We&rsquo;re sorry&hellip;something went wrong while setting up your account. Please contact us for help.</p>';
    }

}
