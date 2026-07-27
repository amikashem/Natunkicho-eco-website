/**
 * Natun Kicho Translate - WORKING VERSION
 * Actually triggers Google Translate
 */

// Global state
var NKTranslate = {
    isInitialized: false,
    isTranslating: false,
    currentLang: 'en',
    googleTranslateSelect: null
};

// Initialize when DOM is ready
jQuery(document).ready(function($) {
    console.log('NK Translate: Starting...');
    
    // Get current language from PHP
    if (window.nk_translate && window.nk_translate.current_lang) {
        NKTranslate.currentLang = window.nk_translate.current_lang;
    }
    
    console.log('Current language:', NKTranslate.currentLang);
    
    // Get elements
    var $widget = $('#nk-translate-widget');
    if ($widget.length === 0) {
        console.log('Widget not found (maybe homepage)');
        return;
    }
    
    var $toggleBtn = $('#nk-translate-toggle');
    var $panel = $('#nk-translate-panel');
    var $closeBtn = $('#nk-close-panel');
    var $langItems = $('.nk-lang-item');
    
    console.log('Found', $langItems.length, 'language items');
    
    // Initially hide panel
    $panel.hide();
    
    // 1. Toggle panel
    $toggleBtn.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if ($panel.is(':visible')) {
            $panel.hide();
        } else {
            $panel.show();
            // Try to find Google Translate dropdown
            findGoogleTranslateDropdown();
        }
    });
    
    // 2. Close button
    $closeBtn.on('click', function(e) {
        e.preventDefault();
        $panel.hide();
    });
    
    // 3. Close when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#nk-translate-widget').length && $panel.is(':visible')) {
            $panel.hide();
        }
    });
    
    // 4. Language selection - MAIN FUNCTION
    $langItems.on('click', function() {
        if (NKTranslate.isTranslating) {
            console.log('Already translating, please wait');
            return;
        }
        
        var $item = $(this);
        var langCode = $item.data('lang');
        var langName = $item.text().trim();
        
        console.log('Selected language:', langCode, langName);
        
        // Update UI
        $langItems.removeClass('active');
        $item.addClass('active');
        $panel.hide();
        
        // Update button text
        $toggleBtn.find('.translate-text').text(langCode === 'en' ? 'Translate' : 'Translated');
        
        // Start translation
        translatePage(langCode, langName);
    });
    
    console.log('NK Translate: Ready');
    
    // Initialize
    findGoogleTranslateDropdown();
    hideGoogleBanner();
    setInterval(hideGoogleBanner, 1000);
    
    // Mark as initialized
    NKTranslate.isInitialized = true;
});

// Find Google Translate dropdown
function findGoogleTranslateDropdown() {
    if (NKTranslate.googleTranslateSelect) {
        return;
    }
    
    // Try to find the dropdown
    NKTranslate.googleTranslateSelect = document.querySelector('.goog-te-combo');
    
    if (NKTranslate.googleTranslateSelect) {
        console.log('Found Google Translate dropdown');
        // Set current language if not English
        if (NKTranslate.currentLang !== 'en') {
            NKTranslate.googleTranslateSelect.value = NKTranslate.currentLang;
        }
    } else {
        // Try again in 500ms
        setTimeout(findGoogleTranslateDropdown, 500);
    }
}

// Main translation function - THIS IS THE KEY FUNCTION
function translatePage(langCode, langName) {
    console.log('Starting translation to:', langCode);
    
    NKTranslate.isTranslating = true;
    var $toggleBtn = jQuery('#nk-translate-toggle');
    
    // Show loading state
    $toggleBtn.addClass('loading').prop('disabled', true);
    $toggleBtn.find('.translate-text').text('Translating...');
    
    if (langCode === 'en') {
        // Restore to English
        restoreToEnglish();
    } else {
        // Translate to other language
        translateToLanguage(langCode, langName);
    }
}

// Restore to English
function restoreToEnglish() {
    console.log('Restoring to English');
    
    // Method 1: Use Google Translate dropdown
    if (NKTranslate.googleTranslateSelect) {
        NKTranslate.googleTranslateSelect.value = 'en';
        triggerChangeEvent(NKTranslate.googleTranslateSelect);
        console.log('Set dropdown to English');
    }
    
    // Method 2: Clear cookies
    clearTranslationCookies();
    
    // Method 3: Use Google API if available
    if (window.google && google.translate && google.translate.translate) {
        try {
            google.translate.translate.restore();
        } catch (e) {
            console.log('Google restore failed:', e);
        }
    }
    
    // Show message
    showMessage('Restored to English');
    
    // Reload page after delay for clean state
    setTimeout(function() {
        window.location.reload();
    }, 800);
}

