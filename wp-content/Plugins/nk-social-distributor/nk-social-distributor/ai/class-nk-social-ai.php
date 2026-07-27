<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class NK_Social_AI {

    /**
     * Generates a platform-specific caption based on content type.
     */
    public static function generate_caption( $post_id, $platform ) {
        $post = get_post( $post_id );
        $post_type = $post->post_type;
        
        $title = $post->post_title;
        $url = get_permalink( $post_id );
        
        // This is a dynamic prompt builder. We customize the vibe per platform.
        $prompt = "";

        if ( $post_type === 'job_listing' ) {
            $location = get_post_meta( $post_id, '_job_location', true );
            $company = get_post_meta( $post_id, '_company_name', true );
            $company_text = $company ? " at $company" : "";
            $location_text = $location ? " in $location" : "";
            
            if ( $platform === 'linkedin' ) {
                $prompt = "Write a highly professional LinkedIn post announcing a new hospitality job opening for '$title'$company_text$location_text. Keep it under 3 short paragraphs. Include 3 professional hashtags. Do not include the URL in the text, just say 'Apply below'.";
            } elseif ( $platform === 'telegram' ) {
                $prompt = "Write a short, punchy Telegram alert (using emojis) for a new job opening: '$title'$location_text. Include #HospitalityJobs. End with 'Apply via NatunKicho: {url}'.";
            }
        } elseif ( $post_type === 'post' ) {
            // It's a Blog Post
            $excerpt = wp_trim_words( $post->post_content, 30 );
            if ( $platform === 'linkedin' ) {
                $prompt = "Write an engaging LinkedIn post summarizing this article: '$title'. Article context: '$excerpt'. Ask a question to drive engagement. Use 3 professional hashtags.";
            } else {
                $prompt = "Write a quick, engaging social media snippet for this blog post: '$title'. Use 2 emojis. End with 'Read More: {url}'.";
            }
        }

        // ------------------------------------------------------------------
        // TODO: CONNECT TO NATUNKICHO'S AI API HERE (OpenAI / Internal API)
        // ------------------------------------------------------------------
        // For Phase 2, we return a cleanly formatted fallback string until 
        // we lock in your specific API credentials.
        
        $fallback_caption = "🚀 New Update from NatunKicho!\n\n📌 $title\n\n👉 View Details: $url\n\n#NatunKicho #Hospitality";
        
        return $fallback_caption;
    }
}