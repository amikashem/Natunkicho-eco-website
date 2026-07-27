Here Is the full overview of my websites)
NK Recruitment Platform (NEW)
Plugins we have follwoing PLugins 
Wp-job-manager, ( will remove after new build)
SliceWP
tutorLMS,
Elementor
Nk-AI-Core, ( all NK- plugins are my own)
Rank math 
Nk-SEO, (inactive)
NK-social_distributor,
Nk-email-engine
Woocmmerce,
EPs-Payment with woocommerce,
post-to-pdf-exporter,


( main theme is astra)
natunkicho-child/
│
├── assets/
│   ├── css/
│   │   ├── ai.css                         (AI feature styling)
│   │   ├── dashboard.css                  (General dashboard design)
│   │   ├── draft_header.css               (Custom header layout styles)
│   │   ├── dropdown-menu.css              (Dropdown menu styling)
│   │   ├── employer.css                   (Employer area styles)
│   │   ├── home.css                       (Homepage styling)
│   │   ├── jobs.css                       (Job listing and job page styles)
│   │   ├── nk-calculator.css              (Calculator UI styles)
│   │   ├── nk-category-grid.css           (Category grid shortcode styles)
│   │   ├── nk-category-grid-style2.css    (Alternative category grid design)
│   │   ├── nk-contact-form.css            (Contact form styling)
│   │   ├── nk-dashboard.css               (Custom NK dashboard styles)
│   │   ├── nk-dynamic-menu-section.css    (Dynamic menu section styles)
│   │   ├── nk-footer-slider.css           (Footer slider styling)
│   │   ├── nk-hero-slider.css             (Hero/banner slider styling)
│   │   ├── nksp-single.min.css            (Single post template styles)
│   │   ├── profile.css                    (User profile page styling)
│   │   └── translate.css                  (Language translation button styles)
        ---learning-marketplace.css       (NEW: Dedicated styles for the learning platform)
│   │
│   ├── js/
│   │   ├── main.js                        (Global site JavaScript)
│   │   ├── nk-calculator-core.js          (Calculator core logic)
│   │   ├── nk-category-grid.js            (Category grid interactions)
│   │   ├── nk-category-grid-style2.js     (Category grid v2 interactions)
│   │   ├── nk-contact-form.js             (Contact form functionality)
│   │   ├── nk-dynamic-menu-section.js     (Dynamic menu behavior)
│   │   ├── nk-dropdown-menu.js            (Dropdown menu functionality)
│   │   ├── nk-fc-food-cost.js             (Food cost calculator logic)
│   │   ├── nk-food-cost.js                (Food cost tool functions)
│   │   ├── nk-footer-slider.js            (Footer slider controls)
│   │   ├── nk-hero-slider.js              (Hero slider controls)
│   │   ├── nksp-single.min.js             (Single post template scripts)
│   │   ├── save-jobs.js                   (Save/Favorite jobs feature)
│   │   └── translate.js                   (Translation functionality)
         ---learning-marketplace.js        (NEW: Sticky nav, AJAX filters, and slider logic)
