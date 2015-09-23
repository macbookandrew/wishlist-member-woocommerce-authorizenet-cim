<?php
/*
Plugin Name: Wishlist Member + Woocommerce + Authorize.net CIM
Version: 1.0
Description: Integrates Wishlist Member, Woocommerce, and the Authorize.net CIM gateway
Author: AndrewRMinion Design
Author URI: https://andrewrminion.com
Plugin URI: http://code.andrewrminion.com/wishlist-member-woocommerce-authorize-net-cim/
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
 * Hook into the payment complete action
 */
add_action( 'woocommerce_payment_complete', 'wlmwac_add_user_levels' );

/**
 * Update the customer’s info in WishList Member
 */
function wlmwac_add_user_levels( $order_id ) {

    // get order info
    $order = new WC_Order( $order_id );

    // set up levels array
    $new_levels = array();

    // loop through items to get level IDs and add to the array
    foreach ( $order->get_items() as $item ) {
        $product = $order->get_product_from_item( $item );
        $new_levels[] = $product->sku;
    }

    // check for existing user by email address
    $this_user = get_user_by( 'email', $order->billing_email );

    // add or update members
    if ( $this_user ) {
        // add these levels to an existing member
        $member = wlmapi_update_member( $this_user->ID, array(
            'user_email'        => $order->billing_email,
            'Levels'            => $new_levels
        ));
    } else {
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
    }

    if ( $member['success'] == 1 ) {
        echo apply_filters( 'wlmwac_register_success', '<p>You&rsquo;ve been successfully registered. <a href="' . wp_login_url() . '">Log in</a> to access your content.</p>' );
    } else {
        echo apply_filters( 'wlmwac_register_failure', '<p>We&rsquo;re sorry&hellip;something went wrong while setting up your account. Please contact us for help.</p>' );
    }
}

/**
 * Hook into cancelled/expired subscriptions actions
 */
add_action( 'cancelled_subscription', 'wlmwac_remove_user_levels', 10, 2 );
add_action( 'subscription_end_of_prepaid_term', 'wlmwac_remove_user_levels', 10, 2 );
add_action( 'subscription_expired', 'wlmwac_remove_user_levels', 10, 2 );
add_action( 'subscription_put_on-hold', 'wlmwac_remove_user_levels', 10, 2 );
add_action( 'subscription_trial_end', 'wlmwac_remove_user_levels', 10, 2 );
add_action( 'scheduled_subscription_expiration', 'wlmwac_remove_user_levels', 10, 2 );

/**
 * Remove WLM access on subscription cancellation or expiration
 * @param integer $user_id          ID of the user for whom the subscription was cancelled
 * @param string  $subscription_key Subscription key for the just-cancelled subscription
 */
function wlmwac_remove_user_levels( $user_id, $subscription_key ) {

    // get original order information
    $order_details = WC_Subscriptions_Manager::get_subscription( $subscription_key );
    $product = new WC_Product( $order_details['product_id'] );

    // remove member from level
    $member = wlmapi_remove_member_from_level( $product->get_sku(), $user_id );

    if ( $member['success'] == 1 ) {
        echo apply_filters( 'wlmwac_cancel_success', '<p>Your subscription has been successfully cancelled.</p>' );
    } else {
        echo apply_filters( 'wlmwac_cancel_failure', '<p>We&rsquo;re sorry&hellip;something went wrong while cancelling your subscription. Please contact us for help.</p>' );
    }
}
