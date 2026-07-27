document.addEventListener('DOMContentLoaded', function(){
    const keyword = document.querySelector('.job_filters .search_keywords input');
    const location = document.querySelector('.job_filters .search_location input');

    if(keyword) keyword.placeholder = 'Chef, Hotel Manager, Receptionist...';
    if(location) location.placeholder = 'Dubai, Singapore, London...';
}); 

jQuery(document).ready(function($){
    // Update Apply Job Button
    $('.nk-apply-job-btn').on('click', function(e){
        e.preventDefault();
        let button = $(this);
        $.ajax({
            url: nk_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'nk_apply_job',
                job_id: button.data('job')
            },
            success: function(response){
                // Replace alert with Toast Notification
                nk_show_toast(response.message, response.success ? 'success' : 'error');
            }
        });
    });

    // Update AI CV Builder
    $('.nk-ai-cv-builder form').on('submit', function(){
        let button = $('.nk-ai-btn');
        button.addClass('loading').prop('disabled', true).text('Generating...');
    });
});

