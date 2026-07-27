<?php
namespace NK_AI_RankMath\AI;

class Prompt_Templates {
    private static $templates = [
        'seo_title' => [
            'description' => 'Generate optimized SEO title',
            'template' => "Generate an SEO-optimized title that is compelling and includes the main keyword.\n\nContent: {content}\nTarget Keyword: {keyword}\nCurrent Title: {current_title}\nDesired Length: {length} characters\nTone: {tone}\n\nRules:\n- Include primary keyword near the beginning\n- Keep between {min_length}-{max_length} characters\n- Make it click-worthy and compelling",
            'defaults' => [
                'length' => 60,
                'min_length' => 40,
                'max_length' => 60,
                'tone' => 'professional'
            ]
        ],
        
        'meta_description' => [
            'description' => 'Generate optimized meta description',
            'template' => "Create a compelling meta description that summarizes the content and encourages clicks.\n\nContent: {content}\nTarget Keyword: {keyword}\nDesired Length: {length} characters\n\nRules:\n- Include primary keyword naturally\n- Keep between {min_length}-{max_length} characters\n- Include a call-to-action\n- Match the content tone",
            'defaults' => [
                'length' => 160,
                'min_length' => 130,
                'max_length' => 160
            ]
        ],
        
        'focus_keyword' => [
            'description' => 'Suggest primary focus keyword',
            'template' => "Analyze this content and suggest the best primary focus keyword.\n\nContent: {content}\nTitle: {title}\nHeading: {heading}\n\nRules:\n- Choose the most relevant keyword\n- Consider search volume potential\n- Ensure relevance to content\n- Include variations if needed",
            'defaults' => []
        ],
        
        'keyword_suggestions' => [
            'description' => 'Generate keyword suggestions',
            'template' => "Generate comprehensive keyword suggestions for this content.\n\nContent: {content}\nPrimary Keyword: {keyword}\n\nReturn in format:\nPrimary: [primary keyword]\nSecondary: [secondary keywords]\nLong-tail: [long-tail keywords]\nLSI: [LSI keywords]\n\nRules:\n- Include relevant variations\n- Consider search intent\n- Include semantic keywords",
            'defaults' => []
        ],
        
        'schema_generation' => [
            'description' => 'Generate Schema markup',
            'schema_type' => 'Article',
            'template' => "Generate {schema_type} Schema markup for this content.\n\nContent: {content}\nTitle: {title}\nAuthor: {author}\nDate: {date}\n\nRules:\n- Use valid JSON-LD format\n- Include all required properties\n- Follow Schema.org standards\n- Include appropriate modifiers",
            'defaults' => [
                'schema_type' => 'Article'
            ]
        ],
        
        'faq_generation' => [
            'description' => 'Generate FAQ section',
            'template' => "Generate an FAQ section based on this content.\n\nContent: {content}\nTopic: {topic}\nNumber of Questions: {question_count}\n\nRules:\n- Each Q&A should be relevant\n- Cover common questions\n- Provide comprehensive answers\n- Use natural language",
            'defaults' => [
                'question_count' => 5
            ]
        ],
        
        'internal_links' => [
            'description' => 'Suggest internal links',
            'template' => "Suggest relevant internal linking opportunities for this content.\n\nContent: {content}\nCurrent Links: {current_links}\nAvailable Posts: {available_posts}\n\nRules:\n- Match content relevance\n- Suggest anchor text\n- Explain why link is relevant",
            'defaults' => []
        ],
        
        'image_alt' => [
            'description' => 'Generate image ALT text',
            'template' => "Generate descriptive ALT text for this image.\n\nImage Context: {context}\nImage Description: {description}\nContent Topic: {topic}\n\nRules:\n- Be descriptive and accurate\n- Include relevant keywords naturally\n- Keep under 125 characters\n- Consider accessibility",
            'defaults' => []
        ],
        
        'readability_improvement' => [
            'description' => 'Improve content readability',
            'template' => "Improve the readability of this content while maintaining meaning.\n\nContent: {content}\nCurrent Grade Level: {grade_level}\nTarget Grade Level: {target_grade}\n\nRules:\n- Use shorter sentences\n- Simplify complex words\n- Maintain key information\n- Improve flow and clarity",
            'defaults' => [
                'target_grade' => 8
            ]
        ],
        
        'content_optimization' => [
            'description' => 'Optimize content for SEO',
            'template' => "Optimize this content for search engines while maintaining quality.\n\nContent: {content}\nTarget Keywords: {keywords}\nCurrent Score: {score}\n\nRules:\n- Include keywords naturally\n- Improve structure\n- Enhance value\n- Fix SEO issues",
            'defaults' => []
        ],
        
        'seo_score_improvement' => [
            'description' => 'Generate SEO score improvement tips',
            'template' => "Generate actionable tips to improve the SEO score of this content.\n\nContent: {content}\nCurrent Score: {score}\nIssues Identified: {issues}\n\nRules:\n- Prioritize high-impact fixes\n- Provide specific actions\n- Explain the reasoning\n- Include success metrics",
            'defaults' => []
        ],
        
        'bulk_optimization' => [
            'description' => 'Optimize multiple pieces of content',
            'template' => "Analyze and optimize these content pieces for better SEO.\n\nContent Items: {content_items}\nTotal Count: {count}\nGoal: {goal}\n\nRules:\n- Process each item individually\n- Maintain consistency\n- Consider relationships\n- Prioritize improvements",
            'defaults' => [
                'goal' => 'improve_seo'
            ]
        ],
        
        // Additional context-aware templates
        'hospitality_content' => [
            'description' => 'Optimize hospitality-specific content',
            'template' => "Optimize this hospitality content for both users and search engines.\n\nContent: {content}\nProperty Type: {property_type}\nLocation: {location}\nServices: {services}\n\nRules:\n- Focus on guest experience\n- Include local SEO elements\n- Highlight unique amenities\n- Use hospitality terminology",
            'defaults' => []
        ],
        
        'woocommerce_product' => [
            'description' => 'Optimize WooCommerce product description',
            'template' => "Create an SEO-optimized product description that converts.\n\nProduct: {product_name}\nCategory: {category}\nFeatures: {features}\nPrice: {price}\nCompetitors: {competitors}\n\nRules:\n- Highlight benefits not features\n- Include keywords naturally\n- Focus on solving problems\n- Drive conversion",
            'defaults' => []
        ],
        
        'job_listing' => [
            'description' => 'Optimize job listing content',
            'template' => "Optimize this job listing for better visibility and response rate.\n\nJob Title: {job_title}\nCompany: {company}\nDescription: {description}\nRequirements: {requirements}\nLocation: {location}\n\nRules:\n- Include relevant job keywords\n- Emphasize unique benefits\n- Be clear and specific\n- Include call-to-action",
            'defaults' => []
        ]
    ];
    
    public static function build($prompt_key, $content, $context = []) {
        if (!isset(self::$templates[$prompt_key])) {
            return false;
        }
        
        $template_data = self::$templates[$prompt_key];
        $template = $template_data['template'];
        $defaults = $template_data['defaults'] ?? [];
        
        // Merge context with defaults
        $variables = array_merge($defaults, $context);
        $variables['content'] = $content;
        
        // Add additional context
        if (!isset($variables['keyword']) && isset($context['keyword'])) {
            $variables['keyword'] = $context['keyword'];
        }
        
        if (!isset($variables['title']) && isset($context['title'])) {
            $variables['title'] = $context['title'];
        }
        
        // Replace placeholders
        $result = $template;
        foreach ($variables as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $result = str_replace('{' . $key . '}', $value, $result);
        }
        
        // Handle optional sections
        $result = self::remove_unfilled_placeholders($result);
        
        return $result;
    }
    
    private static function remove_unfilled_placeholders($text) {
        // Remove any remaining placeholders
        return preg_replace('/\{[^}]+\}/', '', $text);
    }
    
    public static function get_template($key) {
        return self::$templates[$key] ?? null;
    }
    
    public static function get_all_templates() {
        return self::$templates;
    }
}