jQuery(document).ready(function ($) {

    var selector = $('.misc-pub-crush-img-head');
    var id = $('.misc-pub-crush-img-head').find('.wp_image_compress').attr('data-id');
    var size = 'full';
    var status = $('.misc-pub-crush-img-head').find('.misc-pub-original-size').attr('data-status');
    if (status == 'enqueued') {
        var check_interval = setInterval(check_status, 1000, id, size, selector);
        selector.attr('data-interval', check_interval);
    }

    $(document).on('click', '#edit_compression_type', function (e) {
        e.preventDefault();
        $('#choose-compression-type').modal('show');
        var radio_id = $('.compression_type_value').attr('radio_id');
        if (radio_id) {
            $('#' + radio_id).attr('checked', true);
        } else {
            $('#' + wpic.compression_type_id).attr('checked', true);

        }
    });
    $('input[name="compression_type"]').click(function () {
        $("input[name='compression_type']:checked").attr('checked', false);
        $(this).attr('checked', true);
    });
    $('.compression_type_save').click(function () {
        var button = $('.compression_type_save');
        var compression_type = $("input[name='compression_type']:checked").val();
        var radio_id = $("input[name='compression_type']:checked").attr('id');
        var old_button_text = button.val();
        button.val(wpic.compression_type_data_save).attr('disabled', 'true');

        var data = {
            action: 'wpic_compression_type_data_save',
            compression_type: compression_type,
            id: wpic.image_id
        }

        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'post',
            success: function (data) {
                if (data) {
                    $('.compression_type_value').text(data);
                    $('.compression_type_value').attr('radio_id', radio_id);
                }
            }, complete: function () {
                button.val(old_button_text).removeAttr('disabled');
                jQuery('#choose-compression-type').modal('hide');
            }
        });
    });
    //image compress
    $(document).on('click', '.wp_image_compress', function () {
        var upgrade = $('.main-card-container').attr('data-upgrade');
        if (upgrade == 'yes') {
            jQuery('#upgrade_error').modal('show');
        } else {
            var button = $(this);
            var id = button.attr('data-id');
            var url = button.attr('data-guid');
            var size = button.attr('data-size');
            var status = button.attr('data-status');
            button.hide();
            $('#edit_compression_type').hide();
            button.parent().find('.queued').css('display', 'inline-flex');
            button.parent().find('.not-crushed-status').hide();
            button.parent().find('.already-crushed').hide();
            button.parent().find('.restored-status').hide();

            var data = {
                action: 'wpic_single_image_media_compress',
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
                        button.parent().find('.queued').hide();
                        button.parent().find('.crush-spinner').show();
                        $('#edit_compression_type').hide();
                        var crushed_id = data.crushed_id;
                        button.attr('data-crushed_id', crushed_id);
                    } else {
                        if (data && data.error) {
                            jQuery('#general-error-status-msg .modal-body').text(data.error);
                            jQuery('#general-error-status-msg').modal('show');
                        }
                        button.parent().find('.queued').hide();
                        button.parent().find('.crush-spinner').hide();
                        button.show();
                        $('#edit_compression_type').show();
                        if (status && status == 'restored') {
                            button.parent().find('.restored-status').css('display', 'inline-flex');
                            button.parent().find('.not-crushed-status').hide();
                            button.parent().find('.already-crushed').hide();
                        } else {
                            button.parent().find('.restored-status').hide();
                            button.parent().find('.not-crushed-status').css('display', 'inline-flex');
                        }
                    }


                }, complete: function () {
                    var crushed_id = button.attr('data-crushed_id');
                    if (crushed_id) {
                        var interval = setInterval(check_compress, 1000, crushed_id, button);
                        button.attr('data-interval', interval);
                    }

                }
            });
        }
    });

    //image restore
    $(document).on('click', '.wp_image_restore', function (e) {
        var button = $(this);
        var id = button.attr('data-id');
        var url = button.attr('data-guid');
        var size = button.attr('data-size');
        var backup_image = button.attr('data-backup');
        button.hide();
        button.parent().find('.restore-spinner').show();

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
                button.parent().find('.restore-spinner').hide();
                if (data.image_id && data.image_url) {
                    button.parent().find('.wp_image_compress').show();
                    $('#edit_compression_type').show();
                    button.parent().find('.wp_image_compress').attr('data-guid', data.image_url);
                    button.parent().find('.wp_image_compress').attr('data-id', data.image_id);
                    button.parent().find('.wp_image_compress').attr('data-status', 'restored');
                    button.parent().find('.wp_image_compress').attr('data-size', size);
                    fill_image_details(id, 'image_id', size);
                } else {
                    button.show();
                }

            }, complete: function () {
            }
        });
    });

});


