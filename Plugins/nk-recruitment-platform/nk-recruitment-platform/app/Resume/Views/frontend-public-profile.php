<?php if (!defined('ABSPATH')) exit; 
$viewer_id = get_current_user_id();
?>

<div class="nkrp-public-profile">
    
    <!-- Paywall Banner for Free Users -->
    <?php if (!$is_unlocked): ?>
        <div class="nkrp-paywall-banner">
            <span class="dashicons dashicons-lock"></span>
            <div class="nkrp-paywall-text">
                <strong>Premium Feature Locked</strong>
                <p>You are viewing this candidate in restricted mode. Upgrade your Employer account to view their name, contact details, direct message them, and download their full CV.</p>
            </div>
            <a href="<?= esc_url(home_url('/membership/')) ?>" class="nkrp-btn-upgrade">Upgrade to Premium</a>
        </div>
    <?php endif; ?>

    <!-- Header / Hero Section -->
    <div class="nkrp-profile-header">
        <div class="nkrp-profile-avatar">
            <span class="dashicons dashicons-businessman"></span>
        </div>
        <div class="nkrp-profile-title-area">
            <!-- THE BLUR EFFECT IN ACTION -->
            <h1 class="<?= !$is_unlocked ? 'nkrp-blurred' : '' ?>">
                <?= $is_unlocked ? esc_html($resume_data->display_name) : 'Candidate #NK' . $resume_data->id ?>
            </h1>
            <h2><?= esc_html($resume_data->resume_title) ?></h2>
            
            <div class="nkrp-skills-container">
                <?php foreach(array_slice($skills, 0, 5) as $skill): ?>
                    <span class="nkrp-skill-badge"><?= esc_html($skill) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="nkrp-profile-actions">
            <?php if ($is_unlocked): ?>
                
                <?php if ($viewer_id !== (int)$resume_data->user_id): ?>
                    <!-- DIRECT MESSAGING INTEGRATION -->
                    <a href="<?= esc_url(add_query_arg(['tab' => 'messages', 'chat_id' => $resume_data->user_id], home_url('/employer-dashboard/'))) ?>" class="nkrp-btn-primary">
                        <span class="dashicons dashicons-format-chat"></span> Direct Message
                    </a>
                <?php endif; ?>

                <a href="mailto:<?= esc_attr($resume_data->user_email) ?>" class="nkrp-btn-secondary">
                    <span class="dashicons dashicons-email"></span> Email
                </a>
                
                <?php if (!empty($resume_data->file_path)): ?>
                    <a href="<?= esc_url($resume_data->file_path) ?>" target="_blank" class="nkrp-btn-secondary">
                        <span class="dashicons dashicons-pdf"></span> Download CV
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <button class="nkrp-btn-locked" disabled>
                    <span class="dashicons dashicons-lock"></span> Direct Message
                </button>
                <button class="nkrp-btn-locked" disabled>
                    <span class="dashicons dashicons-lock"></span> Contact Details
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Objective Section -->
    <?php if (!empty($resume_data->objective)): ?>
        <div class="nkrp-profile-section">
            <h3>Professional Summary</h3>
            <p class="nkrp-profile-text"><?= nl2br(esc_html($resume_data->objective)) ?></p>
        </div>
    <?php endif; ?>

    <!-- Timeline Grid -->
    <div class="nkrp-profile-grid">
        
        <!-- Experience -->
        <div class="nkrp-timeline-column">
            <h3><span class="dashicons dashicons-portfolio"></span> Work Experience</h3>
            <div class="nkrp-timeline">
                <?php if(empty($experience)): ?>
                    <p style="color:#94a3b8; font-style:italic;">No experience listed.</p>
                <?php else: ?>
                    <?php foreach ($experience as $exp): ?>
                        <div class="nkrp-timeline-item">
                            <div class="nkrp-timeline-dot"></div>
                            <div class="nkrp-timeline-content">
                                <h4><?= esc_html($exp['job_title'] ?? $exp['title'] ?? '') ?></h4>
                                <!-- BLUR COMPANY NAME IF LOCKED -->
                                <h5 class="<?= !$is_unlocked ? 'nkrp-blurred nkrp-blurred-sm' : '' ?>">
                                    <?= $is_unlocked ? esc_html($exp['company']) : 'Confidential Hospitality Group' ?>
                                </h5>
                                <span class="nkrp-timeline-date"><?= esc_html($exp['start_date'] ?? $exp['date'] ?? '') ?> <?= !empty($exp['end_date']) ? '- ' . esc_html($exp['end_date']) : '' ?></span>
                                <p><?= nl2br(esc_html($exp['description'] ?? $exp['desc'] ?? '')) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Education -->
        <div class="nkrp-timeline-column">
            <h3><span class="dashicons dashicons-welcome-learn-more"></span> Education</h3>
            <div class="nkrp-timeline">
                <?php if(empty($education)): ?>
                    <p style="color:#94a3b8; font-style:italic;">No education listed.</p>
                <?php else: ?>
                    <?php foreach ($education as $edu): ?>
                        <div class="nkrp-timeline-item">
                            <div class="nkrp-timeline-dot"></div>
                            <div class="nkrp-timeline-content">
                                <h4><?= esc_html($edu['degree']) ?></h4>
                                <!-- BLUR INSTITUTION IF LOCKED -->
                                <h5 class="<?= !$is_unlocked ? 'nkrp-blurred nkrp-blurred-sm' : '' ?>">
                                    <?= $is_unlocked ? esc_html($edu['institution']) : 'Private Institution' ?>
                                </h5>
                                <span class="nkrp-timeline-date">Class of <?= esc_html($edu['grad_year'] ?? $edu['year'] ?? '') ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</div>

