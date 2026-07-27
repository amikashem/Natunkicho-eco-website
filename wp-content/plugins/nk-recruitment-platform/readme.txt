nk-recruitment-platform
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
│   │   │   └── MigrationController.php
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