<?php
if (!defined('ABSPATH')) exit;
$experience = json_decode($resume->experience_data, true) ?: [];
$education  = json_decode($resume->education_data, true) ?: [];
$skills     = json_decode($resume->skills_data, true) ?: [];
?>
<div class="nkrp-professional-cv">
    <div class="pro-header">
        <div class="pro-title-box">
            <h1><?= esc_html($candidate->first_name . ' ' . $candidate->last_name) ?></h1>
            <p class="pro-subtitle"><?= esc_html($resume->resume_title) ?></p>
        </div>
        <div class="pro-contact-box">
            <p><?= esc_html($candidate->email) ?></p>
            <p><?= esc_html($candidate->phone) ?></p>
            <p><?= esc_html($candidate->location_city . ', ' . $candidate->location_country) ?></p>
        </div>
    </div>

    <div class="pro-body">
        <?php if (!empty($resume->objective)): ?>
            <div class="pro-section">
                <div class="pro-label">Profile</div>
                <div class="pro-content"><?= wp_kses_post($resume->objective) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($experience)): ?>
            <div class="pro-section">
                <div class="pro-label">Experience</div>
                <div class="pro-content">
                    <?php foreach ($experience as $exp): ?>
                        <div class="pro-item">
                            <div class="pro-item-header">
                                <strong><?= esc_html($exp['role'] ?? '') ?></strong>
                                <span class="pro-date"><?= esc_html($exp['years'] ?? '') ?></span>
                            </div>
                            <div class="pro-company"><?= esc_html($exp['company'] ?? '') ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($skills)): ?>
            <div class="pro-section">
                <div class="pro-label">Expertise</div>
                <div class="pro-content">
                    <ul class="pro-skills-list">
                        <?php foreach ($skills as $skill): ?><li><?= esc_html($skill) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<style>
    .nkrp-professional-cv { max-width: 900px; margin: 0 auto; background: #fff; padding: 50px; font-family: "Georgia", serif; color: #333; border: 1px solid #eaeaea; }
    .nkrp-professional-cv .pro-header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 3px solid #1e3a8a; padding-bottom: 20px; margin-bottom: 30px; }
    .nkrp-professional-cv h1 { margin: 0 0 5px 0; font-size: 36px; color: #1e3a8a; }
    .nkrp-professional-cv .pro-subtitle { margin: 0; font-size: 16px; color: #666; font-family: "Arial", sans-serif; text-transform: uppercase; letter-spacing: 1px;}
    .nkrp-professional-cv .pro-contact-box { text-align: right; font-family: "Arial", sans-serif; font-size: 13px; color: #555; line-height: 1.6; }
    .nkrp-professional-cv .pro-contact-box p { margin: 0; }
    .nkrp-professional-cv .pro-section { display: grid; grid-template-columns: 150px 1fr; gap: 30px; margin-bottom: 30px; }
    .nkrp-professional-cv .pro-label { font-size: 14px; font-weight: bold; text-transform: uppercase; color: #1e3a8a; font-family: "Arial", sans-serif; border-top: 1px solid #ccc; padding-top: 10px; }
    .nkrp-professional-cv .pro-content { border-top: 1px solid #ccc; padding-top: 10px; font-size: 15px; line-height: 1.6; }
    .nkrp-professional-cv .pro-item-header { display: flex; justify-content: space-between; font-family: "Arial", sans-serif; font-size: 16px; }
    .nkrp-professional-cv .pro-company { font-style: italic; color: #666; margin-bottom: 15px; }
    .nkrp-professional-cv .pro-skills-list { margin: 0; padding-left: 20px; columns: 2; font-family: "Arial", sans-serif; }
    @media(max-width: 768px) { .nkrp-professional-cv .pro-section { grid-template-columns: 1fr; gap: 10px; } .nkrp-professional-cv .pro-header { flex-direction: column; align-items: flex-start; gap: 15px; } .nkrp-professional-cv .pro-contact-box { text-align: left; } }
</style>