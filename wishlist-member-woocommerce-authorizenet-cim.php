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

    // set up curl connection
    $ch = curl_init( $post_URL );
    curl_setopt( $ch, CURLOPT_POST, true );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

    // loop through items post each to the postToURL
    foreach ( $order->get_items() as $item ) {
        $product = $order->get_product_from_item( $item );

        // prepare the customer’s data
        $data = array ();
        #TODO: check for existing subscription and ACTIVATE with old transaction_id
        $api_command = 'CREATE';
        $data['cmd'] = $api_command;
        $data['lastname'] = $order->billing_last_name;
        $data['firstname'] = $order->billing_first_name;
        $data['email'] = $order->billing_email;
        $data['level'] = $product->sku;
        $data['transaction_id'] = $order_id;

        // generate the hash
        $delimited_data = strtoupper ( implode ( '|', $data ) );
        $hash = md5( $api_command . '__' . $secret_word . '__' . $delimited_data );

        // include the hash to the data to be sent
        $data['hash'] = $hash;

        // send data to the post URL
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        $returnValue = curl_exec( $ch );

        // process return value
        list( $cmd, $url ) = explode ("\n", $returnValue);

        // check if the returned command is the same as what we passed
        if ( $cmd == 'CREATE' ) {
            echo apply_filters( 'wlmwac_success', '<p>You&rsquo;ve been successfully registered. <a href="' . wp_login_url() . '">Log in</a> to access your content.</p>' );

            // finish registration by pulling the continue link in the background
            #TODO: search for existing WP user and adding this level to their account, or create new user if none exists
            $finish_registration = curl_init( $url );
            curl_setopt( $finish_registration, CURLOPT_RETURNTRANSFER, true );
            curl_exec( $finish_registration );
            curl_close( $finish_registration );
        } else {
            echo '<p>We&rsquo;re sorry&hellip;something went wrong while setting up your account. Please contact us for help.</p>';
        }
    }

    // close curl connection
    curl_close( $ch );

}