│   │
│   └── images/                            (Theme images & icons)
│
├── inc/
│   │
│   ├── ai/
│   │   ├── ai-cv-builder.php              (AI-generated CV builder)
│   │   └── ai-skill-suggestions.php       (AI skill recommendations)
│   │
│   ├── salaries/
│   │   ├── salary-database.php            (Salary database tables & data storage)
│   │   ├── salary-router.php              (Salary URL routing system)
│   │   ├── salary-shortcodes.php          (Salary calculators & comparison tools)
│   │   ├── salary-dashboard.php           (Salary widgets in dashboards)
│   │   └── salary-ai-insights.php         (AI salary insights automation)
│   │
│   ├── api/
│   │   ├── adzuna.php                     (Adzuna API integration)
│   │   ├── api-sync-manager.php           (Controls all API imports/sync)
│   │   ├── careerjet.php                  (CareerJet API integration)
│   │   ├── findwork.php                   (FindWork API integration)
│   │   └── jsearch.php                    (JSearch API integration)
│   │
│   ├── auth/
│   │   ├── auth-functions.php             (Authentication helper functions)
│   │   ├── auth-split-screen.php          (Custom login/register layout)
│   │   ├── login.php                      (Login system)
│   │   ├── nk-social-oauth.php            (Google/LinkedIn/Facebook login)
│   │   └── register.php                   (User registration system)
│   │
│   ├── candidate/
│   │   ├── candidate-alerts.php           (Job alerts for candidates)
│   │   ├── candidate-applied-jobs.php     (Applied jobs management)
│   │   ├── candidate-profile.php          (Candidate profile management)
│   │   ├── candidate-saved-jobs.php       (Saved jobs functionality)
│   │   └── cv-public-view.php             (Public CV display page)
│   │
│   ├── core/
│   │   ├── init.php                       (Loads theme modules)
│   │   ├── notifications.php              (Site notifications system)
│   │   ├── roles.php                      (Custom user roles & permissions)
│   │   ├── theme-optimizations.php        (Performance optimizations)
│   │   └── custom-redirects.php           (Custom redirects & routing rules)
│   │
│   ├── dashboard/
│   │   ├── dashboard-router.php           (Dashboard page routing)
│   │   ├── settings.php                   (Dashboard settings page)
│   │   ├── sidebar.php                    (Dashboard sidebar menu)
│   │   └── widgets.php                    (Dashboard widgets)
│   │
│   ├── employer/
│   │   ├── company-profile.php            (Employer company profile)
│   │   ├── employer-applications.php      (Manage job applications)
│   │   ├── employer-dashboard.php         (Employer dashboard)
│   │   ├── employer-jobs.php              (Employer job management)
│   │   └── talent-database.php            (Candidate database access)
│   │
│   ├── jobs/
│   │   ├── ecosystem-teaser.php           (Promotional ecosystem blocks)
│   │   ├── featured-jobs-shortcode.php    (Featured jobs shortcode)
│   │   ├── jobs-apply.php                 (Job application process)
│   │   ├── jobs-functions.php             (Core job functions)
│   │   ├── jobs-save.php                  (Save jobs backend logic)
│   │   ├── jobs-template.php              (Custom job templates)
│   │   └── taxonomy-fixes.php             (Job category/location fixes)
│   │
│   ├── learning/
│   │   └── learning-alerts.php            (Course & learning alerts)
        
│   ├── search/
│   │   ├── category-links-shortcode.php   (Category links shortcode)
│   │   ├── hero-search-shortcode.php      (Homepage hero search)
│   │   ├── job-portal-shortcode.php       (Job portal search widget)
│   │   └── unified-search.php             (Global search engine)
│   │
│   ├── woocommerce/
│   │   └── saas-engine.php                (Subscription & SaaS logic)
│   │
│   └── cv-builder/
│       ├── cv-renderer.php                (CV output renderer)
│       ├── cv-builder.php                 (Main CV builder engine)
│       ├── cv-sections.php                (CV section management)
│       ├── cv-pdf-export.php              (PDF export functionality)
│       ├── cv-template-modern.php         (Modern CV template)
│       └── cv-premium-templates.php       (Premium CV templates)
     ---lms/
        ├── lms-affiliate-manager.php
        ├── lms-cleanup.php
        ├── lms-custom-fields.php
        ├── lms-external-courses.php
        ├── lms-init.php
        ├── lms-marketplace-data.php
        ├── lms-slicewp-sync.php
        │
        └── templates/
            ├── single-nk_external_course.php
            ├── single-nk_institute.php
            └── single-nk_tutor.php     
── -tutor/
    │                              *(Tutor LMS automatically looks for this folder in your theme)*
