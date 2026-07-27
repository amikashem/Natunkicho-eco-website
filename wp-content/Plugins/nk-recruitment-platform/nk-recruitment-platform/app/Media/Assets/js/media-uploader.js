jQuery(document).ready(function ($) {

    function bindMediaUploader(button, input, preview) {

        $(document).on('click', button, function (e) {

            e.preventDefault();

            const frame = wp.media({
                title: 'Select Image',
                library: {
                    type: 'image'
                },
                button: {
                    text: 'Use this image'
                },
                multiple: false
            });

            frame.off('select');

            frame.on('select', function () {

                const attachment = frame
                    .state()
                    .get('selection')
                    .first()
                    .toJSON();

                $(input).val(attachment.url).trigger('change');

                $(preview).html(
                    '<img src="' + attachment.url + '" style="max-width:150px;border:1px solid #ddd;padding:5px;border-radius:6px;">'
                );

            });

            frame.open();

        });

    }

    bindMediaUploader(
        '#upload_company_logo',
        '#company_logo',
        '#company_logo_preview'
    );

    bindMediaUploader(
        '#upload_company_cover',
        '#company_cover',
        '#company_cover_preview'
    );

});