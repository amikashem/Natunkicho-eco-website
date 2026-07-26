<?php
/**
 * NatunKicho Premium CV Templates Engine (Zety-Inspired)
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="nk-premium-template-switcher" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 25px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <h4 style="margin: 0; font-size: 14px; color: #0f172a;">🎨 Choose Premium Layout</h4>
        <?php if(!$is_premium): ?>
            <span style="font-size: 11px; background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 4px; font-weight: bold;">Premium Feature</span>
        <?php endif; ?>
    </div>
    
    <div style="display: flex; gap: 10px;">
        <button type="button" class="nk-template-btn active" data-template="template-cascade" style="flex: 1; padding: 8px; border: 2px solid #0A66C2; background: #eff6ff; border-radius: 6px; cursor: pointer; font-weight: bold; color: #1e40af; font-size: 12px;">
            Cascade (Modern)
        </button>
        
        <button type="button" class="nk-template-btn <?php echo !$is_premium ? 'locked' : ''; ?>" data-template="template-crisp" style="flex: 1; padding: 8px; border: 1px solid #cbd5e1; background: #fff; border-radius: 6px; cursor: <?php echo $is_premium ? 'pointer' : 'not-allowed'; ?>; font-weight: bold; color: #475569; font-size: 12px; opacity: <?php echo $is_premium ? '1' : '0.6'; ?>;">
            <?php echo $is_premium ? 'Crisp (Classic)' : '🔒 Crisp (Classic)'; ?>
        </button>
        
        <button type="button" class="nk-template-btn <?php echo !$is_premium ? 'locked' : ''; ?>" data-template="template-concept" style="flex: 1; padding: 8px; border: 1px solid #cbd5e1; background: #fff; border-radius: 6px; cursor: <?php echo $is_premium ? 'pointer' : 'not-allowed'; ?>; font-weight: bold; color: #475569; font-size: 12px; opacity: <?php echo $is_premium ? '1' : '0.6'; ?>;">
            <?php echo $is_premium ? 'Concept (Creative)' : '🔒 Concept'; ?>
        </button>
    </div>
</div>

<style id="nk-premium-print-styles">
    /* 1. CASCADE (Zety Modern) - Dark Elegant Sidebar */
    .nk-cv-canvas.template-cascade > div:nth-child(1) { background: #2c3e50 !important; color: #ffffff !important; border-right: none !important; }
    .nk-cv-canvas.template-cascade > div:nth-child(1) h1, .nk-cv-canvas.template-cascade > div:nth-child(1) h3, .nk-cv-canvas.template-cascade > div:nth-child(1) div { color: #ffffff !important; }
    .nk-cv-canvas.template-cascade > div:nth-child(1) .prev-heading-side { border-bottom: 1px solid #475569 !important; color: #94a3b8 !important; }
    .nk-cv-canvas.template-cascade > div:nth-child(1) span[style*="background: #e2e8f0"] { background: rgba(255,255,255,0.1) !important; color: #fff !important; }

    /* 2. CRISP (Zety Classic) - 1 Column Top Down, Minimalist */
    .nk-cv-canvas.template-crisp { display: block !important; padding: 50px !important; }
    .nk-cv-canvas.template-crisp > div:nth-child(1) { width: 100% !important; display: block !important; border: none !important; text-align: center !important; padding: 0 0 20px 0 !important; background: transparent !important; color: #000 !important; border-bottom: 2px solid #0f172a !important; margin-bottom: 20px; }
    .nk-cv-canvas.template-crisp > div:nth-child(2) { width: 100% !important; display: block !important; padding: 0 !important; }
    .nk-cv-canvas.template-crisp #prev-photo { display: inline-block; margin-bottom: 15px; border: 2px solid #e2e8f0 !important; }
    .nk-cv-canvas.template-crisp h1 { text-align: center !important; font-size: 32px !important; }
    .nk-cv-canvas.template-crisp .prev-heading-side { display: none !important; } 
    .nk-cv-canvas.template-crisp .prev-heading-main { color: #0f172a !important; border-bottom: 1px solid #cbd5e1 !important; text-align: left; }
    .nk-cv-canvas.template-crisp .nk-social-links { flex-direction: row !important; justify-content: center !important; flex-wrap: wrap; }

    /* 3. CONCEPT (Zety Creative) - Asian Inspired, Colored Timeline */
    .nk-cv-canvas.template-concept > div:nth-child(1) { background: #fdf2f8 !important; border-right: 4px solid #be185d !important; }
    .nk-cv-canvas.template-concept .prev-heading-main, .nk-cv-canvas.template-concept .prev-heading-side { color: #be185d !important; border-bottom-color: #fbcfe8 !important; }
    .nk-cv-canvas.template-concept .nk-exp-block-border { border-left-color: #be185d !important; }
    .nk-cv-canvas.template-concept .nk-exp-dot { background: #be185d !important; }
</style>

<script>
jQuery(document).ready(function($) {
    $('.nk-template-btn').click(function() {
        if ($(this).hasClass('locked')) {
            alert('🔒 Premium Feature: Upgrade to unlock premium international formats.');
            return;
        }
        $('.nk-template-btn').css({'border': '1px solid #cbd5e1', 'background': '#fff', 'color': '#475569'}).removeClass('active');
        $(this).css({'border': '2px solid #0A66C2', 'background': '#eff6ff', 'color': '#1e40af'}).addClass('active');
        
        let selectedTemplate = $(this).data('template');
        $('#nk-cv-canvas').removeClass('template-cascade template-crisp template-concept').addClass(selectedTemplate);
    });
});
</script>