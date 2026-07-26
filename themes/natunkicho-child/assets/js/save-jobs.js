jQuery(document).ready(function($){
    $(document).on('click', '.nk-save-job-btn', function(e){
        e.preventDefault();
        
        let button = $(this);
        let jobID = button.data('job');
        let nonce = button.data('nonce'); // Fetch security token

        if (button.hasClass('login-to-save')) {
            window.location.href = button.attr('href');
            return;
        }

        $.ajax({
            type: 'POST',
            url: nk_ajax.ajax_url,
            data: {
                action: 'nk_save_job',
                job_id: jobID,
                nonce: nonce
            },
            beforeSend: function(){
                button.text('Updating...');
            },
            success: function(response){
                if(response.success){
                    if (response.status === 'saved') {
                        button.text('✓ Saved').addClass('saved');
                    } else {
                        button.text('♡ Save Job').removeClass('saved');
                    }
                } else {
                    if (response.message === 'Please login first.') {
                        window.location.href = '/login/';
                    } else {
                        alert(response.message);
                        button.text('Error');
                    }
                }
            }
        });
    });
});