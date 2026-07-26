<?php
/**
 * Template Part: Student & Learner Logged-In View
 */
$current_user = wp_get_current_user();
$first_name = $current_user->user_firstname ? $current_user->user_firstname : $current_user->display_name;

// Placeholder metrics for future LMS integration (LearnDash/TutorLMS)
$enrolled_courses = 2;
$completed_courses = 0;
$certificates_earned = 0;
?>

<style>
    .nk-student-dashboard { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; padding: 60px 20px; min-height: 80vh; }
    .nk-dash-container { max-width: 1200px; margin: 0 auto; width: 100%; }
    
    .nk-student-header { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border-radius: 16px; padding: 40px; color: #fff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; box-shadow: 0 10px 30px rgba(139,92,246,0.15); margin-bottom: 40px; }
    .nk-student-header h1 { margin: 0 0 10px 0; font-size: 32px; font-weight: 800; color: #fff; line-height: 1.2; }
    .nk-student-header p { margin: 0; color: #ddd6fe; font-size: 16px; }
    .nk-btn-student { background: #fff; color: #6d28d9; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 15px; transition: 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .nk-btn-student:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.15); }

    .nk-metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 50px; }
    .nk-metric-card { background: #fff; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; border-left: 5px solid #8b5cf6; }
    .nk-metric-card h4 { margin: 0 0 10px 0; color: #64748b; font-size: 14px; text-transform: uppercase; font-weight: 700; }
    .nk-metric-card .number { font-size: 40px; font-weight: 900; color: #0f172a; margin: 0; line-height: 1; }
    
    .nk-section-title { font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 20px; }
    .nk-student-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 50px; }
    .nk-student-tool { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 30px; display: flex; align-items: flex-start; gap: 20px; text-decoration: none; transition: 0.3s; }
    .nk-student-tool:hover { border-color: #8b5cf6; box-shadow: 0 10px 30px rgba(139,92,246,0.08); transform: translateY(-3px); }
    .nk-student-tool-icon { font-size: 32px; background: #f5f3ff; color: #8b5cf6; width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .nk-student-tool h3 { margin: 0 0 8px 0; color: #0f172a; font-size: 20px; font-weight: 800; }
    .nk-student-tool p { margin: 0; color: #64748b; font-size: 14px; line-height: 1.6; }
</style>

<div class="nk-student-dashboard">
    <div class="nk-dash-container">
        
        <div class="nk-student-header">
            <div>
                <h1>Student Learning Hub 🎓</h1>
                <p>Welcome back, <?php echo esc_html($first_name); ?>. Continue your journey to hospitality excellence.</p>
            </div>
            <div>
                <a href="<?php echo esc_url(site_url('/courses/')); ?>" class="nk-btn-student">Browse New Courses</a>
            </div>
        </div>

        <div class="nk-metrics-grid">
            <div class="nk-metric-card" style="border-left-color: #8b5cf6;">
                <h4>Active Courses</h4>
                <div class="number"><?php echo intval($enrolled_courses); ?></div>
            </div>
            <div class="nk-metric-card" style="border-left-color: #10b981;">
                <h4>Completed Courses</h4>
                <div class="number"><?php echo intval($completed_courses); ?></div>
            </div>
            <div class="nk-metric-card" style="border-left-color: #f59e0b;">
                <h4>Certificates Earned</h4>
                <div class="number"><?php echo intval($certificates_earned); ?></div>
            </div>
        </div>

        <h2 class="nk-section-title">Your Learning Portal</h2>
        <div class="nk-student-actions">
            <a href="<?php echo esc_url(site_url('/dashboard/?tab=my-courses')); ?>" class="nk-student-tool">
                <div class="nk-student-tool-icon">▶️</div>
                <div>
                    <h3>My Courses</h3>
                    <p>Resume your video tutorials, take quizzes, and track your syllabus progress.</p>
                </div>
            </a>
            
            <a href="<?php echo esc_url(site_url('/dashboard/?tab=my-certificates')); ?>" class="nk-student-tool">
                <div class="nk-student-tool-icon" style="color: #f59e0b; background: #fffbeb;">🏆</div>
                <div>
                    <h3>My Certificates</h3>
                    <p>Download and print your verified training certificates to attach to your CV.</p>
                </div>
            </a>
            
            <a href="<?php echo esc_url(site_url('/blog/')); ?>" class="nk-student-tool">
                <div class="nk-student-tool-icon" style="color: #0A66C2; background: #eff6ff;">📚</div>
                <div>
                    <h3>Free Knowledge Base</h3>
                    <p>Read open-access articles on food costing, kitchen management, and recipes.</p>
                </div>
            </a>
        </div>

    </div>
</div>