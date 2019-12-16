//var interval;

jQuery(document).ready(function ($) {
    //image compress
    $(document).on('click', '.wp_image_compress', function () {
        var crushed_id = '';
        var button = $(this);
        var id = button.attr('data-id');
        var url = button.attr('data-guid');
        var size = button.attr('data-size');
        var status = button.attr('data-status');

        button.hide();
        button.closest("tr").find('.crush-spinner').show();
        button.closest('tr').find('.not-crushed-status').hide();
        button.closest('tr').find('.restored-status').hide();

        var data = {
            action: 'wpic_image_compress',
            id: id,
            url: url,
            size: size
        };

        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'post',
            success: function (data) {
                if (data && data.crushed_id) {
                    crushed_id = data.crushed_id;
                } else {
                    button.closest("tr").find('.crush-spinner').hide();
                    button.show();
                    if (status && status == 'restored') {
                        button.closest('tr').find('.restored-status').show();
                        button.closest('tr').find('.not-crushed-status').hide();
                    } else {
                        button.closest('tr').find('.restored-status').hide();
                        button.closest('tr').find('.not-crushed-status').show();
                    }
                }


            }, complete: function () {
                if (crushed_id) {
                    var interval = setInterval(check_compress, 1000, crushed_id, button);
                    button.attr('data-interval', interval);
                }

            }
        });
    });

    //image restore
    $(document).on('click', '.wp_image_restore', function () {
        var button = $(this);
        var id = button.attr('data-id');
        var url = button.attr('data-guid');
        var size = button.attr('data-size');
        var backup_image = button.attr('data-backup');
        button.hide();
        button.closest("tr").find('.restore-spinner').show();
        button.closest('tr').find('.crushed-status').hide();
        button.closest('tr').find('.saved-image').hide();

        var data = {
            action: 'wpic_image_restore',
            id: id,
            url: url,
            size: size,
            backup_image: backup_image
        };

        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'post',
            success: function (data) {
                button.closest("tr").find('.restore-spinner').hide();
                if (data.image_id && data.image_url) {
                    button.closest('tr').find('.restored-status').show();
                    button.closest('tr').find('.wp_image_compress').show();
                    button.closest('tr').find('.wp_image_compress').attr('data-guid', data.image_url);
                    button.closest('tr').find('.wp_image_compress').attr('data-id', data.image_id);
                    button.closest('tr').find('.wp_image_compress').attr('data-status', 'restored');
                } else {
                    button.show();
                    button.closest('tr').find('.crushed-status').show();
                    button.closest('tr').find('.saved-image').show();
                }

            }, complete: function () {
            }
        });
    });
    function check_compress(crushed_id, button) {
        if (crushed_id && button) {
            console.log(crushed_id);
            var data = {
                action: 'wpic_image_check_compress',
                id: crushed_id
            };

            $.ajax({
                url: ajaxurl,
                data: data,
                type: 'post',
                success: function (data) {
                    console.log(data);
                    if (data.backup && data.saved) {
                        button.closest("tr").find('.crush-spinner').hide();
                        button.closest('tr').find('.not-crushed-status').hide();
                        button.closest('tr').find('.crushed-status').show();
                        if (data.backup == 'yes') {
                            button.closest('tr').find('.wp_image_restore').show();
                            button.closest('tr').find('.restored-status').hide();
                            button.closest('tr').find('.wp_image_restore').attr('data-backup', data.image_backup_path);
                            button.closest('tr').find('.wp_image_restore').attr('data-guid', data.image_url);
                            button.closest('tr').find('.wp_image_restore').attr('data-id', data.image_id);
                        }
                        if (data.saved) {
                            button.closest('tr').find('.saved-image').show();
                            button.closest('tr').find('.saved-image').text(data.saved);
                        }
                        var interval = button.attr('data-interval');
                        clearInterval(interval);
                    }
                }, complete: function () {
                }
            });
        } else {
            var interval = button.attr('data-interval');
            clearInterval(interval);
        }

    }

});


