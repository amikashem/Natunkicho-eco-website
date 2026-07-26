<?php
/**
 * Template Name: Founder Landing Page
 * Description: A premium, fast, and lightweight executive landing page for the founder of NatunKicho.
 */

// Block direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
// ==========================================
// CENTRALIZED IMAGE LINKS (REPLACE THESE)
// ==========================================
$fnd_images = [
    'founder_photo'  => 'https://i0.wp.com/natunkicho.com/wp-content/uploads/2025/10/Kashem.jpg?resize=600%2C653&ssl=1', // Add your main portrait URL here
    'country_uk'     => 'https://cdn-icons-png.flaticon.com/512/9746/9746676.png', 
    'country_uae'    => 'https://cdn-icons-png.flaticon.com/512/9746/9746676.png', 
    'country_ksa'    => 'https://cdn-icons-png.flaticon.com/512/9746/9746676.png', 
    'project_hub'    => 'https://i0.wp.com/natunkicho.com/wp-content/uploads/2025/09/From-Kitchen-to-Customer-The-2025-Food-Entrepreneurs-Essential-Playbook.png?w=600&ssl=1', 
    'project_consult'=> 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ2nc_yyP6lWJZujjO0yp8O84P3l829DeTwwUVCBySurFwB6fnQXdHm8d4&s=10', 
    'project_academy'=> '', 
    'cert_haccp'     => '', 
    'cert_leadership'=> '', 
    'cert_digital'   => '', 
    'cert_ai'        => '', 
    'award_innovator'=> 'https://media.licdn.com/dms/image/v2/C512DAQEsdWJLWmxJyg/profile-treasury-image-shrink_800_800/profile-treasury-image-shrink_800_800/0/1602196870865?e=1784282400&v=beta&t=aekt70GgMGjAcmdRDrqukH9d4TAAuLmEkUQBPpDCJVI', 
    'award_training' => 'https://media.licdn.com/dms/image/v2/C562DAQG2Ud-ymmRCNA/profile-treasury-image-shrink_800_800/profile-treasury-image-shrink_800_800/0/1623300628061?e=1784282400&v=beta&t=80i2z6ZvkpiI2A1X9NzQrqh2IdCUrFmqpJKEk4PvV_0', 
    'award_pioneer'  => '', 
    'avatar_rahat'   => '', 
    'avatar_sarah'   => '', 
];

/**
 * Helper function to render image tags gracefully or display structural fallbacks
 */
