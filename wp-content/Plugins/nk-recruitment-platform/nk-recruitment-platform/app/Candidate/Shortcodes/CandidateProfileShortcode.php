<?php

declare(strict_types=1);

namespace NKRecruitment\Candidate\Shortcodes;

if (!defined('ABSPATH')) {
    exit;
}

class CandidateProfileShortcode
{
    public function register(): void
    {
        add_shortcode('nk_candidate_profile', [$this, 'render']);
    }

    public function render(): string
    {
        $candidate_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        
        if ($candidate_id <= 0) {
            return '<div style="padding:40px; text-align:center; background:#f8fafc; border-radius:12px; color:#64748b; margin: 40px auto; max-width: 800px;"><h2>Candidate Not Found</h2><p>This profile does not exist or has been removed.</p></div>';
        }

        $user_info = get_userdata($candidate_id);
        if (!$user_info || (!in_array('nkrp_candidate', (array)$user_info->roles) && !in_array('candidate', (array)$user_info->roles))) {
            return '<div style="padding:40px; text-align:center; background:#f8fafc; border-radius:12px; color:#64748b; margin: 40px auto; max-width: 800px;"><h2>Candidate Unavailable</h2><p>This user is not registered as a candidate.</p></div>';
        }

        // Check if the viewer is Premium or Administrator
        $current_viewer_id = get_current_user_id();
        $is_premium = apply_filters('nkrp_is_user_premium', false, $current_viewer_id);
        
        // Admins and the Candidate themselves always get full Premium view
        if (current_user_can('manage_options') || $current_viewer_id === $candidate_id) {
            $is_premium = true;
        }

        // Fetch Candidate Meta
        $title = get_user_meta($candidate_id, '_nkrp_professional_title', true);
        $bio = get_user_meta($candidate_id, '_nkrp_bio', true);
        $skills = get_user_meta($candidate_id, '_nkrp_skills', true);
        $phone = get_user_meta($candidate_id, '_nkrp_phone', true);
        $linkedin = get_user_meta($candidate_id, '_nkrp_linkedin', true);
        $city = get_user_meta($candidate_id, '_nkrp_city', true);
        $country = get_user_meta($candidate_id, '_nkrp_country', true);
        $location = trim((string)$city . ' ' . (string)$country);
        $experience_data = get_user_meta($candidate_id, '_nkrp_experience_data', true) ?: [];
        $education_data = get_user_meta($candidate_id, '_nkrp_education_data', true) ?: [];
        $photo_id = get_user_meta($candidate_id, '_nkrp_photo_id', true);
        
        $photo_url = $photo_id ? wp_get_attachment_image_url((int)$photo_id, 'medium') : '';

        // --- PREMIUM DATA MASKING ---
        if ($is_premium) {
            $display_name = trim($user_info->first_name . ' ' . $user_info->last_name) ?: $user_info->display_name;
            $display_photo = $photo_url 
                ? '<img src="' . esc_url($photo_url) . '" style="width:100px; height:100px; border-radius:50%; object-fit:cover; border:3px solid #3b82f6;">' 
                : '<div style="width:100px; height:100px; background:#e2e8f0; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:32px; color:#64748b; border:3px solid #cbd5e1;">' . esc_html(strtoupper(substr($display_name, 0, 1))) . '</div>';
        } else {
            $display_name = 'Candidate #' . $candidate_id;
            $display_photo = '<div style="width:100px; height:100px; background:#e2e8f0; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:32px; color:#64748b; filter: blur(4px); border:3px solid #cbd5e1;"><span class="dashicons dashicons-lock"></span></div>';
        }

        ob_start();
        ?>
        <div class="nkrp-candidate-profile" style="max-width: 900px; margin: 40px auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #334155;">
            
            <?php if (!$is_premium): ?>
                <div style="background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%); border: 1px solid #fde047; padding: 20px; border-radius: 12px; margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span class="dashicons dashicons-lock" style="font-size: 32px; width: 32px; height: 32px; color: #a16207;"></span>
                        <div>
                            <h3 style="margin: 0 0 5px 0; color: #854d0e; font-size: 18px; font-weight: 700;">Premium Content Locked</h3>
                            <p style="margin: 0; color: #a16207; font-size: 14px;">Upgrade your Employer account to unlock this candidate's real name, contact info, and full profile details.</p>
                        </div>
                    </div>
                    <a href="<?= esc_url(home_url('/membership/')) ?>" style="background: #a16207; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; transition: background 0.2s;">Upgrade Account</a>
                </div>
            <?php endif; ?>

            <div style="background: #fff; padding: 35px; border-radius: 16px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 25px; margin-bottom: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); flex-wrap: wrap;">
                <?= $display_photo ?>
                <div style="flex: 1; min-width: 250px;">
                    <h1 style="margin: 0 0 5px 0; font-size: 26px; color: #0f172a; font-weight: 800;"><?= esc_html($display_name) ?></h1>
                    <div style="font-size: 16px; color: #2563eb; font-weight: 600; margin-bottom: 12px;"><?= esc_html($title ?: 'Hospitality Professional') ?></div>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 15px; font-size: 14px; color: #64748b;">
                        <?php if ($location): ?>
                            <span style="display: flex; align-items: center; gap: 5px;"><span class="dashicons dashicons-location"></span> <?= esc_html($location) ?></span>
                        <?php endif; ?>

                        <?php if ($is_premium): ?>
                            <?php if ($phone): ?>
                                <span style="display: flex; align-items: center; gap: 5px;"><span class="dashicons dashicons-phone"></span> <?= esc_html($phone) ?></span>
                            <?php endif; ?>
                            <?php if ($user_info->user_email): ?>
                                <span style="display: flex; align-items: center; gap: 5px;"><span class="dashicons dashicons-email"></span> <?= esc_html($user_info->user_email) ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="display: flex; align-items: center; gap: 5px; color: #94a3b8;"><span class="dashicons dashicons-lock"></span> Contact info hidden</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($is_premium && $current_viewer_id !== $candidate_id): ?>
                    <div>
                        <a href="<?= esc_url(home_url('/employer-dashboard/?tab=messages&new_msg=' . $candidate_id)) ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #2563eb; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 15px;"><span class="dashicons dashicons-email-alt"></span> Send Message</a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($skills)): ?>
                <div style="background: #fff; padding: 35px; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 30px;">
                    <h2 style="margin: 0 0 15px 0; font-size: 18px; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; font-weight: 700;">Core Skills & Expertise</h2>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php foreach (explode(',', $skills) as $skill): ?>
                            <span style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600;"><?= esc_html(trim($skill)) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($bio)): ?>
                <div style="background: #fff; padding: 35px; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 30px;">
                    <h2 style="margin: 0 0 15px 0; font-size: 18px; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; font-weight: 700;">Professional Summary</h2>
                    <div style="font-size: 15px; line-height: 1.7; color: #475569;">
                        <?= wp_kses_post(nl2br($bio)) ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($experience_data)): ?>
                <div style="background: #fff; padding: 35px; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 30px;">
                    <h2 style="margin: 0 0 20px 0; font-size: 18px; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; font-weight: 700;">Work Experience</h2>
                    <?php foreach ($experience_data as $exp): ?>
                        <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9;">
                            <h3 style="margin: 0 0 4px 0; font-size: 17px; color: #0f172a; font-weight: 700;"><?= esc_html($exp['title'] ?? 'Role Title') ?></h3>
                            <div style="font-size: 14px; color: #2563eb; font-weight: 600; margin-bottom: 8px;">
                                <?= esc_html($is_premium ? ($exp['company'] ?? 'Confidential') : 'Confidential Company') ?> 
                                <span style="color: #94a3b8; font-weight: 400; margin-left: 8px;">(<?= esc_html($exp['date'] ?? '') ?>)</span>
                            </div>
                            <?php if (!empty($exp['desc'])): ?>
                                <div style="font-size: 14px; line-height: 1.6; color: #475569;">
                                    <?= wp_kses_post(nl2br($exp['desc'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($education_data)): ?>
                <div style="background: #fff; padding: 35px; border-radius: 16px; border: 1px solid #e2e8f0;">
                    <h2 style="margin: 0 0 20px 0; font-size: 18px; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; font-weight: 700;">Education & Certifications</h2>
                    <?php foreach ($education_data as $edu): ?>
                        <div style="margin-bottom: 15px;">
                            <h3 style="margin: 0 0 2px 0; font-size: 16px; color: #0f172a; font-weight: 700;"><?= esc_html($edu['degree'] ?? '') ?></h3>
                            <div style="font-size: 14px; color: #64748b;">
                                <?= esc_html($edu['institution'] ?? '') ?> <?= !empty($edu['year']) ? '(' . esc_html($edu['year']) . ')' : '' ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
        <?php
        return ob_get_clean();
    }
}