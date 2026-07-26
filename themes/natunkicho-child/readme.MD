📁 1. The Backend Engine (inc/lms/)
This directory contains all the business logic, API integrations, and database architecture. It completely bypasses the main theme root to keep the environment clean.

lms-init.php

What it is: The central hub. It requires/includes all other LMS files and enqueues the custom premium CSS (learning-marketplace.css).

Developer Note: If you create a new PHP logic file for the LMS, you must require_once it here.

lms-cleanup.php

What it is: The security and UX firewall. It hides default WordPress clutter and forcefully blocks non-admin users from accessing /wp-admin/, redirecting them seamlessly to the frontend Tutor LMS dashboard.

Future Edits: Add any code here that removes standard WP meta boxes or hides Tutor LMS upsell banners.

lms-affiliate-manager.php

What it is: A meta box engine for native Tutor LMS courses. It allows admins to quickly paste external links into internal courses (useful for manual, one-off affiliate links).

lms-slicewp-sync.php

What it is: The Dashboard Unifier. It injects the SliceWP shortcode directly into a custom tab within the Tutor LMS frontend dashboard.

Developer Note: Ensures affiliate promoters do not have to log into two separate dashboards.

lms-marketplace-data.php

What it is: Registers the custom data structures for the marketplace, specifically the nk_tutor (1-on-1 Mentors) and nk_institute (B2B Partners) Custom Post Types.

Crucial Feature: Contains the Template Router. It intercepts WordPress template loading and forces these CPTs to use the files hidden in the templates/ folder instead of the theme root.

lms-external-courses.php

What it is: The crown jewel of the aggregator. It creates the nk_external_course CPT, the nk_course_provider taxonomy, and builds the API Control Room under the Tutor LMS menu.

How it works: It features a highly secure settings panel for storing multi-line credentials (e.g., Rouxbe Network IDs + API Keys) and an AJAX-powered Importer Dashboard that fetches course data and downloads external featured images into the WordPress media library.

Future Edits: When Natunkicho is approved for live API access by partners (Coursera, edX), developers will replace the demo array data in the nk_process_api_fetch() AJAX function with real wp_remote_get() endpoint mappings.

lms-custom-fields.php

What it is: The designated file for any future Advanced Custom Fields (ACF) or custom meta boxes needed for standard courses, mentors, or institutes (e.g., adding a "Years of Experience" field for Mentors).

📂 The Hidden Templates (inc/lms/templates/)
These files handle the single-page layout for custom post types.

single-nk_tutor.php: The frontend profile design for 1-on-1 Mentors.

single-nk_institute.php: The frontend profile design for B2B Partners (Universities/Hotel Brands).

single-nk_external_course.php: The SEO buffer page for API courses. It displays the course details, partner logo, and an affiliate redirect button, and queries 3 "Related Courses" at the bottom. Developer Note: Never redirect users instantly to Coursera; always land them here first so Google indexes Natunkicho as the authority.

🎨 2. The Frontend Architecture (template-parts/learning/)
This directory contains the modular UI components that build the Coursera/MasterClass-style marketplace.

nav-marketplace.php

What it is: The Smart Header.

Logic: If logged out, it shows a "Join Academy" bulletproof CSS dropdown with specific registration routes (Student, Instructor, Institute) and a secure login button pointing to a dedicated /academy-login/ shortcode page. If logged in (and enrolled in a course), it morphs into an "Academy Dashboard" button.

section-course-grid.php

What it is: The Unified Display Grid.

Logic: Uses a WP_Query array to fetch BOTH 'courses' (Internal) and 'nk_external_course' (External) simultaneously. An if/else statement inside the loop applies dynamic UI badges (e.g., "Partner: Rouxbe" vs "Natunkicho Academy").

card-course.php (Future Architecture)

Purpose: To extract the HTML loop from section-course-grid.php into its own reusable component. Useful if you want to display a course card on a blog post or user profile.

section-hero.php & section-dynamic-promo.php (Future Architecture)

Purpose: The top-of-page search bars and dynamic promotional banners (e.g., "Get 20% off Culinary Arts this week").

section-categories.php (Future Architecture)

Purpose: The visual grid of categories (Culinary, Management, Tech). Because we linked nk_external_course to the Tutor LMS category taxonomy, clicking a category here will automatically filter both internal and external courses.

section-institutes.php & section-tutors.php (Future Architecture)

Purpose: Carousel sliders or grids highlighting top B2B Partners (nk_institute) and elite 1-on-1 Mentors (nk_tutor).

section-roadmaps.php (Future Architecture)

Purpose: UI component for "Career Paths" (e.g., "How to become a General Manager"), grouping multiple courses into a sequential learning track.

section-testimonials.php (Future Architecture)

Purpose: Social proof section pulling reviews from successful students.

🚀 3. Status Report & Future Roadmap
✅ What We Have Successfully Completed
Architecture Separation: We successfully isolated heavy API affiliate courses from native LMS courses, protecting the database from bloat while visually merging them on the frontend.

API Control Room: Built a robust, multi-line credential storage system and AJAX importer tool in the backend.

Smart Navigation & Auth: Created a conditional dropdown navigation and custom, centralized authentication pages (/academy-login/) that keep LMS users strictly separated from Job Board users.

Template Routing: Established a clean root directory by routing custom post types through the inc/lms/templates/ folder.

Unified Dashboards: Injected SliceWP affiliate tracking directly into the Tutor LMS student/instructor dashboard.

🔜 What Needs to be Done Next (Future Developer Tasks)
Map the Live APIs: Once Natunkicho receives live API endpoints from Rouxbe, Typsy, and Coursera, a developer needs to open lms-external-courses.php and replace the demo $fetched_data array with actual JSON parsing logic (wp_remote_get).

Build the Institute Dashboard: Currently, students and tutors have a dashboard. We need to create a custom frontend management view for B2B Institutes to view analytics on how many students have enrolled in their specific courses.

Flesh out the Frontend Sections: Build the HTML/CSS for section-roadmaps.php, section-hero.php, and section-categories.php to complete the homepage visual design.

Search Integration: Implement an AJAX search bar on the frontend that queries both the courses and nk_external_course databases simultaneously.