function check_compress(crushed_id, button) {
    if (crushed_id && button) {
        var data = {
            action: 'wpic_image_check_compress',
            id: crushed_id
        };

        jQuery.ajax({
            url: ajaxurl,
            data: data,
            type: 'post',
            success: function (data) {
                if (data.status) {
                    if (data.status == 'crushed') {
                        button.parent().find('.crush-spinner').hide();
                        if (data.backup == 'yes') {
                            jQuery('#edit_compression_type').hide();
                            button.parent().find('.wp_image_restore').show();
                            button.parent().find('.restored-status').hide();
                            button.parent().find('.wp_image_restore').attr('data-backup', data.image_backup_path);
                            button.parent().find('.wp_image_restore').attr('data-guid', data.image_url);
                            button.parent().find('.wp_image_restore').attr('data-id', data.image_id);
                        }
                        update_quota_used_card();
                        var interval = button.attr('data-interval');
                        clearInterval(interval);
                    } else {
                        button.parent().find('.crush-spinner').hide();
                        button.show();
                        jQuery('#edit_compression_type').show();
                        var interval = button.attr('data-interval');
                        clearInterval(interval);
                        //show modal
                        //jQuery('#error-status-msg').modal('show');
                    }
                    fill_image_details(crushed_id, '', data.image_size);
                }
            }, complete: function () {

            }
        });
    } else {
        var interval = button.attr('data-interval');
        clearInterval(interval);
    }

}

function check_status(id, size, row) {
    if (id && size && row) {
        var check_interval = row.attr('data-interval');
        var data = {
            action: 'wpic_image_check_status',
            id: id,
            size: size
        };

        jQuery.ajax({
            url: ajaxurl,
            data: data,
            type: 'post',
            success: function (data) {
                if (data.status) {
                    if (data.status == 'enqueued') {
                        row.attr('data-process', 'yes');
                        row.find('.crush-spinner').show();
                        row.find('.wp_image_restore').hide();
                        row.find('.wp_image_compress').hide();
                        jQuery('#edit_compression_type').hide();

                    } else if (data.status == 'crushed') {
                        row.find('.wp_image_compress').hide();
                        row.find('.crush-spinner').hide();
                        jQuery('#edit_compression_type').hide();
                        if (data.backup == 'yes') {
                            row.find('.wp_image_restore').show();
                            row.find('.wp_image_restore').attr('data-backup', data.image_backup_path);
                            row.find('.wp_image_restore').attr('data-guid', data.image_url);
                            row.find('.wp_image_restore').attr('data-id', data.image_id);
                        }
                        clearInterval(check_interval);
                    } else if (data.status == 'error') {

                        row.find('.crush-spinner').hide();
                        row.find('.wp_image_compress').show();
                        jQuery('#edit_compression_type').show();
                        var enqueued_process = row.attr('data-process');
                        if (enqueued_process == 'yes') {
                            //jQuery('#error-status-msg').modal('show');
                            clearInterval(check_interval);
                        } else {
                            clearInterval(check_interval);
                        }
                    } else {
                        clearInterval(check_interval);
                    }
                    fill_image_details(id, 'image_id', size);
                } else {
                    clearInterval(check_interval);
                }
            }, complete: function () {

            }
        });
    } else {
        clearInterval(check_interval);
    }
}

function fill_image_details(id, type, size) {
    var $ = jQuery;
    if (id) {

        var data = {
            action: 'wpic_fill_image_details',
            id: id
        };
        if (type == 'image_id') {
            data.type = 'image_id';
        } else {
            data.type = 'crush_id';
        }
        if (size) {
            data.size = size;
        }

        jQuery.ajax({
            url: ajaxurl,
            data: data,
            type: 'post',
            success: function (data) {
                if (data.image_id) {
                    if (size == 'full') {
                        $('.crushed_size_value').text('-');
                        $('.saved_value').text('-');
                        if (data.crushed_size) {
                            $('.crushed_size_value').text(data.crushed_size);
                        }
                        if (data.saved) {
                            $('.saved_value').text(data.saved);
                        }

                    }
                }

            }, complete: function () {

            }
        });

    }

}
function update_quota_used_card() {
    var data = {
        action: 'wpic_update_quota_used_card'
    };

    jQuery.ajax({
        url: ajaxurl,
        data: data,
        success: function (data) {
            if (data.status == 'valid') {
                jQuery('.main-card-container').attr('data-upgrade', 'no')
                jQuery('.upgrade-plan-container').hide();
                jQuery('.quota-used-card .used').text(data.quota_usage + ' MB');
                jQuery('.quota-used-card .quota').text('/ ' + data.bytes + ' MB');
                jQuery('.quota-used-card .progress-bar').attr('style', 'width:' + data.precentage + '%');
            } else if (data.status == 'upgrade') {
                jQuery('.main-card-container').attr('data-upgrade', 'yes')
                jQuery('.upgrade-plan-container').show();
                jQuery('.upgrade-plan-url').attr('href', data.upgrade_url);
            }
        }
    });
}