// Translate to other language - WORKING METHOD
function translateToLanguage(langCode, langName) {
    console.log('Translating to:', langCode);
    
    var translationStarted = false;
    
    // METHOD 1: Use cached Google Translate dropdown
    if (NKTranslate.googleTranslateSelect) {
        console.log('Using cached dropdown');
        NKTranslate.googleTranslateSelect.value = langCode;
        triggerChangeEvent(NKTranslate.googleTranslateSelect);
        translationStarted = true;
    }
    
    // METHOD 2: Try to find dropdown
    if (!translationStarted) {
        var select = document.querySelector('.goog-te-combo');
        if (select) {
            console.log('Found dropdown dynamically');
            select.value = langCode;
            triggerChangeEvent(select);
            NKTranslate.googleTranslateSelect = select;
            translationStarted = true;
        }
    }
    
    // Set cookie for persistence
    setTranslationCookie(langCode);
    
    // Show message
    showMessage('Translating to ' + langName);
    
    // If dropdown not found, use cookie method
    if (!translationStarted) {
        console.log('Dropdown not found, using cookie method');
        setTimeout(function() {
            window.location.reload();
        }, 800);
        return;
    }
    
    // Reset button after delay
    setTimeout(function() {
        jQuery('#nk-translate-toggle').removeClass('loading').prop('disabled', false);
        NKTranslate.isTranslating = false;
    }, 1500);
}

// Trigger change event properly
function triggerChangeEvent(element) {
    // Create and dispatch change event
    var event = new Event('change', {
        bubbles: true,
        cancelable: true
    });
    element.dispatchEvent(event);
    
    // Also try other event types
    element.dispatchEvent(new Event('input'));
    element.dispatchEvent(new Event('blur'));
    
    console.log('Change event triggered for language:', element.value);
}

// Set translation cookie
function setTranslationCookie(langCode) {
    var domain = window.location.hostname;
    // Remove www. if present
    if (domain.indexOf('www.') === 0) {
        domain = domain.substring(4);
    }
    
    var cookieValue = '/en/' + langCode;
    var expires = new Date();
    expires.setFullYear(expires.getFullYear() + 1);
    
    // Set cookie for domain
    document.cookie = "googtrans=" + cookieValue + 
                     "; expires=" + expires.toUTCString() + 
                     "; path=/; domain=." + domain + 
                     "; SameSite=Lax";
    
    // Set cookie for current host
    document.cookie = "googtrans=" + cookieValue + 
                     "; expires=" + expires.toUTCString() + 
                     "; path=/";
    
    console.log('Cookie set for language:', langCode);
}

// Clear translation cookies
function clearTranslationCookies() {
    var domain = window.location.hostname;
    if (domain.indexOf('www.') === 0) {
        domain = domain.substring(4);
    }
    
    var pastDate = new Date(0).toUTCString();
    
    // Clear all Google Translate cookies
    var cookies = ['googtrans', 'googtrans_redirected'];
    
    cookies.forEach(function(cookieName) {
        // Clear for domain
        document.cookie = cookieName + "=; expires=" + pastDate + 
                         "; path=/; domain=." + domain;
        
        // Clear for current host
        document.cookie = cookieName + "=; expires=" + pastDate + 
                         "; path=/";
    });
    
    console.log('Translation cookies cleared');
}

// Show message
function showMessage(text) {
    // Remove existing messages
    var existing = document.querySelector('.nk-translate-message');
    if (existing) existing.remove();
    
    // Create message element
    var message = document.createElement('div');
    message.className = 'nk-translate-message';
    message.textContent = text;
    
    // Style it
    message.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4CAF50;
        color: white;
        padding: 12px 20px;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000000;
        font-size: 14px;
        font-weight: 500;
    `;
    
    document.body.appendChild(message);
    
    // Remove after 3 seconds
    setTimeout(function() {
        if (message.parentNode) {
            message.style.transition = 'opacity 0.3s';
            message.style.opacity = '0';
            setTimeout(function() {
                if (message.parentNode) message.parentNode.removeChild(message);
            }, 300);
        }
    }, 3000);
}

// Hide Google banner
function hideGoogleBanner() {
    // Hide all Google Translate banners
    var banners = document.querySelectorAll('.goog-te-banner-frame, .goog-te-banner, .skiptranslate');
    banners.forEach(function(banner) {
        banner.style.display = 'none';
    });
    
    // Fix body position
    document.body.style.top = '0';
}