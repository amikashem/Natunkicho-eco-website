<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class NK_AI_Admin {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_menus' ) );
    }

    public static function register_menus() {
        // Main AI Core Menu
        add_menu_page(
            'NatunKicho AI Core',
            'AI Core',
            'manage_options',
            'nk-ai-core',
            array( __CLASS__, 'render_dashboard' ),
            'dashicons-buddicons-replies',
            58 // Places it prominently in the WP menu
        );

        // Submenus
        add_submenu_page( 'nk-ai-core', 'Providers & API Keys', 'Providers', 'manage_options', 'nk-ai-providers', array( __CLASS__, 'render_providers' ) );
        add_submenu_page( 'nk-ai-core', 'Prompt Library', 'Prompt Library', 'manage_options', 'nk-ai-prompts', array( __CLASS__, 'render_prompts' ) );
        add_submenu_page( 'nk-ai-core', 'Usage & Analytics', 'Analytics', 'manage_options', 'nk-ai-analytics', array( __CLASS__, 'render_analytics' ) );
        add_submenu_page( 'nk-ai-core', 'Job Queue', 'Queue Manager', 'manage_options', 'nk-ai-queue', array( __CLASS__, 'render_queue' ) );
    }

    public static function render_dashboard() {
        echo '<div class="wrap"><h1>NatunKicho AI Core Gateway</h1><p>Welcome to the central intelligence hub. All AI requests flow through here.</p></div>';
    }

    public static function render_providers() {
        echo '<div class="wrap"><h1>AI Providers & Security</h1><p>Manage OpenAI, Gemini, etc. Keys are encrypted via AES-256 before saving to DB.</p></div>';
    }

    public static function render_prompts() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nk_ai_prompts';
        $message = '';

        // Handle Save/Update action
        if ( isset( $_POST['nk_save_prompt'] ) && check_admin_referer( 'nk_save_prompt_nonce' ) ) {
            $module        = sanitize_text_field( $_POST['module_name'] );
            $prompt_key    = sanitize_text_field( $_POST['prompt_key'] );
            $system_prompt = sanitize_textarea_field( $_POST['system_prompt'] );
            
            // Check if prompt key already exists for this module
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM $table_name WHERE module_name = %s AND prompt_key = %s",
                $module, $prompt_key
            ) );

            if ( $exists ) {
                $wpdb->update( $table_name, array( 'system_prompt' => $system_prompt ), array( 'id' => $exists ) );
                $message = '<div class="updated notice is-dismissible"><p>Prompt updated successfully!</p></div>';
            } else {
                $wpdb->insert( $table_name, array(
                    'module_name'   => $module,
                    'prompt_key'    => $prompt_key,
                    'system_prompt' => $system_prompt,
                    'version'       => '1.0'
                ) );
                $message = '<div class="updated notice is-dismissible"><p>New Prompt added successfully!</p></div>';
            }
        }

        // Handle Delete action
        if ( isset( $_GET['delete_prompt'] ) && current_user_can('manage_options') ) {
            $delete_id = intval( $_GET['delete_prompt'] );
            $wpdb->delete( $table_name, array( 'id' => $delete_id ) );
            $message = '<div class="updated notice is-dismissible"><p>Prompt deleted.</p></div>';
        }

        // Fetch all current prompts
        $prompts = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY module_name ASC" );

        // Render the SaaS UI
        ?>
        <div class="wrap" style="max-width: 1200px;">
            <h1 class="wp-heading-inline">NatunKicho Prompt Library</h1>
            <p style="color: #666; font-size: 14px;">Manage the core intelligence instructions for all NatunKicho AI Modules.</p>
            <?php echo $message; ?>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; margin-top: 20px;">
                
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #ccd0d4;">
                    <h3>Add / Update Prompt</h3>
                    <form method="POST">
                        <?php wp_nonce_field( 'nk_save_prompt_nonce' ); ?>
                        
                        <p>
                            <label><strong>Module Name (e.g., seo, ats, cv)</strong></label><br>
                            <input type="text" name="module_name" required style="width: 100%; margin-top: 5px;" placeholder="e.g., seo_module">
                        </p>
                        
                        <p>
                            <label><strong>Prompt Key (Unique ID)</strong></label><br>
                            <input type="text" name="prompt_key" required style="width: 100%; margin-top: 5px;" placeholder="e.g., generate_meta_title">
                        </p>
                        
                        <p>
                            <label><strong>System Prompt (The AI Instructions)</strong></label><br>
                            <textarea name="system_prompt" required rows="10" style="width: 100%; margin-top: 5px; font-family: monospace;" placeholder="You are a 10x SEO expert. Your job is to..."></textarea>
                        </p>
                        
                        <input type="submit" name="nk_save_prompt" class="button button-primary button-hero" value="Save System Prompt">
                    </form>
                </div>

                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #ccd0d4;">
                    <h3>Active System Prompts</h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Module</th>
                                <th>Prompt Key</th>
                                <th>Prompt Preview</th>
                                <th style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ( empty( $prompts ) ) : ?>
                                <tr><td colspan="4">No prompts found. Add your first one on the left.</td></tr>
                            <?php else : ?>
                                <?php foreach ( $prompts as $p ) : ?>
                                    <tr>
                                        <td><strong><?php echo esc_html( $p->module_name ); ?></strong></td>
                                        <td><code><?php echo esc_html( $p->prompt_key ); ?></code></td>
                                        <td style="font-size:12px; color:#555;"><?php echo wp_trim_words( esc_html( $p->system_prompt ), 15 ); ?></td>
                                        <td>
                                            <a href="?page=nk-ai-prompts&delete_prompt=<?php echo $p->id; ?>" style="color: #d63638;" onclick="return confirm('Are you sure you want to delete this prompt?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
        <?php
    }

    public static function render_analytics() {
        echo '<div class="wrap"><h1>Usage & Cost Analytics</h1><p>Monitor token usage and financial costs per module.</p></div>';
    }

    public static function render_queue() {
        echo '<div class="wrap"><h1>Background Queue</h1><p>Monitor heavy tasks (Bulk SEO, ATS Parsing) running in the background.</p></div>';
    }
}