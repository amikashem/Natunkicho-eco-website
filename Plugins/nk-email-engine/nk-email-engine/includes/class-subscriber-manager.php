<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class NK_Subscriber_Manager {

    /**
     * 10X UPGRADE: Initialize system hooks to auto-sync WordPress users.
     */
    public static function init() {
        add_action( 'user_register', array( __CLASS__, 'sync_wp_user' ) );
        add_action( 'profile_update', array( __CLASS__, 'sync_wp_user' ) );
    }

    /**
     * 10X UPGRADE: Instantly grabs user data upon registration/update.
     */
    public static function sync_wp_user( $user_id ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) return;

        $name = trim( $user->first_name . ' ' . $user->last_name );
        if ( empty( $name ) ) $name = $user->display_name ?: $user->user_login;

        $roles = (array) $user->roles;
        $interest = 'subscriber';
        if ( in_array( 'employer', $roles ) ) { $interest = 'employer'; }
        elseif ( in_array( 'candidate', $roles ) || in_array( 'premium', $roles ) ) { $interest = 'candidate'; }
        elseif ( in_array( 'administrator', $roles ) ) { $interest = 'admin'; }

        // Pass to our central subscribe engine (Bypasses double opt-in for verified WP users)
        self::subscribe( $user->user_email, $name, $interest, 'wp_user_sync', false );
    }

    /**
     * Add a new subscriber. Returns array(success, message, subscriber_id).
     * Checks suppression list first — a suppressed/bounced/complained address
     * is never allowed back in silently.
     */
    public static function subscribe( $email, $name, $interests = '', $source = 'website', $double_opt_in = true ) {
        global $wpdb;

        $email = sanitize_email( $email );
        if ( ! is_email( $email ) ) {
            return array( 'success' => false, 'message' => 'Invalid email address.', 'id' => 0 );
        }

        if ( self::is_suppressed( $email ) ) {
            return array( 'success' => false, 'message' => 'This email address cannot be subscribed.', 'id' => 0 );
        }

        $table    = NK_Database::table( 'subscribers' );
        $existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE email = %s", $email ), ARRAY_A );

        $verification_token = wp_generate_password( 32, false );
        $unsubscribe_token  = wp_generate_password( 32, false );
        $status             = $double_opt_in ? 'pending' : 'active';

        if ( $existing ) {
            // Update interests/name if synced from WP
            $new_interests = !empty($interests) ? sanitize_text_field($interests) : $existing['interests'];
            
            if ( 'unsubscribed' === $existing['status'] && $source !== 'wp_user_sync' ) {
                // Re-subscribe manual form: reset to pending/active, refresh tokens.
                $wpdb->update( $table, array(
                    'status'              => $status,
                    'name'                => sanitize_text_field( $name ),
                    'interests'           => $new_interests,
                    'verification_token'  => $verification_token,
                    'unsubscribe_token'   => $unsubscribe_token,
                    'updated_at'          => current_time( 'mysql' ),
                ), array( 'id' => $existing['id'] ) );

                if ( $double_opt_in ) {
                    self::send_verification_email( $email, $name, $verification_token );
                }
                return array( 'success' => true, 'message' => 'Re-subscribed.', 'id' => (int) $existing['id'] );
            } else {
                // Just silently update their name/role if it changed in WP, don't change unsubscribe status
                $wpdb->update( $table, array(
                    'name'       => sanitize_text_field( $name ),
                    'interests'  => $new_interests,
                    'updated_at' => current_time( 'mysql' ),
                ), array( 'id' => $existing['id'] ) );
            }

            return array( 'success' => false, 'message' => 'This email is already subscribed/synced.', 'id' => (int) $existing['id'] );
        }

        $wpdb->insert( $table, array(
            'email'               => $email,
            'name'                => sanitize_text_field( $name ),
            'status'              => $status,
            'source'              => sanitize_text_field( $source ),
            'interests'           => sanitize_text_field( $interests ),
            'verification_token'  => $verification_token,
            'unsubscribe_token'   => $unsubscribe_token,
            'created_at'          => current_time( 'mysql' ),
            'updated_at'          => current_time( 'mysql' ),
        ) );

        $id = $wpdb->insert_id;

        if ( $double_opt_in ) {
            self::send_verification_email( $email, $name, $verification_token );
        }

        return array( 'success' => true, 'message' => $double_opt_in ? 'Please check your email to confirm.' : 'Subscribed successfully.', 'id' => $id );
    }

    public static function verify( $token ) {
        global $wpdb;
        $table = NK_Database::table( 'subscribers' );
        $sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE verification_token = %s", $token ), ARRAY_A );
        if ( ! $sub ) return false;
        $wpdb->update( $table, array( 'status' => 'active', 'updated_at' => current_time( 'mysql' ) ), array( 'id' => $sub['id'] ) );
        return true;
    }

    public static function unsubscribe( $token ) {
        global $wpdb;
        $table = NK_Database::table( 'subscribers' );
        $sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE unsubscribe_token = %s", $token ), ARRAY_A );
        if ( ! $sub ) return false;
        $wpdb->update( $table, array( 'status' => 'unsubscribed', 'updated_at' => current_time( 'mysql' ) ), array( 'id' => $sub['id'] ) );
        return true;
    }

    public static function is_suppressed( $email ) {
        global $wpdb;
        $table = NK_Database::table( 'suppression_list' );
        $row   = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE email = %s", $email ) );
        return ! empty( $row );
    }

    public static function suppress( $email, $reason = 'manual' ) {
        global $wpdb;
        $table = NK_Database::table( 'suppression_list' );

        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE email = %s", $email ) );
        if ( $exists ) return true;

        $wpdb->insert( $table, array(
            'email'      => sanitize_email( $email ),
            'reason'     => sanitize_text_field( $reason ),
            'created_at' => current_time( 'mysql' ),
        ) );

        $sub_table = NK_Database::table( 'subscribers' );
        $wpdb->update( $sub_table, array( 'status' => 'suppressed', 'updated_at' => current_time( 'mysql' ) ), array( 'email' => $email ) );

        return true;
    }

    public static function get_active_by_interest( $interest = '' ) {
        global $wpdb;
        $table = NK_Database::table( 'subscribers' );
        if ( $interest ) {
            return $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$table} WHERE status = 'active' AND interests LIKE %s",
                '%' . $wpdb->esc_like( $interest ) . '%'
            ), ARRAY_A );
        }
        return $wpdb->get_results( "SELECT * FROM {$table} WHERE status = 'active'", ARRAY_A );
    }

    public static function get_all( $status = '', $page = 1, $per_page = 50 ) {
        global $wpdb;
        $table  = NK_Database::table( 'subscribers' );
        $offset = ( max( 1, $page ) - 1 ) * $per_page;
        if ( $status ) {
            return $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$table} WHERE status = %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $status, $per_page, $offset
            ), ARRAY_A );
        }
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page, $offset
        ), ARRAY_A );
    }

    public static function count_by_status() {
        global $wpdb;
        $table = NK_Database::table( 'subscribers' );
        $rows  = $wpdb->get_results( "SELECT status, COUNT(*) AS total FROM {$table} GROUP BY status", ARRAY_A );
        $counts = array();
        foreach ( $rows as $r ) {
            $counts[ $r['status'] ] = (int) $r['total'];
        }
        return $counts;
    }

    private static function send_verification_email( $email, $name, $token ) {
        $confirm_link = add_query_arg( array( 'nk_verify' => $token ), home_url( '/' ) );
        $subject      = 'Please confirm your subscription';
        $body         = sprintf(
            '<p>Hi %s,</p><p>Please confirm your subscription by clicking the link below:</p><p><a href="%s">Confirm Subscription</a></p>',
            esc_html( $name ),
            esc_url( $confirm_link )
        );
        NK_Email_Queue::enqueue( $email, $name, $subject, $body, array( 'priority' => 'high' ) );
    }
}
NK_Subscriber_Manager::init(); // Fire the WP Hooks immediately