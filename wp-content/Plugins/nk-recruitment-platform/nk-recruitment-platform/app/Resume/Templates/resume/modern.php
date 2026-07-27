<?php
if (!defined('ABSPATH')) exit;
$experience = json_decode($resume->experience_data, true) ?: [];
$education  = json_decode($resume->education_data, true) ?: [];
$skills     = json_decode($resume->skills_data, true) ?: [];
?>
<div class="nkrp-modern-cv">
    <div class="cv-header">
        <h1 class="cv-name"><?= esc_html($candidate->first_name . ' ' . $candidate->last_name) ?></h1>
        <h2 class="cv-title"><?= esc_html($resume->resume_title) ?></h2>
        <div class="cv-meta">
            <?php if ($candidate->location_city): ?><span><i class="dashicons dashicons-location"></i> <?= esc_html($candidate->location_city . ', ' . $candidate->location_country) ?></span><?php endif; ?>
            <?php if ($candidate->email): ?><span><i class="dashicons dashicons-email"></i> <?= esc_html($candidate->email) ?></span><?php endif; ?>
        </div>
    </div>
    <?php if (!empty($resume->objective)): ?>
        <div class="cv-section"><h3 class="cv-section-title"><?php esc_html_e('Professional Summary', 'nk-recruitment'); ?></h3><div class="cv-content objective-text"><?= wp_kses_post($resume->objective) ?></div></div>
    <?php endif; ?>
    <div class="cv-grid">
        <div class="cv-main-col">
            <?php if (!empty($experience)): ?>
                <div class="cv-section"><h3 class="cv-section-title"><?php esc_html_e('Work Experience', 'nk-recruitment'); ?></h3>
                    <div class="cv-timeline">
                        <?php foreach ($experience as $exp): ?>
                            <div class="timeline-item">
                                <div class="timeline-date"><?= esc_html($exp['years'] ?? '') ?></div>
                                <div class="timeline-content"><h4 class="item-title"><?= esc_html($exp['role'] ?? '') ?></h4><h5 class="item-subtitle"><?= esc_html($exp['company'] ?? '') ?></h5></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="cv-sidebar-col">
            <?php if (!empty($skills)): ?>
                <div class="cv-section"><h3 class="cv-section-title"><?php esc_html_e('Top Skills', 'nk-recruitment'); ?></h3>
                    <div class="cv-skills"><?php foreach ($skills as $skill): ?><span class="skill-badge"><?= esc_html($skill) ?></span><?php endforeach; ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<style>
    .nkrp-modern-cv { background: #fff; max-width: 900px; margin: 0 auto; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    .nkrp-modern-cv .cv-header { background: #0f172a; color: #fff; padding: 40px; }
    .nkrp-modern-cv .cv-name { margin: 0 0 5px 0; font-size: 32px; font-weight: 800; color:#fff; }
    .nkrp-modern-cv .cv-title { margin: 0 0 15px 0; font-size: 18px; color: #94a3b8; }
    .nkrp-modern-cv .cv-meta { display: flex; gap: 20px; flex-wrap: wrap; font-size: 14px; color: #cbd5e1; }
    .nkrp-modern-cv .cv-section { padding: 30px 40px; border-bottom: 1px solid #f1f5f9; }
    .nkrp-modern-cv .cv-section-title { font-size: 18px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
    .nkrp-modern-cv .objective-text { color: #475569; line-height: 1.6; }
    .nkrp-modern-cv .cv-grid { display: grid; grid-template-columns: 2fr 1fr; }
    .nkrp-modern-cv .cv-main-col { border-right: 1px solid #f1f5f9; }
    .nkrp-modern-cv .timeline-item { display: grid; grid-template-columns: 120px 1fr; gap: 20px; margin-bottom: 20px; }
    .nkrp-modern-cv .timeline-date { color: #64748b; font-size: 14px; font-weight: 600; }
    .nkrp-modern-cv .item-title { margin: 0 0 4px 0; font-size: 16px; font-weight: 700; color: #0f172a; }
    .nkrp-modern-cv .item-subtitle { margin: 0; font-size: 15px; color: #3b82f6; font-weight: 500;}
    .nkrp-modern-cv .cv-skills { display: flex; flex-wrap: wrap; gap: 10px; }
    .nkrp-modern-cv .skill-badge { background: #f1f5f9; color: #334155; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; }
    @media(max-width: 768px) { .nkrp-modern-cv .cv-grid { grid-template-columns: 1fr; } .nkrp-modern-cv .cv-main-col { border-right: none; } .nkrp-modern-cv .timeline-item { grid-template-columns: 1fr; gap: 5px; } }
</style>