<style>
    /* SaaS Profile UI */
    .nkrp-public-profile { max-width: 1000px; margin: 40px auto; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color: #334155; }
    
    /* Paywall Banner */
    .nkrp-paywall-banner { background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 20px; margin-bottom: 30px; display: flex; align-items: center; gap: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .nkrp-paywall-banner .dashicons-lock { font-size: 32px; width: 32px; height: 32px; color: #d97706; }
    .nkrp-paywall-text { flex: 1; }
    .nkrp-paywall-text strong { color: #92400e; font-size: 16px; display: block; margin-bottom: 5px; }
    .nkrp-paywall-text p { margin: 0; color: #b45309; font-size: 14px; }
    .nkrp-btn-upgrade { background: #d97706; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; white-space: nowrap; transition: background 0.2s; }
    .nkrp-btn-upgrade:hover { background: #b45309; }

    /* The Blur Effect Magic */
    .nkrp-blurred { color: transparent !important; text-shadow: 0 0 10px rgba(15,23,42,0.8); user-select: none; }
    .nkrp-blurred-sm { text-shadow: 0 0 6px rgba(15,23,42,0.6); }

    /* Header */
    .nkrp-profile-header { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px; display: flex; gap: 30px; align-items: center; margin-bottom: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); flex-wrap: wrap; }
    .nkrp-profile-avatar { width: 100px; height: 100px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #94a3b8; flex-shrink: 0; }
    .nkrp-profile-avatar .dashicons { font-size: 50px; width: 50px; height: 50px; }
    
    .nkrp-profile-title-area { flex: 1; min-width: 250px; }
    .nkrp-profile-title-area h1 { margin: 0 0 5px 0; font-size: 32px; color: #0f172a; }
    .nkrp-profile-title-area h2 { margin: 0 0 15px 0; font-size: 18px; color: #64748b; font-weight: 500; }
    
    .nkrp-skills-container { display: flex; flex-wrap: wrap; gap: 8px; }
    .nkrp-skill-badge { background: #e0e7ff; color: #3730a3; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    
    .nkrp-profile-actions { display: flex; flex-direction: column; gap: 10px; }
    .nkrp-btn-primary { background: #2563eb; color: #fff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; justify-content: center; transition: background 0.2s; }
    .nkrp-btn-primary:hover { background: #1d4ed8; color: #fff; }
    .nkrp-btn-secondary { background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; justify-content: center; transition: background 0.2s; }
    .nkrp-btn-secondary:hover { background: #f8fafc; }
    .nkrp-btn-locked { background: #f1f5f9; color: #94a3b8; border: 1px dashed #cbd5e1; padding: 12px 24px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; justify-content: center; cursor: not-allowed; }

    /* Content Sections */
    .nkrp-profile-section { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px; margin-bottom: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .nkrp-profile-section h3 { margin: 0 0 20px 0; font-size: 20px; color: #0f172a; display: flex; align-items: center; gap: 8px;}
    .nkrp-profile-text { line-height: 1.7; color: #475569; font-size: 16px; margin: 0; }
    
    .nkrp-profile-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
    .nkrp-timeline-column { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .nkrp-timeline-column h3 { margin: 0 0 30px 0; font-size: 20px; color: #0f172a; display: flex; align-items: center; gap: 8px;}
    
    /* Beautiful Timeline */
    .nkrp-timeline { position: relative; padding-left: 20px; border-left: 2px solid #e2e8f0; }
    .nkrp-timeline-item { position: relative; margin-bottom: 30px; }
    .nkrp-timeline-item:last-child { margin-bottom: 0; }
    .nkrp-timeline-dot { position: absolute; left: -27px; top: 5px; width: 12px; height: 12px; background: #2563eb; border-radius: 50%; border: 3px solid #eff6ff; }
    .nkrp-timeline-content h4 { margin: 0 0 5px 0; font-size: 16px; color: #0f172a; }
    .nkrp-timeline-content h5 { margin: 0 0 5px 0; font-size: 14px; color: #64748b; font-weight: 500; }
    .nkrp-timeline-date { font-size: 12px; color: #94a3b8; font-weight: 600; display: block; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;}
    .nkrp-timeline-content p { margin: 0; font-size: 14px; color: #475569; line-height: 1.6; }

    @media (max-width: 768px) {
        .nkrp-profile-grid { grid-template-columns: 1fr; }
        .nkrp-profile-header { padding: 25px; flex-direction: column; text-align: center; }
        .nkrp-skills-container { justify-content: center; }
        .nkrp-paywall-banner { flex-direction: column; text-align: center; }
    }
</style>