│   ├── archive-course.php         (Our premium Course Grid and Sidebar Filters)
│   ├── single-course.php          (The beautiful detail page with the "Enroll / Go to Partner" button)
│   └── /loop/                     
│       └── course.php             (Our custom individual course card design)
│
├── includes/
│   │
│   ├── helpers/
│   │   └── meta-viewport.php              (Mobile viewport settings)
│   │
│   ├── setup/
│   │   ├── content-width.php              (Content width configuration)
│   │   ├── elementor-settings.php         (Elementor compatibility settings)
│   │   ├── enqueue-calculator.php         (Loads calculator assets)
│   │   ├── enqueue-scripts.php            (Loads CSS & JS assets)
│   │   ├── sidebar-register.php           (Registers sidebars)
│   │   └── theme-supports.php             (Theme feature support)
│   │
│   ├── shortcodes/
│   │   ├── dropdown-menu.php              (Dropdown menu shortcode)
│   │   ├── latest-jobs-widget.php         (Latest jobs widget)
│   │   ├── nk-calculator.php              (Calculator shortcode)
│   │   ├── nk-category-grid-shortcode.php (Category grid shortcode)
│   │   ├── nk-category-grid-style2.php    (Category grid v2 shortcode)
│   │   ├── nk-contact-form.php            (Contact form shortcode)
│   │   ├── nk-dynamic-menu-section.php    (Dynamic menu shortcode)
│   │   ├── nk-food-cost-calculator.php    (Food cost calculator shortcode)
│   │   ├── nk-footer-slider.php           (Footer slider shortcode)
│   │   ├── nk-hero-slider.php             (Hero slider shortcode)
│   │   ├── nk-pricing-tables.php          (Pricing table shortcode)
│   │   ├── nk-yield-loss-calculator.php   (Yield loss calculator)
│   │   ├── post-grid-shortcode.php        (Post grid display shortcode)
│   │   └── nk-dual-search-shortcode.php   (Dual search bar shortcode)
│   │
│   ├── translate/
│   │   ├── translate-button.php           (Translate button output)
│   │   └── translate-functions.php        (Translation functions)
│   │
│   ├── widgets/
│   │   ├── custom-widgets.php             (Custom Elementor widgets)
│   │   └── floating-chat.php              (Floating chat widget)
│   │
│   └── mailchimp-handler.php              (Mailchimp integration)
  
├── job_manager/
│   ├── content-job_listing.php            (Job listing card template)
│   └── job-submit.php                     (Custom job submission form)
│
├── template-parts/
│   │
│   ├── category-bar/
│   │   ├── category-grid-template.php     (Category grid layout)
│   │   ├── nk-category-bar.css            (Category bar styles)
│   │   ├── nk-category-bar.js             (Category bar scripts)
│   │   └── nk-category-bar.php            (Category bar template)
│   │
│   ├── salaries/
│   │   ├── archive-salary.php             (Salary hub page)
│   │   ├── single-salary.php              (Single salary page)
│   │   ├── calc-affordability.php         (Affordability calculator UI)
│   │   └── calc-compare.php               (Salary comparison UI)
│   │
│   ├── post-sections/
│   │   └── post-grid-section.php          (Post grid content section)
│   │
│   ├── product-slider/
│   │   ├── nk-product-slider.css          (Product slider styles)
│   │   ├── nk-product-slider.js           (Product slider scripts)
│   │   └── nk-product-slider.php          (Product slider template)
│   │
│   ├── single/
│   │   ├── author-box.php                 (Single post author box)
│   │   ├── comments.php                   (Comments template)
│   │   ├── content-single-nksp.php        (Single post content layout)
│   │   └── sidebar-nksp.php               (Single post sidebar)
│   │
│   └── Home/
│       ├── employer.php                   (Employer homepage section)
│       ├── candidate.php                  (Candidate homepage section)
│       └── guest.php                      (Guest homepage section)
    learning/                          (NEW: Modular UI frontend components)
│       ├── section-hero.php               (NEW: Hero banner with search integration)
│       ├── nav-marketplace.php            (NEW: The sticky floating platform navigation)
│       ├── section-categories.php         (NEW: Featured hospitality categories grid)
│       ├── section-institutes.php         (NEW: Dynamic institute carousel)
│       ├── section-course-grid.php        (NEW: Standardized course/affiliate cards layout)
│       ├── section-roadmaps.php           (NEW: Interactive career roadmap cards)
│       ├── section-dynamic-promo.php      (NEW: Admin-controlled flexible ad/promo block)
│       └── section-tutors.php             (NEW: Private tutor profiles grid)
        ├── card-course.php
        ├── section-testimonials.php
│       
│
├── woocommerce/
│   └── archive-product.php                (WooCommerce shop archive)
│
├── header.php                             (Global site header)
├── category.php                           (Category archive template)
├── functions.php                          (Main theme bootstrap file)
├── page.php                               (Default page template)
├── screenshot.jpg                         (Theme preview image)
├── single-job_listing.php                 (Single job page template)
├── single.php                             (Default single post template)
├── style.css                              (Theme information & global styles)
├── structure.txt                          (Developer reference structure file)
├── tag.php                                (Tag archive template)
└── template-homepage.php                  (Custom homepage template)
├── template-learning-marketplace.php      (NEW: The standalone page template bringing it all together)
├── single-nk_course.php                   (NEW: Detail page template for individual courses)
└── single-nk_institute.php
