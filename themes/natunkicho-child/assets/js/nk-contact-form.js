(function($){
    'use strict';

    document.addEventListener("DOMContentLoaded", function() {
        let form = document.getElementById("nk-contact-form");
        if (!form) return;

        form.addEventListener("submit", function(e){
            e.preventDefault();

            let result = document.getElementById("nk-contact-result");
            let btn = document.getElementById("nk-contact-submit");
            let originalBtnText = btn.textContent;
            
            // Clear previous messages
            result.innerHTML = "";
            result.className = "nk-contact-result";
            
            // Disable button and show loading
            btn.disabled = true;
            btn.textContent = "Sending...";
            btn.style.opacity = "0.6";

            // Validate required fields
            let emailField = document.getElementById("nk_email");
            let mathField = document.getElementById("nk_math");
            
            if (!emailField.value.trim()) {
                showError("Please enter your email address.", btn, originalBtnText);
                emailField.focus();
                return;
            }
            
            if (!mathField.value.trim()) {
                showError("Please answer the security question.", btn, originalBtnText);
                mathField.focus();
                return;
            }

            // Prepare form data
            let formData = new FormData(form);
            formData.append("action", "nk_contact_submit");
            
            // Add nonce from localized script
            if (typeof nkContactVars !== 'undefined') {
                formData.append("nonce", nkContactVars.nonce);
            } else {
                console.error("nkContactVars is not defined");
                showError("Configuration error. Please refresh the page.", btn, originalBtnText);
                return;
            }

            // Determine AJAX URL
            let ajaxUrl = nkContactVars.ajax_url;
            
            // Debug log (remove in production)
            console.log("Sending to:", ajaxUrl);
            
            // Send request
            fetch(ajaxUrl, {
                method: "POST",
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(resp => {
                btn.disabled = false;
                btn.textContent = originalBtnText;
                btn.style.opacity = "1";

                if (resp.success) {
                    showSuccess(resp.data.message);
                    form.reset();
                    
                    // Auto-hide success message after 5 seconds
                    setTimeout(() => {
                        if (result.classList.contains('success')) {
                            result.innerHTML = "";
                            result.className = "nk-contact-result";
                        }
                    }, 5000);
                } else {
                    showError(resp.data.message || "An error occurred.");
                }
            })
            .catch(error => {
                console.error("Fetch error:", error);
                showError("Request failed. Please check your connection and try again.", btn, originalBtnText);
            });

        });

        function showError(message, btn = null, originalBtnText = null) {
            let result = document.getElementById("nk-contact-result");
            result.innerHTML = `<span class="nk-error">${message}</span>`;
            result.className = "nk-contact-result error";
            
            if (btn && originalBtnText) {
                btn.disabled = false;
                btn.textContent = originalBtnText;
                btn.style.opacity = "1";
            }
        }

        function showSuccess(message) {
            let result = document.getElementById("nk-contact-result");
            result.innerHTML = `<span class="nk-success">${message}</span>`;
            result.className = "nk-contact-result success";
        }
    });

})(jQuery);