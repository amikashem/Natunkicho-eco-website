(function($) {
    'use strict';

    class NKAIHandler {
        constructor() {
            this.initialize();
        }

        initialize() {
            // Wait for Rank Math to load
            this.waitForRankMath();
            
            // Setup event listeners
            this.setupListeners();
            
            // Add keyboard shortcuts
            this.setupShortcuts();
        }

        waitForRankMath() {
            const checkInterval = setInterval(() => {
                if (this.isRankMathLoaded()) {
                    clearInterval(checkInterval);
                    this.addAIButtons();
                }
            }, 500);
            
            // Timeout after 10 seconds
            setTimeout(() => clearInterval(checkInterval), 10000);
        }

        isRankMathLoaded() {
            return (
                typeof rankMath !== 'undefined' &&
                typeof rankMath.analysis !== 'undefined'
            );
        }

        addAIButtons() {
            const fields = this.identifyFields();
            
            fields.forEach(field => {
                const button = this.createAIButton(field);
                field.element.after(button);
            });
        }

        identifyFields() {
            const fields = [];
            
            // SEO Title
            const titleField = document.querySelector('#rank_math_title');
            if (titleField) {
                fields.push({
                    id: 'title',
                    type: 'seo_title',
                    element: titleField,
                    fieldType: 'input'
                });
            }
            
            // Meta Description
            const descField = document.querySelector('#rank_math_description');
            if (descField) {
                fields.push({
                    id: 'description',
                    type: 'meta_description',
                    element: descField,
                    fieldType: 'textarea'
                });
            }
            
            // Focus Keyword
            const keywordField = document.querySelector('#rank_math_focus_keyword');
            if (keywordField) {
                fields.push({
                    id: 'keyword',
                    type: 'focus_keyword',
                    element: keywordField,
                    fieldType: 'input'
                });
            }
            
            return fields;
        }

        createAIButton(field) {
            const button = document.createElement('button');
            button.className = 'button nk-ai-button';
            button.setAttribute('data-field', field.id);
            button.setAttribute('data-type', field.type);
            button.innerHTML = '✨ NK AI';
            button.title = 'Generate AI content for this field';
            
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleAIButtonClick(field, button);
            });
            
            return button;
        }

        async handleAIButtonClick(field, button) {
            // Disable button and show loading state
            button.disabled = true;
            button.innerHTML = '⏳ Processing...';
            
            try {
                const content = this.getContentForAI();
                const context = this.getContext(field);
                
                const response = await this.callAIGateway(field.type, content, context);
                
                if (response.success) {
                    this.insertAIContent(field, response.result);
                    this.showNotification('AI content generated successfully!', 'success');
                } else {
                    throw new Error(response.error || 'AI generation failed');
                }
            } catch (error) {
                this.showNotification(error.message, 'error');
                console.error('AI generation error:', error);
            } finally {
                // Restore button state
                button.disabled = false;
                button.innerHTML = '✨ NK AI';
            }
        }

        getContentForAI() {
            // Get content from WordPress editor
            const editor = document.querySelector('#content');
            let content = '';
            
            if (editor) {
                if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                    content = tinymce.get('content').getContent();
                } else {
                    content = editor.value;
                }
            }
            
            // Also get title
            const title = document.querySelector('#title')?.value || '';
            
            return {
                content: content,
                title: title,
                excerpt: document.querySelector('#excerpt')?.value || '',
            };
        }

        getContext(field) {
            const postId = document.querySelector('#post_ID')?.value || 0;
            const postType = document.querySelector('#post_type')?.value || 'post';
            
            return {
                post_id: postId,
                post_type: postType,
                field_id: field.id,
                current_value: field.element.value,
                language: document.documentElement.lang || 'en_US',
                site_name: document.querySelector('#wp-admin-bar-site-name')?.textContent || ''
            };
        }

        async callAIGateway(type, content, context) {
            const data = {
                action: 'nk_ai_generate',
                type: type,
                content: content,
                context: context,
                nonce: nk_ai_rankmath.nonce
            };
            
            const response = await fetch(nk_ai_rankmath.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            });
            
            return await response.json();
        }

        insertAIContent(field, result) {
            const element = field.element;
            
            // Trigger change events to let Rank Math know field updated
            if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                element.value = result;
                $(element).trigger('change');
                $(element).trigger('input');
            }
            
            // Trigger Rank Math's update
            if (typeof rankMath !== 'undefined' && rankMath.analysis) {
                rankMath.analysis.update();
            }
            
            // Update field in Rank Math if applicable
            this.updateRankMathField(field, result);
        }

        updateRankMathField(field, value) {
            // Update Rank Math's internal state if needed
            if (field.id === 'title' && typeof rankMath !== 'undefined') {
                rankMath.title = value;
            } else if (field.id === 'description' && typeof rankMath !== 'undefined') {
                rankMath.description = value;
            }
        }

        showNotification(message, type = 'info') {
            const notice = document.createElement('div');
            notice.className = `notice notice-${type} is-dismissible`;
            notice.innerHTML = `
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss</span>
                </button>
            `;
            
            const container = document.querySelector('#wpcontent') || document.body;
            container.prepend(notice);
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                notice.style.transition = 'opacity 0.5s';
                notice.style.opacity = '0';
                setTimeout(() => notice.remove(), 500);
            }, 5000);
            
            // Dismiss button
            notice.querySelector('.notice-dismiss').addEventListener('click', () => {
                notice.remove();
            });
        }

        setupListeners() {
            // Listen for Rank Math field updates
            $(document).on('rank_math_field_update', (e, data) => {
                if (data.field && data.value) {
                    // Update AI button state if needed
                }
            });
            
            // Listen for editor changes
            $(document).on('editor_change', () => {
                // Update AI suggestions based on new content
                this.updateAISuggestions();
            });
        }

        updateAISuggestions() {
            // Debounced function to update AI suggestions
            clearTimeout(this.suggestionsTimeout);
            this.suggestionsTimeout = setTimeout(() => {
                // Check if content significantly changed
                const content = this.getContentForAI();
                // Update recommendations if needed
            }, 1000);
        }

        setupShortcuts() {
            // Keyboard shortcuts
            $(document).on('keydown', (e) => {
                // Alt + Shift + A: Generate AI for current field
                if (e.altKey && e.shiftKey && e.key === 'A') {
                    e.preventDefault();
                    const activeField = document.activeElement;
                    if (activeField && activeField.closest('.rank-math-field')) {
                        const field = this.identifyFields().find(
                            f => f.element === activeField || 
                                 f.element === activeField.closest('input, textarea')
                        );
                        if (field) {
                            const button = activeField.closest('.rank-math-field')
                                .querySelector('.nk-ai-button');
                            if (button) {
                                button.click();
                            }
                        }
                    }
                }
            });
        }
    }

    // Initialize when DOM is ready
    $(document).ready(() => {
        window.NKAIHandler = new NKAIHandler();
    });

})(jQuery);