function fnd_render_image($url, $placeholder_text, $ratio_class) {
    if ( ! empty($url) ) {
        echo '<img src="' . esc_url($url) . '" alt="' . esc_attr($placeholder_text) . '" class="fnd-real-img ' . esc_attr($ratio_class) . '">';
    } else {
        echo '<div class="fnd-image-placeholder ' . esc_attr($ratio_class) . '"><span>' . esc_html($placeholder_text) . '</span></div>';
    }
}
?>

    <!-- HERO SECTION -->
    <header class="fnd-hero">
        <div class="fnd-container fnd-hero-grid">
            <div class="fnd-hero-content">
                <span class="fnd-badge">Meet The Founder</span>
                <h1 class="fnd-hero-title">Driving the Future of Hospitality & Digital Innovation <br/> <i>Abul Kashem</i></h1>
                <p class="fnd-hero-subtitle">Founder NatunKicho</p>
                <p class="fnd-hero-lead">An international hospitality leader, corporate trainer, and digital strategist dedicated to transforming kitchen operations, empowering professionals, and scaling culinary businesses through technology.</p>
                <div class="fnd-btn-group">
                    <a href="#story" class="fnd-btn fnd-btn-primary">View My Journey</a>
                    <a href="https://www.linkedin.com/in/chef-abul-kashem/" class="fnd-btn fnd-btn-secondary">Connect Linkedin</a>
                    <a href="https://natunkicho.com/contact/" class="fnd-btn fnd-btn-outline">Contact Me</a>
                </div>
            </div>
            <div class="fnd-hero-image-wrapper">
                <?php fnd_render_image($fnd_images['founder_photo'], 'Founder Photo', 'square-ratio'); ?>
            </div>
        </div>
    </header>

    <!-- QUICK STATISTICS -->
    <section class="fnd-stats fnd-section-light">
        <div class="fnd-container fnd-stats-grid">
            <div class="fnd-stat-card">
                <span class="fnd-stat-number">15+</span>
                <span class="fnd-stat-label">Years of Experience</span>
            </div>
            <div class="fnd-stat-card">
                <span class="fnd-stat-number">7+</span>
                <span class="fnd-stat-label">Countries Worked</span>
            </div>
            <div class="fnd-stat-card">
                <span class="fnd-stat-number">12+</span>
                <span class="fnd-stat-label">Industries Served</span>
            </div>
            <div class="fnd-stat-card">
                <span class="fnd-stat-number">50+</span>
                <span class="fnd-stat-label">Corporate Clients</span>
            </div>
            <div class="fnd-stat-card">
                <span class="fnd-stat-number">200+</span>
                <span class="fnd-stat-label">Training Sessions</span>
            </div>
            <div class="fnd-stat-card">
                <span class="fnd-stat-number">10k+</span>
                <span class="fnd-stat-label">Professionals Impacted</span>
            </div>
        </div>
    </section>

    <!-- MY STORY -->
    <section id="story" class="fnd-section">
        <div class="fnd-container fnd-narrow-content">
            <div class="fnd-section-header">
                <h2 class="fnd-section-title">My Story</h2>
                <div class="fnd-title-line"></div>
            </div>
            <div class="fnd-story-text">
                <p>My Story

Every meaningful journey begins with a simple passion. Mine began with a curiosity about food—not just how it is prepared, but how it brings people together, creates opportunities, and shapes unforgettable experiences.

Growing up in Bangladesh, I discovered that hospitality is more than serving meals. It is about creating value, building relationships, and making people feel welcome. That realization inspired me to pursue a professional career in hospitality and culinary arts, leading me to study at the Sri Lanka Institute of Tourism & Hotel Management.

Studying in Sri Lanka opened my eyes to the international standards of hospitality. I received professional training in culinary arts, restaurant operations, food and beverage service, and hotel management. More importantly, I learned that excellence comes from discipline, continuous learning, and attention to detail.

After completing my education, my career took me beyond borders. Each country I worked in became another chapter in my professional growth.

In the Maldives, I experienced luxury hospitality and world-class guest service, where precision and customer satisfaction were at the heart of every operation.

In Afghanistan, I worked in a demanding environment serving thousands of meals every day. Managing large-scale food production taught me operational discipline, teamwork, efficiency, and the importance of maintaining quality under pressure.

Later, in Qatar, I worked with multinational teams in international kitchens, strengthening my leadership skills while ensuring food safety, operational excellence, and consistent service standards.

Although these international experiences expanded my knowledge, I always believed that my greatest contribution would be in my own country.

Returning to Bangladesh, I joined Bengal Meat Processing Industries Ltd., where my role gradually evolved far beyond the kitchen. I became involved in corporate sales, business development, product innovation, customer relationship management, production planning, distribution, and strategic growth. Working closely with hotels, restaurants, institutions, and corporate clients allowed me to understand the challenges faced by the hospitality and food industries from both operational and business perspectives.

Later, as a <strong> Culinary Trainer </strong> with foodpanda Bangladesh, I had the opportunity to develop standard operating procedures (SOPs), create training materials, launch new products, conduct cooking demonstrations, and train restaurant professionals across the country. Teaching others reinforced my belief that sharing knowledge creates lasting impact.

Throughout my career, I noticed a common challenge. Many hospitality professionals possessed talent and dedication, yet lacked access to structured learning, practical resources, career guidance, and industry connections. Businesses also struggled to find reliable knowledge, training, and consulting tailored to the realities of the hospitality sector.

