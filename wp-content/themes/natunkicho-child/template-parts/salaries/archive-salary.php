<?php
if (!defined('ABSPATH')) exit;
get_header(); 
?>
<style>
    .nk-salary-hub { background: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; min-height: 80vh; padding-bottom: 80px; }
    .nk-salary-hero { text-align: center; padding: 120px 20px 140px 20px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; }
    .nk-salary-hero h1 { font-size: 48px; font-weight: 900; margin-bottom: 20px; line-height: 1.2; }
    .nk-salary-hero p { font-size: 20px; color: #cbd5e1; max-width: 700px; margin: 0 auto; line-height: 1.6; }
    .nk-salary-container { max-width: 1200px; margin: 0 auto; width: 100%; padding: 0 20px; box-sizing: border-box; }
    .nk-salary-badge { display: inline-block; background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #34d399; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 700; margin-bottom: 20px; letter-spacing: 1px; text-transform: uppercase; }
    
    /* Typewriter Cursor CSS */
    .nk-type-wrap { color: #10b981; border-right: 3px solid #10b981; padding-right: 8px; animation: blink 0.75s step-end infinite; }
    @keyframes blink { 50% { border-color: transparent; } }
</style>

<div class="nk-salary-hub">
    
    <div class="nk-salary-hero">
        <span class="nk-salary-badge">Global Market Data</span>
        <h1><span class="nk-type-wrap" id="nk-type-text">Hospitality</span><br>Salary Intelligence</h1>
        <p>Discover real-time compensation data, compare global salaries, and know your exact market value to negotiate better offers.</p>
    </div>

    <div class="nk-salary-container">
        
        <?php echo do_shortcode('[nk_salary_search]'); ?>
        
        <?php echo do_shortcode('[nk_salary_grid]'); ?>

    </div>
</div>

<script>
// Dynamic Typewriter Effect
document.addEventListener("DOMContentLoaded", function() {
    const words = ["Hospitality", "Executive Chef", "General Manager", "Front Desk Agent", "F&B Director", "Housekeeping"];
    let wordIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    const typeText = document.getElementById("nk-type-text");

    function typeEffect() {
        const currentWord = words[wordIndex];
        
        if (isDeleting) {
            typeText.innerText = currentWord.substring(0, charIndex - 1);
            charIndex--;
        } else {
            typeText.innerText = currentWord.substring(0, charIndex + 1);
            charIndex++;
        }

        let typeSpeed = isDeleting ? 50 : 100;

        // If word is finished typing
        if (!isDeleting && charIndex === currentWord.length) {
            typeSpeed = 2500; // Pause for 2.5 seconds so they can read it
            isDeleting = true;
        } 
        // If word is completely deleted
        else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            wordIndex = (wordIndex + 1) % words.length; // Move to next word
            typeSpeed = 500; // Pause before typing new word
        }

        setTimeout(typeEffect, typeSpeed);
    }

    // Start effect after 1 second
    setTimeout(typeEffect, 1000);
});
</script>

<?php get_footer(); ?>