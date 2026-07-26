<?php
if (!defined('ABSPATH')) exit;
$experience = json_decode($resume->experience_data, true) ?: [];
$education  = json_decode($resume->education_data, true) ?: [];
$skills     = json_decode($resume->skills_data, true) ?: [];
?>
<div class="nkrp-executive-cv">
    <div class="exec-sidebar">
        <div class="exec-name-box">
            <h1><?= esc_html($candidate->first_name) ?><br><span class="last-name"><?= esc_html($candidate->last_name) ?></span></h1>
            <div class="exec-title"><?= esc_html($resume->resume_title) ?></div>
        </div>
        
        <div class="exec-contact">
            <h3>Contact</h3>
            <p><?= esc_html($candidate->location_city . ', ' . $candidate->location_country) ?></p>
            <p><?= esc_html($candidate->email) ?></p>
            <p><?= esc_html($candidate->phone) ?></p>
        </div>

        <?php if (!empty($skills)): ?>
            <div class="exec-skills">
                <h3>Core Competencies</h3>
                <ul><?php foreach ($skills as $skill): ?><li><?= esc_html($skill) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>
    </div>

    <div class="exec-main">
        <?php if (!empty($resume->objective)): ?>
            <div class="exec-section">
                <h2>Executive Summary</h2>
                <div class="exec-text"><?= wp_kses_post($resume->objective) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($experience)): ?>
            <div class="exec-section">
                <h2>Professional Experience</h2>
                <?php foreach ($experience as $exp): ?>
                    <div class="exec-item">
                        <div class="exec-item-header">
                            <span class="exec-role"><?= esc_html($exp['role'] ?? '') ?></span>
                            <span class="exec-date"><?= esc_html($exp['years'] ?? '') ?></span>
                        </div>
                        <div class="exec-company"><?= esc_html($exp['company'] ?? '') ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<style>
    .nkrp-executive-cv { display: flex; max-width: 950px; margin: 0 auto; background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.1); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    .nkrp-executive-cv .exec-sidebar { width: 35%; background: #171717; color: #fff; padding: 40px; }
    .nkrp-executive-cv .exec-main { width: 65%; padding: 50px; background: #fafafa; }
    .nkrp-executive-cv h1 { margin: 0; font-size: 38px; font-weight: 300; line-height: 1.1; color: #fff; letter-spacing: 1px;}
    .nkrp-executive-cv .last-name { font-weight: 800; color: #d4af37; /* Gold Accent */ }
    .nkrp-executive-cv .exec-title { margin-top: 15px; font-size: 14px; text-transform: uppercase; letter-spacing: 2px; color: #a3a3a3; }
    .nkrp-executive-cv h3 { font-size: 16px; text-transform: uppercase; letter-spacing: 1.5px; border-bottom: 1px solid #333; padding-bottom: 10px; margin-top: 40px; margin-bottom: 20px; color: #fff; }
    .nkrp-executive-cv .exec-contact p { margin: 0 0 10px 0; font-size: 13px; color: #ccc; }
    .nkrp-executive-cv .exec-skills ul { list-style: none; padding: 0; margin: 0; }
    .nkrp-executive-cv .exec-skills li { margin-bottom: 8px; font-size: 13px; color: #ccc; }
    .nkrp-executive-cv h2 { font-size: 22px; color: #171717; border-bottom: 2px solid #d4af37; padding-bottom: 10px; margin-top: 0; margin-bottom: 25px; }
    .nkrp-executive-cv .exec-section { margin-bottom: 40px; }
    .nkrp-executive-cv .exec-text { font-size: 15px; line-height: 1.7; color: #444; }
    .nkrp-executive-cv .exec-item { margin-bottom: 25px; }
    .nkrp-executive-cv .exec-item-header { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 5px; }
    .nkrp-executive-cv .exec-role { font-size: 18px; font-weight: 700; color: #171717; }
    .nkrp-executive-cv .exec-date { font-size: 13px; color: #666; font-weight: 600; }
    .nkrp-executive-cv .exec-company { font-size: 15px; color: #d4af37; font-weight: 600; }
    @media(max-width: 768px) { .nkrp-executive-cv { flex-direction: column; } .nkrp-executive-cv .exec-sidebar, .nkrp-executive-cv .exec-main { width: 100%; padding: 30px; } }
</style>