<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class NK_Template_Manager {

    // Internal trackers to hold hijacked emails
    private static $hijacked_subject = '';
    private static $hijacked_html = '';

    /**
     * 10X UPGRADE: Initialize Interceptors
     * Hooks deep into WP Core to catch generic emails before they dispatch.
     */
    public static function init() {
        add_filter( 'wp_new_user_notification_email', array( __CLASS__, 'intercept_welcome_email' ), 10, 3 );
        add_filter( 'retrieve_password_title', array( __CLASS__, 'intercept_password_title' ), 10, 3 );
        add_filter( 'retrieve_password_message', array( __CLASS__, 'intercept_password_message' ), 10, 4 );
        
        // This is the actual net that catches the intercepted email and routes it to Amazon SES/Brevo Queue
        add_filter( 'pre_wp_mail', array( __CLASS__, 'divert_to_queue' ), 10, 2 );
    }

    // ========================================================================
    // 🚀 THE QUEUE DIVERTER
    // ========================================================================
    public static function divert_to_queue( $null, $atts ) {
        if ( self::$hijacked_html !== '' ) {
            // This is a mapped email! Divert it to our high-speed Queue system.
            $to = isset($atts['to']) ? $atts['to'] : '';
            if ( is_array($to) ) { $to = $to[0]; }

            if( class_exists('NK_Email_Queue') ) {
                NK_Email_Queue::enqueue( $to, '', self::$hijacked_subject, self::$hijacked_html, array('priority' => 'high') );
            }

            // Reset our trackers
            self::$hijacked_html = '';
            self::$hijacked_subject = '';

            // Return TRUE to instantly short-circuit and kill the native WP mail process
            return true;
        }
        return null;
    }

    // ========================================================================
    // 📥 WELCOME EMAIL INTERCEPTOR
    // ========================================================================
    public static function intercept_welcome_email( $email_args, $user, $blogname ) {
        $mapped_id = get_option( 'nk_mapped_tpl_welcome' );
        if ( ! $mapped_id ) return $email_args;

        $tpl = self::get( $mapped_id );
        if ( ! $tpl ) return $email_args;

        $vars = array(
            'name'  => $user->first_name ? $user->first_name : $user->user_login,
            'email' => $user->user_email,
        );

        // Save to static properties so pre_wp_mail can catch it
        self::$hijacked_subject = self::render( $tpl['subject'], $vars );
        self::$hijacked_html    = self::render( $tpl['html_content'], $vars );

        return $email_args;
    }

    // ========================================================================
    // 🔐 PASSWORD RESET INTERCEPTOR
    // ========================================================================
    public static function intercept_password_title( $title, $user_login, $user_data ) {
        $mapped_id = get_option( 'nk_mapped_tpl_pwd_reset' );
        if ( ! $mapped_id ) return $title;

        $tpl = self::get( $mapped_id );
        if ( ! $tpl ) return $title;

        $vars = array( 'name' => $user_data->first_name ? $user_data->first_name : $user_login, 'email' => $user_data->user_email );
        self::$hijacked_subject = self::render( $tpl['subject'], $vars );

        return self::$hijacked_subject;
    }

    public static function intercept_password_message( $message, $key, $user_login, $user_data ) {
        $mapped_id = get_option( 'nk_mapped_tpl_pwd_reset' );
        if ( ! $mapped_id ) return $message;

        $tpl = self::get( $mapped_id );
        if ( ! $tpl ) return $message;

        $action_link = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');

        $vars = array(
            'name'        => $user_data->first_name ? $user_data->first_name : $user_login,
            'email'       => $user_data->user_email,
            'action_link' => $action_link,
            'action_text' => 'Securely Reset Password'
        );

        self::$hijacked_html = self::render( $tpl['html_content'], $vars );

        return self::$hijacked_html;
    }

    // ========================================================================
    // STANDARD TEMPLATE CRUD METHODS
    // ========================================================================
    public static function create( $name, $type, $subject, $html_content ) {
        global $wpdb;
        $table = NK_Database::table( 'email_templates' );

        $wpdb->insert( $table, array(
            'template_name' => sanitize_text_field( $name ),
            'template_type' => sanitize_text_field( $type ),
            'subject'       => sanitize_text_field( $subject ),
            'html_content'  => wp_kses_post( $html_content ),
            'status'        => 'active',
            'created_at'    => current_time( 'mysql' ),
            'updated_at'    => current_time( 'mysql' ),
        ) );
        return $wpdb->insert_id;
    }

    public static function update( $id, $fields ) {
        global $wpdb;
        $table = NK_Database::table( 'email_templates' );

        $data = array( 'updated_at' => current_time( 'mysql' ) );

        if ( isset( $fields['template_name'] ) )  { $data['template_name'] = sanitize_text_field( $fields['template_name'] ); }
        if ( isset( $fields['template_type'] ) )  { $data['template_type'] = sanitize_text_field( $fields['template_type'] ); }
        if ( isset( $fields['subject'] ) )        { $data['subject'] = sanitize_text_field( $fields['subject'] ); }
        if ( isset( $fields['html_content'] ) )   { $data['html_content'] = wp_kses_post( $fields['html_content'] ); }
        if ( isset( $fields['status'] ) )         { $data['status'] = sanitize_text_field( $fields['status'] ); }

        return $wpdb->update( $table, $data, array( 'id' => (int) $id ) );
    }

    public static function delete( $id ) {
        global $wpdb;
        $table = NK_Database::table( 'email_templates' );
        return $wpdb->delete( $table, array( 'id' => (int) $id ) );
    }

    public static function get( $id ) {
        global $wpdb;
        $table = NK_Database::table( 'email_templates' );
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ), ARRAY_A );
    }

    public static function get_all( $status = 'active' ) {
        global $wpdb;
        $table = NK_Database::table( 'email_templates' );

        if ( $status ) {
            return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE status = %s ORDER BY updated_at DESC", $status ), ARRAY_A );
        }
        return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY updated_at DESC", ARRAY_A );
    }

    public static function render( $content, array $vars = array() ) {
        $defaults = array(
            'name'             => '',
            'email'            => '',
            'job_title'        => '',
            'company_name'     => '',
            'candidate_name'   => '',
            'unsubscribe_link' => '',
            'action_link'      => '#',
            'action_text'      => 'Click Here',
            'dashboard_link'   => home_url( '/dashboard/' ),
        );

        $vars = array_merge( $defaults, $vars );

        foreach ( $vars as $key => $value ) {
            $content = str_replace( '{{' . $key . '}}', esc_html( $value ), $content );
        }

        return $content;
    }
}

// 🔴 10X INIT: Fire the interceptors globally immediately when the file loads
NK_Template_Manager::init();