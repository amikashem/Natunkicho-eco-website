nk-ai-rankmath/
├── nk-ai-rankmath.php
├── uninstall.php
├── readme.txt
├── composer.json (optional)
│
├── app/
│   ├── Core/
│   │   ├── Plugin.php
│   │   ├── Activator.php
│   │   ├── Deactivator.php
│   │   └── Loader.php
│   │
│   ├── Admin/
│   │   ├── Admin.php
│   │   ├── Settings.php
│   │   └── Assets.php
│   │
│   ├── AI/
│   │   ├── Gateway.php
│   │   ├── Handlers/
│   │   │   ├── SEO_Title_Handler.php
│   │   │   ├── Meta_Description_Handler.php
│   │   │   ├── Focus_Keyword_Handler.php
│   │   │   ├── Keyword_Suggestions_Handler.php
│   │   │   ├── Schema_Handler.php
│   │   │   ├── FAQ_Handler.php
│   │   │   ├── Internal_Links_Handler.php
│   │   │   ├── Image_ALT_Handler.php
│   │   │   ├── Readability_Handler.php
│   │   │   ├── Content_Optimization_Handler.php
│   │   │   ├── SEO_Score_Handler.php
│   │   │   └── Bulk_Optimization_Handler.php
│   │   └── Prompts/
│   │       └── Prompt_Templates.php
│   │
│   ├── RankMath/
│   │   ├── Integration.php
│   │   ├── Field_Detector.php
│   │   └── Renderer.php
│   │
│   ├── API/
│   │   ├── REST.php
│   │   ├── Controller/
│   │   │   └── AI_Controller.php
│   │   └── Middleware/
│   │       └── Auth.php
│   │
│   ├── Assets/
│   │   └── Manager.php
│   │
│   ├── Helpers/
│   │   ├── Helpers.php
│   │   ├── Cache.php
│   │   ├── Logger.php
│   │   └── Validator.php
│   │
│   └── Integrations/
│       ├── WooCommerce.php
│       ├── RankMath_Pro.php
│       └── ThirdParty.php
│
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── admin.min.css
│   ├── js/
│   │   ├── admin.js
│   │   ├── field-buttons.js
│   │   ├── sidebar.js
│   │   └── admin.min.js
│   └── images/
│       ├── logo.svg
│       └── icon.svg
│
├── languages/
│   └── nk-ai-rankmath.pot
│
└── templates/
    ├── admin/
    │   ├── settings.php
    │   └── dashboard.php
    ├── ai/
    │   ├── preview.php
    │   └── bulk.php
    └── sidebar/
        └── tools.php