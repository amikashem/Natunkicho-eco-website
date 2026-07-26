<?php

declare(strict_types=1);

namespace NKRecruitment\Membership\Services;

use NKRecruitment\Database\DatabaseManager;

if (!defined('ABSPATH')) exit;

class WooCommerceIntegration
{
    public function register(): void
    {
        // Fires automatically when a WooCommerce Order is marked "Completed" or "Processing"
        add_action('woocommerce_order_status_completed', [$this, 'autoUpgradeUser']);
        add_action('woocommerce_order_status_processing', [$this, 'autoUpgradeUser']);
    }

    public function autoUpgradeUser($order_id): void
    {
        $order = wc_get_order($order_id);
        if (!$order) return;

        $user_id = $order->get_user_id();
        if (!$user_id) return; // Guest checkout, skip.

        // The specific IDs you use for Premium Packages
        $premium_product_ids = [2943, 2945, 2948, 2978, 2979, 2980, 2956, 2945];

        $should_upgrade = false;

        // Loop through everything they just bought
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            if (in_array($product_id, $premium_product_ids)) {
                $should_upgrade = true;
                break;
            }
        }

        if ($should_upgrade) {
            global $wpdb;
            $table = DatabaseManager::table('subscriptions');
            
            // Upgrade them to premium!
            $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE user_id = %d", $user_id));
            if ($exists) {
                $wpdb->update($table, ['plan_key' => 'premium', 'status' => 'active'], ['user_id' => $user_id]);
            }
        }
    }
}