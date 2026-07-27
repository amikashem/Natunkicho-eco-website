<?php
namespace NK_AI_RankMath\Assets;

class Manager {
    public function enqueue_admin() {
        $admin_assets = new \NK_AI_RankMath\Admin\Assets();
        $admin_assets->enqueue_admin(get_current_screen()->base);
    }
    
    public function enqueue_public() {
        // Public assets if needed
    }
}