That challenge inspired me to create  <strong> **NatunKicho**.</strong>

NatunKicho was founded with a vision to become more than a website. It is being built as a complete hospitality ecosystem—a platform where students, professionals, entrepreneurs, restaurants, hotels, manufacturers, and institutions can learn, grow, connect, and succeed together.

Today, <strong>NatunKicho </strong>continues to evolve into a comprehensive platform offering hospitality education, career development, industry insights, professional consulting, food safety awareness, digital learning, and technology-driven solutions. My long-term vision is to integrate artificial intelligence, practical training, and industry collaboration to help shape the future of hospitality in Bangladesh and beyond.

My journey has never been defined by job titles or positions. It has been defined by continuous learning, international experience, meaningful relationships, and a commitment to helping others grow.

As I continue this journey, my mission remains unchanged: to share knowledge, inspire professionals, support businesses, and build a stronger, smarter, and more innovative hospitality industry for future generations.

**"Success in hospitality is not measured only by the meals we serve, but by the lives we touch, the knowledge we share, and the opportunities we create.
</p>
            </div>
        </div>
    </section>

    <!-- GLOBAL EXPERIENCE -->
    <section class="fnd-section fnd-section-light">
        <div class="fnd-container">
            <div class="fnd-section-header">
                <h2 class="fnd-section-title">Global Experience</h2>
                <div class="fnd-title-line"></div>
            </div>
            <div class="fnd-grid-3">
                <div class="fnd-card">
                    <div class="fnd-card-header">
                        <?php fnd_render_image($fnd_images['country_uk'], 'Country Image', 'icon-ratio'); ?>
                        <h3>Qatar</h3>
                    </div>
                    <p><strong>Experience Summary:</strong> Spearheaded operational overhauls for premium dining establishments and corporate catering systems.</p>
                    <p class="fnd-skills-tag"><strong>Skills:</strong> Operational Efficiency, Agility, Compliance</p>
                </div>
                <div class="fnd-card">
                    <div class="fnd-card-header">
                        <?php fnd_render_image($fnd_images['country_uae'], 'Country Image', 'icon-ratio'); ?>
                        <h3>United Arab Emirates</h3>
                    </div>
                    <p><strong>Experience Summary:</strong> Managed multi-unit kitchen setups and scaled high-volume cloud kitchen infrastructures in fast-paced markets.</p>
                    <p class="fnd-skills-tag"><strong>Skills:</strong> Scalability, Multi-unit Management, Logistics</p>
                </div>
                <div class="fnd-card">
                    <div class="fnd-card-header">
                        <?php fnd_render_image($fnd_images['country_ksa'], 'Country Image', 'icon-ratio'); ?>
                        <h3>Saudi Arabia</h3>
                    </div>
                    <p><strong>Experience Summary:</strong> Formulated food safety strategies and corporate dining architectures for leading enterprise clients.</p>
                    <p class="fnd-skills-tag"><strong>Skills:</strong> Corporate B2B, Strategic Alignment, Food Safety</p>
                </div>
            </div>
            
            <br/>
            <br/>
            <div class="fnd-grid-3">
                <div class="fnd-card">
                    <div class="fnd-card-header">
                        <?php fnd_render_image($fnd_images['country_uk'], 'Country Image', 'icon-ratio'); ?>
                        <h3>Maldives</h3>
                    </div>
                    <p><strong>Experience Summary:</strong> Spearheaded operational overhauls for premium dining establishments and corporate catering systems.</p>
                    <p class="fnd-skills-tag"><strong>Skills:</strong> Operational Efficiency, Agility, Compliance</p>
                </div>
                <div class="fnd-card">
                    <div class="fnd-card-header">
                        <?php fnd_render_image($fnd_images['country_uae'], 'Country Image', 'icon-ratio'); ?>
                        <h3>Sri Lanka</h3>
                    </div>
                    <p><strong>Experience Summary:</strong> Managed multi-unit kitchen setups and scaled high-volume cloud kitchen infrastructures in fast-paced markets.</p>
                    <p class="fnd-skills-tag"><strong>Skills:</strong> Scalability, Multi-unit Management, Logistics</p>
                </div>
                <div class="fnd-card">
                    <div class="fnd-card-header">
                        <?php fnd_render_image($fnd_images['country_ksa'], 'Country Image', 'icon-ratio'); ?>
                        <h3>Afghanistan</h3>
                    </div>
                    <p><strong>Experience Summary:</strong> Formulated food safety strategies and corporate dining architectures for leading enterprise clients.</p>
                    <p class="fnd-skills-tag"><strong>Skills:</strong> Corporate B2B, Strategic Alignment, Food Safety</p>
                </div>
            </div>
            
        </div>
    </section>
     
     

    <!-- INDUSTRIES WORKED -->
    <section class="fnd-section">
        <div class="fnd-container">
            <div class="fnd-section-header">
                <h2 class="fnd-section-title">Industries Worked</h2>
                <div class="fnd-title-line"></div>
            </div>
            <div class="fnd-grid-4">
                <div class="fnd-industry-item">Hotels</div>
                <div class="fnd-industry-item">Restaurants</div>
                <div class="fnd-industry-item">Food Manufacturing</div>
                <div class="fnd-industry-item">Meat Processing</div>
                <div class="fnd-industry-item">Cloud Kitchens</div>
                <div class="fnd-industry-item">Corporate Dining</div>
                <div class="fnd-industry-item">Hospitality Education</div>
                <div class="fnd-industry-item">Distribution</div>
                <div class="fnd-industry-item">Consulting</div>
                <div class="fnd-industry-item">Supply Chain</div>
                <div class="fnd-industry-item">Training</div>
                <div class="fnd-industry-item">Digital Learning</div>
            </div>
        </div>
    </section>

    <!-- AREAS OF EXPERTISE -->
    <section class="fnd-section fnd-section-light">
        <div class="fnd-container">
            <div class="fnd-section-header">
                <h2 class="fnd-section-title">Areas of Expertise</h2>
                <div class="fnd-title-line"></div>
            </div>
            <div class="fnd-grid-3">
                <div class="fnd-card">
                    <span class="fnd-expert-icon">💼</span>
                    <h3>Corporate Sales</h3>
                    <p>B2B strategy orchestration, corporate contract acquisitions, and sustainable revenue development pipelines.</p>
                </div>
                <div class="fnd-card">
                    <span class="fnd-expert-icon">🏭</span>
                    <h3>Food Manufacturing</h3>
                    <p>Optimizing plant layouts, mass-scale processing automation, and scaling raw-to-retail conversion lines.</p>
                </div>
                <div class="fnd-card">
                    <span class="fnd-expert-icon">🛡️</span>
                    <h3>Food Safety & HACCP</h3>
                    <p>Designing global-standard preventative controls, managing rigorous system audits, and critical risk points.</p>
                </div>
                <div class="fnd-card">
                    <span class="fnd-expert-icon">🍳</span>
                    <h3>Kitchen Operations</h3>
                    <p>Maximizing line throughput, structural layout optimization, waste minimization metrics, and labor budgeting.</p>
                </div>
                <div class="fnd-card">
                    <span class="fnd-expert-icon">🤖</span>
                    <h3>AI & Digital Transformation</h3>
                    <p>Integrating modern web tools, system automations, AI writing paradigms, and data-driven learning platforms.</p>
                </div>
                <div class="fnd-card">
                    <span class="fnd-expert-icon">📈</span>
                    <h3>SEO & Growth Marketing</h3>
                    <p>Content architecture planning, search visibility funnels, and performance optimization for web assets.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- PROFESSIONAL JOURNEY -->
    <section class="fnd-section">
        <div class="fnd-container fnd-narrow-content">
            <div class="fnd-section-header">
                <h2 class="fnd-section-title">Professional Journey</h2>
                <div class="fnd-title-line"></div>
            </div>
            <div class="fnd-journey-flow">
                <div class="fnd-journey-step">
                    <div class="fnd-step-number">1</div>
                    <div class="fnd-step-content">
                        <h3>Learning & Foundations</h3>
                        <p>Absorbing core operational rules, master level culinary methodologies, and fundamental business requirements in dynamic kitchen environments.</p>
                    </div>
                </div>
                <div class="fnd-journey-step">
                    <div class="fnd-step-number">2</div>
                    <div class="fnd-step-content">
                        <h3>International Experience</h3>
                        <p>Navigating cross-border operational policies, learning global supply chains, and adaptation across diverse global markets.</p>
                    </div>
                </div>
                <div class="fnd-journey-step">
                    <div class="fnd-step-number">3</div>
                    <div class="fnd-step-content">
                        <h3>Corporate Leadership</h3>
                        <p>Directing enterprise accounts, building regional multi-unit frameworks, and optimizing bottom-line revenue metrics.</p>
                    </div>
                </div>
                <div class="fnd-journey-step">
                    <div class="fnd-step-number">4</div>
                    <div class="fnd-step-content">
                        <h3>Training & Development</h3>
                        <p>Authoring instructional blueprints, standard operating procedures, and creating vocational paths for emerging hospitality talents.</p>
                    </div>
                </div>
                <div class="fnd-journey-step">
                    <div class="fnd-step-number">5</div>
                    <div class="fnd-step-content">
                        <h3>Founder of NatunKicho</h3>
                        <p>Consolidating a lifetime of business and culinary intelligence into a transformative digital ecosystem for training and consulting.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURED PROJECTS -->
    <section class="fnd-section fnd-section-light">
        <div class="fnd-container">
            <div class="fnd-section-header">
                <h2 class="fnd-section-title">Featured Projects</h2>
                <div class="fnd-title-line"></div>
            </div>
            <div class="fnd-grid-3">
                <div class="fnd-project-card">
                    <?php fnd_render_image($fnd_images['project_hub'], 'NatunKicho Platform', 'landscape-ratio'); ?>
                    <div class="fnd-project-info">
                        <h3>NatunKicho Digital Hub</h3>
                        <p>A central digital knowledge infrastructure addressing contemporary training and operational growth limits in hospitality.</p>
                    </div>
                </div>
                <div class="fnd-project-card">
                    <?php fnd_render_image($fnd_images['project_consult'], 'Restaurant Consulting', 'landscape-ratio'); ?>
                    <div class="fnd-project-info">
                        <h3>Enterprise Turnaround Blueprint</h3>
                        <p>Re-engineered a struggling multi-unit brand layout, yielding 25% efficiency gains via workflow standardizations.</p>
                    </div>
                </div>
                <div class="fnd-project-card">
                    <?php fnd_render_image($fnd_images['project_academy'], 'Training Image', 'landscape-ratio'); ?>
                    <div class="fnd-project-info">
                        <h3>Food Handler Academy</h3>
                        <p>Designed a micro-learning pipeline optimized for quick compliance onboarding across food production lines.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SERVICES -->
    <section class="fnd-section">
        <div class="fnd-container">
            <div class="fnd-section-header">
                <h2 class="fnd-section-title">Consulting Services</h2>
                <div class="fnd-title-line"></div>
            </div>
            <div class="fnd-grid-3">
                <div class="fnd-service-card">
                    <h3>Restaurant Consulting</h3>
                    <p>Workflow redesign, menu architecture optimization, and structural profitability auditing.</p>
                </div>
                <div class="fnd-service-card">
                    <h3>Corporate Training</h3>
                    <p>Tailored leadership workshops, scale compliance readiness frameworks, and technical operational drills.</p>
                </div>
                <div class="fnd-service-card">
                    <h3>Food Safety Systems</h3>
                    <p>HACCP development plan implementation, internal systems audits, and baseline risk mitigation setups.</p>
                </div>
                <div class="fnd-service-card">
                    <h3>Digital Transformation</h3>
                    <p>Integrating AI solutions, custom automations, and optimized management infrastructure.</p>
                </div>
                <div class="fnd-service-card">
                    <h3>SOP Development</h3>
                    <p>Authoring operational manuals ensuring consistency across multi-location facilities.</p>
                </div>
                <div class="fnd-service-card">
                    <h3>Career Coaching</h3>
                    <p>Strategic growth mentorship and tactical training programs for future industry executives.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FOUNDER VISION -->
    <section class="fnd-section fnd-section-light">
        <div class="fnd-container fnd-narrow-content">
            <div class="fnd-section-header">
                <h2 class="fnd-section-title">Founder Vision</h2>
                <div class="fnd-title-line"></div>
            </div>
            <blockquote class="fnd-vision-quote">
                <p>"NatunKicho was built to solve a critical industry gap: structural modern access to institutional knowledge. Our long-term mission is to empower hospitality professionals globally by democratizing tactical, high-tier operational guidance."</p>
                <p>By blending verified physical world systems with automation, custom WordPress frameworks, and AI-assisted workflows, we are engineering a scalable environment where legacy business practices cleanly meet modern digital ecosystems.</p>
            </blockquote>
        </div>
    </section>

    <!-- LEADERSHIP PHILOSOPHY -->
    <section class="fnd-section">
        <div class="fnd-container fnd-narrow-content">
            <div class="fnd-section-header">
                <h2 class="fnd-section-title">Leadership Philosophy</h2>
                <div class="fnd-title-line"></div>
            </div>
            <div class="fnd-philosophy-box">
                <p>True professional excellence rests upon structured ownership and perpetual iteration. My philosophy relies heavily on a <strong>customer-first, quality-vetted structure</strong> combined with continuous lifelong upskilling. True innovation never means chasing trend cycles—it means systematically modernizing backend frameworks to resolve actual physical world inefficiencies.</p>
            </div>
        </div>
    </section>

    <!-- CERTIFICATIONS -->
    <section class="fnd-section fnd-section-light">
        <div class="fnd-container">
            <div class="fnd-section-header">
                <h2 class="fnd-section-title">Certifications</h2>
                <div class="fnd-title-line"></div>
            </div>
            <div class="fnd-grid-4">
                <div class="fnd-card text-center">
                    <?php fnd_render_image($fnd_images['cert_haccp'], 'Certificate Image', 'cert-ratio'); ?>
                    <h4>Advanced HACCP Management</h4>
                    <p class="fnd-meta">Global Food Safety Body | 2021</p>
                </div>
                <div class="fnd-card text-center">
                    <?php fnd_render_image($fnd_images['cert_leadership'], 'Certificate Image', 'cert-ratio'); ?>
                    <h4>Hospitality Leadership Certificate</h4>
                    <p class="fnd-meta">Executive Institute | 2019</p>
                </div>
                <div class="fnd-card text-center">
                    <?php fnd_render_image($fnd_images['cert_digital'], 'Certificate Image', 'cert-ratio'); ?>
                    <h4>Advanced Digital Strategy</h4>
                    <p class="fnd-meta">Tech Academy | 2023</p>
                </div>
                <div class="fnd-card text-center">
                    <?php fnd_render_image($fnd_images['cert_ai'], 'Certificate Image', 'cert-ratio'); ?>
                    <h4>AI Integration Specialist</h4>
                    <p class="fnd-meta">Automation Forum | 2025</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTINUOUS PROFESSIONAL DEVELOPMENT -->
    <section class="fnd-section">
        <div class="fnd-container">
            <div class="fnd-section-header">
                <h2 class="fnd-section-title">Continuous Development Focus</h2>
                <div class="fnd-title-line"></div>
            </div>
            <div class="fnd-tags-container">
                <span class="fnd-tag">Hospitality Management</span>
                <span class="fnd-tag">Food Safety & Compliance</span>
                <span class="fnd-tag">Executive Leadership</span>
                <span class="fnd-tag">Business Development</span>
                <span class="fnd-tag">Artificial Intelligence</span>
                <span class="fnd-tag">SEO Architecture</span>
                <span class="fnd-tag">Cybersecurity Standards</span>
                <span class="fnd-tag">Advanced Data Analysis</span>
                <span class="fnd-tag">Process Automation</span>
                <span class="fnd-tag">WordPress Core Ecosystems</span>
            </div>
        </div>
    </section>

    <!-- AWARDS & RECOGNITION -->
    <section class="fnd-section fnd-section-light">
        <div class="fnd-container">
            <div class="fnd-section-header">
                <h2 class="fnd-section-title">Awards & Recognition</h2>
                <div class="fnd-title-line"></div>
            </div>
            <div class="fnd-grid-3">
                <div class="fnd-card">
                    <?php fnd_render_image($fnd_images['award_innovator'], 'Award Image', 'award-ratio'); ?>
                    <h3>Hospitality Innovator Award</h3>
                    <p>Recognized for driving systemic operational evolution through regional training deployments.</p>
                    <span class="fnd-meta">2022</span>
                </div>
                <div class="fnd-card">
                    <?php fnd_render_image($fnd_images['award_training'], 'Award Image', 'award-ratio'); ?>
                    <h3>Excellence in Corporate Training</h3>
                    <p>Awarded for optimizing onboarding processes across international enterprise accounts.</p>
                    <span class="fnd-meta">2024</span>
                </div>
                <div class="fnd-card">
                    <?php fnd_render_image($fnd_images['award_pioneer'], 'Award Image', 'award-ratio'); ?>
                    <h3>Digital Pioneer Shortlist</h3>
                    <p>Acknowledged for blending cloud tech solutions with foundational kitchen operations.</p>
                    <span class="fnd-meta">2025</span>
                </div>
            </div>
        </div>
    </section>

    <!-- TESTIMONIALS -->
    <section class="fnd-section">
        <div class="fnd-container">
            <div class="fnd-section-header">
                <h2 class="fnd-section-title">Endorsements</h2>
                <div class="fnd-title-line"></div>
            </div>
            <div class="fnd-grid-2">
                <div class="fnd-testimonial-card">
                    <p class="fnd-quote">"The operational frameworks put in place transformed our back-of-house structure. Training times dropped while compliance metrics surged dramatically."</p>
                    <div class="fnd-testimonial-user">
                        <?php fnd_render_image($fnd_images['avatar_rahat'], 'Photo', 'avatar-ratio'); ?>
                        <div>
                            <h4>Rahat Chowdhury</h4>
                            <p>Operations Director | Apex Culinary Group</p>
                        </div>
                    </div>
                </div>
                <div class="fnd-testimonial-card">
                    <p class="fnd-quote">"An exceptional strategist who understands both corporate sales physics and the technical reality of software and SEO systems."</p>
                    <div class="fnd-testimonial-user">
                        <?php fnd_render_image($fnd_images['avatar_sarah'], 'Photo', 'avatar-ratio'); ?>
                        <div>
                            <h4>Sarah Jenkins</h4>
                            <p>Managing Director | Horizon Hospitality Consulting</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FINAL CALL TO ACTION -->
    <section class="fnd-cta">
        <div class="fnd-container text-center">
            <h2>Let's Build the Future of Hospitality Together</h2>
            <p>Whether you require targeted restaurant consulting, large-scale corporate training structures, or ecosystem advice—let's orchestrate modern growth vectors.</p>
            <div class="fnd-btn-group fnd-center-group">
                <a href="https://natunkicho.com/contact/" class="fnd-btn fnd-btn-white">Contact Me</a>
                <a href="https://natunkicho.com" class="fnd-btn fnd-btn-outline-white">Visit NatunKicho</a>
                <a href="https://www.linkedin.com/in/chef-abul-kashem/" class="fnd-btn fnd-btn-outline-white-subtle">Find on Linkedin</a>
            </div>
        </div>
    </section>
<?php get_footer(); ?>