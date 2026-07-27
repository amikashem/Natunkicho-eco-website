<?php
/**
 * Natun Kicho - Google Translate Functions
 * WORKING VERSION - Proper Google Translate Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if translation should be shown
 */
function nk_translate_should_show() {
    // Hide only on front page
    if (is_front_page()) {
        return false;
    }
    return true;
}

/**
 * Enqueue translate assets
 */
function nk_translate_enqueue_assets() {
    if (!nk_translate_should_show()) {
        return;
    }
    
    // Load CSS
    wp_enqueue_style(
        'nk-translate-css',
        get_stylesheet_directory_uri() . '/assets/css/translate.css',
        array(),
        '1.0'
    );
    
    // Load JS
    wp_enqueue_script(
        'nk-translate-js',
        get_stylesheet_directory_uri() . '/assets/js/translate.js',
        array('jquery'),
        '1.0',
        true
    );
    
    // Get current language
    $current_lang = 'en';
    if (isset($_COOKIE['googtrans'])) {
        $parts = explode('/', $_COOKIE['googtrans']);
        if (isset($parts[2])) {
            $current_lang = $parts[2];
        }
    }
    
    // Pass data to JavaScript
    wp_localize_script('nk-translate-js', 'nk_translate', array(
        'current_lang' => $current_lang,
        'home_url' => home_url()
    ));
}
add_action('wp_enqueue_scripts', 'nk_translate_enqueue_assets');

/**
 * Output Translate Button - WORKING VERSION
 */
function nk_translate_output_button() {
    if (!nk_translate_should_show()) {
        return;
    }
    
    // Get current language
    $current_lang = 'en';
    if (isset($_COOKIE['googtrans'])) {
        $parts = explode('/', $_COOKIE['googtrans']);
        if (isset($parts[2])) {
            $current_lang = $parts[2];
        }
    }
    
    // Button text
    $button_text = ($current_lang !== 'en') ? 'Translated' : 'Translate';
    ?>
    
    <!-- Natun Kicho Translate Button -->
    <div id="nk-translate-widget" class="nk-floating-translator">
        
        <button id="nk-translate-toggle" class="nk-float-btn" type="button">
            <span class="translate-icon">🌐</span>
            <span class="translate-text"><?php echo esc_html($button_text); ?></span>
        </button>

        <div id="nk-translate-panel" class="nk-panel">
            
            <div class="nk-header">
                <strong>Translate Page</strong>
                <button type="button" class="nk-close-panel" id="nk-close-panel">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <ul id="nk-translate-list" class="nk-lang-list">
                <li data-lang="en" class="nk-lang-item <?php echo ($current_lang === 'en') ? 'active' : ''; ?>">
                    English
                </li>
                <li data-lang="bn" class="nk-lang-item <?php echo ($current_lang === 'bn') ? 'active' : ''; ?>">
                    Bangla
                </li>
                <li data-lang="fr" class="nk-lang-item <?php echo ($current_lang === 'fr') ? 'active' : ''; ?>">
                    French
                </li>
                <li data-lang="hi" class="nk-lang-item <?php echo ($current_lang === 'hi') ? 'active' : ''; ?>">
                    Hindi
                </li>
                <li data-lang="ur" class="nk-lang-item <?php echo ($current_lang === 'ur') ? 'active' : ''; ?>">
                    Urdu
                </li>
                <li data-lang="ar" class="nk-lang-item <?php echo ($current_lang === 'ar') ? 'active' : ''; ?>">
                    Arabic
                </li>
                <li data-lang="es" class="nk-lang-item <?php echo ($current_lang === 'es') ? 'active' : ''; ?>">
                    Spanish
                </li>
                <li data-lang="ja" class="nk-lang-item <?php echo ($current_lang === 'ja') ? 'active' : ''; ?>">
                    Japanese
                </li>
            </ul>

        </div>

        <!-- Google Translate Element - MUST be present -->
        <div id="google_translate_element" style="display:none;"></div>

    </div>
    
    <!-- Google Translate Script - SIMPLE AND WORKING -->
    <script type="text/javascript">
    // Global Google Translate initialization function
    // This function name MUST match the callback in script src
    function googleTranslateElementInit() {
        console.log('Google Translate: Initializing...');
        
        if (typeof google === 'undefined' || typeof google.translate === 'undefined') {
            console.error('Google Translate API not loaded');
            return;
        }
        
        try {
            // Initialize Google Translate widget
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'en,bn,fr,hi,ur,ar,es,ja',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                autoDisplay: false
            }, 'google_translate_element');
            
            console.log('Google Translate: Widget initialized');
            
            // Hide Google's toolbar
            setTimeout(function() {
                var banner = document.querySelector('.goog-te-banner-frame');
                if (banner) banner.style.display = 'none';
                
                var simpleBanner = document.querySelector('.goog-te-banner');
                if (simpleBanner) simpleBanner.style.display = 'none';
                
                document.body.style.top = '0px';
            }, 1000);
            
        } catch (error) {
            console.error('Google Translate Error:', error);
        }
    }
    
    // Load Google Translate script
    function loadGoogleTranslate() {
        // Check if already loaded
        if (typeof google !== 'undefined' && typeof google.translate !== 'undefined') {
            console.log('Google Translate: Already loaded');
            return;
        }
        
        // Check if script already exists
        if (document.querySelector('script[src*="translate.google.com"]')) {
            console.log('Google Translate: Script already loading');
            return;
        }
        
        console.log('Google Translate: Loading script...');
        
        // Create script element
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
        script.async = true;
        script.defer = true;
        
        // Add to document
        document.head.appendChild(script);
    }
    
    // Load when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Load Google Translate after a delay
        setTimeout(loadGoogleTranslate, 1000);
    });
    </script>
    <?php
}
add_action('wp_footer', 'nk_translate_output_button', 10);