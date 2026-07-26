└── nk-recruitment-platform
    ├── app
    │   ├── AI
    │   │   ├── Controllers
    │   │   │   └── AIController.php
    │   │   ├── Models
    │   │   ├── Prompts
    │   │   │   └── PromptLibrary.php
    │   │   ├── Providers
    │   │   │   ├── AIProviderInterface.php
    │   │   │   ├── GeminiProvider.php
    │   │   │   ├── GitHubProvider.php
    │   │   │   ├── GrokProvider.php
    │   │   │   └── OpenAIProvider.php
    │   │   ├── Services
    │   │   │   └── AIGatewayService.php
    │   │   ├── Views
    │   │   │   └── dashboard.php
    │   │   └── AIServiceProvider.php
    │   ├── API
    │   │   ├── Auth
    │   │   ├── REST
    │   │   ├── Webhooks
    │   │   └── APIServiceProvider.php
    │   ├── ATS
    │   │   ├── Controllers
    │   │   │   └── ApplicationController.php
    │   │   ├── Models
    │   │   │   └── Application.php
    │   │   ├── Repositories
    │   │   │   └── ApplicationRepository.php
    │   │   ├── Services
    │   │   │   └── ApplicationService.php
    │   │   ├── Shortcodes
    │   │   │   ├── ApplyJobShortcode.php
    │   │   │   └── EmployerATSShortcode.php
    │   │   ├── Views
    │   │   │   ├── application-create.php
    │   │   │   ├── application-edit.php
    │   │   │   ├── application-list.php
    │   │   │   ├── employer-ats-dashboard.php
    │   │   │   └── frontend-apply-job.php
    │   │   ├── ATSServiceProvider.php
    │   │   └── ApplicationServiceProvider.php
    │   ├── Admin
    │   │   ├── Assets
    │   │   ├── Controllers
                └── MigrationController.php
    │   │   ├── Models
    │   │   ├── Repositories
    │   │   ├── Routes
    │   │   ├── Services
    │   │   ├── Views
    │   │   │   └── dashboard.php
    │   │   ├── AdminServiceProvider.php
    │   │   └── MenuManager.php
    │   ├── Analytics
    │   │   ├── Charts
    │   │   ├── Controllers
    │   │   │   ├── AnalyticsDashboardController.php
    │   │   │   └── TrackingController.php
    │   │   ├── Reports
    │   │   ├── Services
    │   │   │   ├── AnalyticsDashboardService.php
    │   │   │   └── TrackingService.php
    │   │   ├── Views
    │   │   │   └── dashboard.php
    │   │   └── AnalyticsServiceProvider.php
    │   ├── Auth
    │   │   ├── Controllers
    │   │   │   └── AuthController.php
    │   │   └── Views
    │   │       ├── login-form.php
    │   │       └── register-form.php
    │   ├── Candidate
    │   │   ├── Assets
    │   │   ├── Controllers
    │   │   │   └── CandidateController.php
    │   │   ├── Models
    │   │   │   └── Candidate.php
    │   │   ├── Repositories
    │   │   │   └── CandidateRepository.php
    │   │   ├── Routes
    │   │   ├── Services
    │   │   │   └── CandidateService.php
    │   │   ├── Shortcodes
    │   │   │   └── CandidateDashboardShortcode.php
    │   │   ├── Views
    │   │   │   ├── candidate-create.php
    │   │   │   ├── candidate-edit.php
    │   │   │   ├── candidate-list.php
    │   │   │   ├── frontend-applied-jobs.php
    │   │   │   ├── frontend-dashboard.php
    │   │   │   ├── frontend-messages.php
    │   │   │   ├── frontend-profile-edit.php
    │   │   │   ├── frontend-profile-preview.php
    │   │   │   ├── frontend-saved-jobs.php
    │   │   │   └── frontend-settings.php
    │   │   └── CandidateServiceProvider.php
    │   ├── Core
    │   │   ├── Activator.php
    │   │   ├── Application.php
    │   │   ├── Autoloader.php
    │   │   ├── Container.php
    │   │   ├── Deactivator.php
    │   │   ├── Loader.php
    │   │   ├── Module.php
    │   │   ├── Router.php
    │   │   └── ServiceProvider.php
    │   │   └── RankMathBridge.php
    │   ├── Database
    │   │   ├── Migrations
    │   │   │   ├── CreateAILogsTable.php
    │   │   │   ├── CreateAnalyticsTable.php
    │   │   │   ├── CreateApplicationsTable.php
    │   │   │   ├── CreateCandidatesTable.php
    │   │   │   ├── CreateCompaniesTable.php
    │   │   │   ├── CreateEmailQueueTable.php
    │   │   │   ├── CreateJobsTable.php
    │   │   │   ├── CreateNotificationsTable.php
    │   │   │   ├── CreateResumesTable.php
    │   │   │   └── CreateSubscriptionsTable.php
    │   │   ├── DatabaseManager.php
    │   │   ├── Installer.php
    │   │   ├── Migration.php
    │   │   ├── MigrationManager.php
    │   │   ├── QueryBuilder.php
    │   │   ├── Repository.php
    │   │   ├── Schema.php
    │   │   └── VersionManager.php
    │   ├── Employer
    │   │   ├── Assets
    │   │   │   ├── css
    │   │   │   │   └── employer.css
    │   │   │   └── js
    │   │   │       └── employer.js
    │   │   ├── Controllers
    │   │   │   └── EmployerController.php
    │   │   ├── Helpers
    │   │   │   ├── CompanyFields.php
    │   │   │   └── UrlHelper.php
    │   │   ├── Models
    │   │   │   ├── Company.php
    │   │   │   └── Job.php
    │   │   ├── Repositories
    │   │   │   └── CompanyRepository.php
    │   │   ├── Routes
    │   │   ├── Services
    │   │   │   └── CompanyService.php
    │   │   ├── Shortcodes
    │   │   │   ├── CompanyProfileShortcode.php
    │   │   │   ├── CreateCompanyShortcode.php
    │   │   │   └── EmployerDashboardShortcode.php
    │   │   ├── Uploads
    │   │   ├── Validators
    │   │   ├── Views
    │   │   │   ├── company-create.php
    │   │   │   ├── company-edit.php
    │   │   │   ├── company-form.php
    │   │   │   ├── company-list.php
    │   │   │   ├── frontend-ats.php
    │   │   │   ├── frontend-company-create.php
    │   │   │   ├── frontend-company-edit.php
    │   │   │   ├── frontend-company-preview.php
    │   │   │   ├── frontend-dashboard.php
    │   │   │   ├── frontend-manage-jobs.php
    │   │   │   ├── frontend-messages.php
    │   │   │   └── frontend-settings.php
                └── frontend-dashboard.php
    │   │   └── EmployerServiceProvider.php
    │   ├── Helpers
    │   ├── Jobs
    │   │   ├── Admin
    │   │   │   └── JobAdmin.php
    │   │   ├── Assets
    │   │   │   └── css
    │   │   │       └── job-editor.css
    │   │   ├── Controllers
    │   │   │   └── JobController.php
    │   │   ├── MetaBoxes
    │   │   │   └── JobMetaBox.php
    │   │   ├── Models
    │   │   │   └── Job.php
    │   │   ├── Repositories
    │   │   │   └── JobRepository.php
    │   │   ├── Services
    │   │   │   └── JobService.php
    │   │   ├── Shortcodes
    │   │   │   ├── JobDetailsShortcode.php
    │   │   │   └── PostJobShortcode.php
    │   │   ├── Views
    │   │   │   ├── frontend-post-job.php
    │   │   │   ├── job-create.php
    │   │   │   ├── job-edit.php
    │   │   │   ├── job-information.php
    │   │   │   ├── job-list.php
    │   │   │   └── job-settings.php
    │   │   └── JobServiceProvider.php
    │   ├── Media
    │   │   ├── Assets
    │   │   │   └── js
    │   │   │       └── media-uploader.js
    │   │   ├── Controllers
    │   │   ├── Services
    │   │   ├── Uploaders
    │   │   └── MediaServiceProvider.php
    │   ├── Membership
    │   │   ├── Admin
    │   │   │   └── MembershipAdmin.php
    │   │   ├── Controllers
    │   │   ├── Permissions
    │   │   ├── Plans
    │   │   │   └── PlanManager.php
    │   │   ├── Services
    │   │   │   ├── PermissionService.php
    │   │   │   └── WooCommerceIntegration.php
                └── MembershipCronService.php
    │   │   ├── Shortcodes
    │   │   │   └── PricingShortcode.php
    │   │   ├── Views
    │   │   │   └── frontend-pricing.php
    │   │   └── MembershipServiceProvider.php
    │   ├── Notifications
    │   │   ├── Email
    │   │   │   └── EmailService.php
    │   │   ├── Push
    │   │   ├── Queue
    │   │   │   └── EmailQueueService.php
    │   │   ├── SMS
    │   │   ├── Templates
    │   │   │   └── default-email.php
    │   │   ├── NotificationService.php
    │   │   └── NotificationServiceProvider.php
    │   ├── Resume
    │   │   ├── Controllers
    │   │   │   └── ResumeController.php
    │   │   ├── Models
    │   │   │   └── Resume.php
    │   │   ├── PDF
    │   │   ├── Repositories
    │   │   │   └── ResumeRepository.php
    │   │   ├── Services
    │   │   │   └── ResumeService.php
    │   │   ├── Shortcodes
    │   │   │   ├── CreateResumeShortcode.php
    │   │   │   ├── EditResumeShortcode.php
    │   │   │   └── PublicProfileShortcode.php
    │   │   ├── Templates
    │   │   │   └── resume
    │   │   │       ├── default.php
    │   │   │       ├── executive.php
    │   │   │       ├── modern.php
    │   │   │       └── professional.php
    │   │   ├── Views
    │   │   │   ├── frontend-public-profile.php
    │   │   │   ├── frontend-resume-builder.php
    │   │   │   ├── frontend-resume-edit.php
    │   │   │   ├── resume-create.php
    │   │   │   ├── resume-edit.php
    │   │   │   ├── resume-list.php
    │   │   │   └── resume-settings.php
    │   │   └── ResumeServiceProvider.php
    │   └── Search
    │       ├── Controllers
    │       │   └── SearchController.php
    │       ├── Filters
    │       ├── Helpers
    │       │   └── SearchHelper.php
    │       ├── Indexers
    │       ├── Models
    │       │   └── SearchFilter.php
    │       ├── Repositories
    │       │   └── SearchRepository.php
    │       ├── Services
    │       │   └── SearchService.php
    │       ├── Shortcodes
    │       │   └── SearchShortcode.php
    │       ├── Views
    │       │   ├── search-form.php
    │       │   ├── search-results.php
    │       │   └── search-settings.php
    │       └── SearchServiceProvider.php
    ├── assets
    │   ├── css
    │   ├── fonts
    │   ├── images
    │   ├── js
    │   └── vendor
    ├── bootstrap
    │   ├── app.php
    │   ├── bootstrap.php
    │   ├── helpers.php
    │   ├── install.php
    │   └── uninstall.php
    ├── config
    │   ├── ai.php
    │   ├── app.php
    │   ├── constants.php
    │   ├── database.php
    │   ├── email.php
    │   ├── membership.php
    │   ├── modules.php
    │   └── permissions.php
    ├── languages
    ├── resources
    │   ├── emails
    │   ├── icons
    │   ├── pdf
    │   └── translations
    ├── templates
    │   ├── candidate
    │   ├── dashboard
    │   ├── emails
    │   ├── employer
    │   ├── public
    │   │   ├── company-profile.php
    │   │   └── job-details.php
    │   │   ├── single-job-page.php
    │   │   └── single-job-virtual.php
    │   └── resume
    ├── CHANGELOG.md
    ├── LICENSE.md
    ├── MySQL to create the resumes table.txt
    ├── nk-recruitment-platform.php
    ├── ongoing.md
    ├── readme.txt
    └── uninstall.php 