<?php
if (!defined('ABSPATH')) exit;
$experience = json_decode($resume->experience_data, true) ?: [];
$education  = json_decode($resume->education_data, true) ?: [];
$skills     = json_decode($resume->skills_data, true) ?: [];
?>
<div class="nkrp-default-cv">
    <div class="cv-header-center">
        <h1><?= esc_html($candidate->first_name . ' ' . $candidate->last_name) ?></h1>
        <p class="contact-info">
            <?= esc_html($candidate->location_city) ?> | <?= esc_html($candidate->email) ?> | <?= esc_html($candidate->phone) ?>
        </p>
    </div>

    <?php if (!empty($resume->objective)): ?>
        <div class="cv-block">
            <h2><?php esc_html_e('Professional Summary', 'nk-recruitment'); ?></h2>
            <div class="cv-text"><?= wp_kses_post($resume->objective) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($experience)): ?>
        <div class="cv-block">
            <h2><?php esc_html_e('Experience', 'nk-recruitment'); ?></h2>
            <?php foreach ($experience as $exp): ?>
                <div class="cv-item">
                    <div class="item-header">
                        <strong><?= esc_html($exp['role'] ?? '') ?></strong>
                        <span><?= esc_html($exp['years'] ?? '') ?></span>
                    </div>
                    <div class="item-sub"><?= esc_html($exp['company'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($education)): ?>
        <div class="cv-block">
            <h2><?php esc_html_e('Education', 'nk-recruitment'); ?></h2>
            <?php foreach ($education as $edu): ?>
                <div class="cv-item">
                    <div class="item-header">
                        <strong><?= esc_html($edu['degree'] ?? '') ?></strong>
                    </div>
                    <div class="item-sub"><?= esc_html($edu['school'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($skills)): ?>
        <div class="cv-block">
            <h2><?php esc_html_e('Skills', 'nk-recruitment'); ?></h2>
            <p class="cv-text"><?= esc_html(implode(', ', $skills)) ?></p>
        </div>
    <?php endif; ?>
</div>
<style>
    .nkrp-default-cv { max-width: 850px; margin: 0 auto; background: #fff; padding: 40px; font-family: Arial, Helvetica, sans-serif; color: #000; }
    .nkrp-default-cv .cv-header-center { text-align: center; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 20px; }
    .nkrp-default-cv h1 { margin: 0 0 10px 0; font-size: 28px; font-weight: normal; color: #000; }
    .nkrp-default-cv .contact-info { margin: 0; font-size: 14px; }
    .nkrp-default-cv h2 { font-size: 16px; text-transform: uppercase; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 25px; margin-bottom: 15px; color: #000;}
    .nkrp-default-cv .item-header { display: flex; justify-content: space-between; font-size: 15px; margin-bottom: 3px; }
    .nkrp-default-cv .item-sub { font-style: italic; font-size: 14px; margin-bottom: 15px; }
    .nkrp-default-cv .cv-text { font-size: 14px; line-height: 1.5; }
</style>