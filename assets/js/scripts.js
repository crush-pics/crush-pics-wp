jQuery(document).ready(function ($) {

    // compare functionality

    $( document ).on( "click", ".image-preview-link", function( e ) {
        e.preventDefault();
        var t = $( e.currentTarget ),
            i = t.siblings( ".dashboard-image-name" ).text();
        return  openPreviewPopup( t.data( wpic.compare_before ), t.data( wpic.compare_after ), i );
    } )

   function openPreviewPopup( e, t, i ) {
        var n = $( "#image-preview-modal" );
        if ( n.find( ".modal-title" ).text( i ), void 0 !== e ) {
            var a = $( '<div class="compare twentytwenty-container"><img src="' + e + '"><img src="' + t + '"></div>' );
            n.find( ".modal-body" ).html( a ), n.on( "shown.bs.modal", function() {
                $( window ).trigger( "resize" )
            } ), $( "#image-preview-modal img" ).on( "load", function() {
                $( "#image-preview-modal .compare" ).twentytwenty()
            } )
        } else {
            var s = '<img class="img-fluid" src="' + t + '">';
            n.find( ".modal-body" ).html( s )
        }
        n.modal( "show" )
    }

    //dashboard change compression auto
    $('#change_compression_auto').change(function () {
        var value = '';
        if ($(this).is(':checked')) {
            value = 'yes';
        }

        var data = {
            action: 'wpic_change_compression_auto',
            value: value
        }

        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'post'
        });
    });

    //compression type
    $('.compression_type_radio').click(function () {
        if ($(this).is(':checked')) {
            $('.border.compression_type_selected').removeClass('compression_type_selected');
            $(this).closest('.border').addClass('compression_type_selected');
        }
    });

    //api key revalidation
    $('.api_key_revalidate').click(function () {
        var api_key = $('.api_key_value').val();
        $('.invalid-feedback').remove();
        $('.valid-feedback').remove();
        $('.api_key_value').removeClass('is-invalid').removeClass('is-valid');

        if (!api_key) {
            $('.api_key_value').focus();
            $('.api_key_value').addClass('is-invalid').after("<span class='invalid-feedback'>" + wpic.api_key_creat_required_field_error + "</span>");
        } else {
            var button = $('.api_key_revalidate');
            var old_button_text = button.val();
            button.val(wpic.api_key_validate).attr('disabled', 'true');
            $('.wpic_api_key_error').hide().text('');

            var data = {
                action: 'wpic_validate_account',
                api_key: api_key
            }

            $.ajax({
                url: ajaxurl,
                data: data,
                type: 'post',
                success: function (data) {
                    if (data == 'valid') {
                        $('.api_key_value').addClass('is-valid').after("<span class='valid-feedback'>" + wpic.api_key_valid_message + "</span>");
                    } else {
                        $('.api_key_value').addClass('is-invalid').after("<span class='invalid-feedback'>" + wpic.api_key_validate_error + "</span>");
                    }
                }, complete: function () {
                    button.val(old_button_text).removeAttr('disabled');
                }
            });
        }
    });

    $('[data-toggle="tooltip"]').tooltip();

    //quality popup
    var jpeg_quality = false;
    var png_quality = false;
    var gif_quality = false;

    $('#custom-sizes-details').on('shown.bs.modal', function () {
        var jpeg_quality_value = wpic.custom_quality_jpeg;
        if (jpeg_quality) {
            jpeg_quality_value = jpeg_quality;
            $("#custom-sizes-details .jpeg .value,.custom_quality_jpeg").text(jpeg_quality + '%');
        }

        $("#custom-sizes-details .jpeg_quality").slider({
            min: 65,
            max: 95,
            value: jpeg_quality_value,
            slide: function (event, ui) {
                $("#custom-sizes-details .jpeg .value,.custom_quality_jpeg").text(ui.value + '%');
                $(".compression_type_custom_jpeg").val(ui.value);
                jpeg_quality = ui.value;
            }
        });

        var png_quality_value = wpic.custom_quality_png;
        if (png_quality) {
            png_quality_value = png_quality;
            $("#custom-sizes-details .png .value,.custom_quality_png").text(png_quality + '%');
        }

        $("#custom-sizes-details .png_quality").slider({
            min: 65,
            max: 95,
            value: png_quality_value,
            slide: function (event, ui) {
                $("#custom-sizes-details .png .value,.custom_quality_png").text(ui.value + '%');
                $(".compression_type_custom_png").val(ui.value);
                png_quality = ui.value;
            }
        });

        var gif_quality_value = wpic.custom_quality_gif;
        if (gif_quality) {
            gif_quality_value = gif_quality;
            $("#custom-sizes-details.gif .value,.custom_quality_gif").text(gif_quality + '%');
        }

        $("#custom-sizes-details .gif_quality").slider({
            min: 65,
            max: 95,
            value: gif_quality_value,
            slide: function (event, ui) {
                $("#custom-sizes-details .gif .value,.custom_quality_gif").text(ui.value + '%');
                $(".compression_type_custom_gif").val(ui.value);
                gif_quality = ui.value;
            }
        });
    });

    $('.compression_quality_save').click(function () {
        var button = $('.compression_quality_save');
        var jpeg_quality = $('.compression_type_custom_jpeg').val();
        var png_quality = $('.compression_type_custom_png').val();
        var gif_quality = $('.compression_type_custom_gif').val();

        var old_button_text = button.val();
        button.val(wpic.custom_quality_data_save).attr('disabled', 'true');

        var data = {
            action: 'wpic_custom_quality_data_save',
            jpeg_quality: jpeg_quality,
            png_quality: png_quality,
            gif_quality: gif_quality
        }

        $.ajax({
            url: ajaxurl,
            data: data,
            type: 'post',
            success: function (data) {
                if (data == 'done') {
                    $('#custom-sizes-details').modal('toggle');
                }
            }, complete: function () {
                button.val(old_button_text).removeAttr('disabled');
            }
        });
    });

    // show validate create account
    $( '.wpic_let_s_go' ).click( function () {
        $( '.wpic_welcome_container' ).fadeOut( 500, 'linear', function () {
            $( '.wpic_api_validate_create_container' ).fadeIn( 500 );
        } );
    } );

    //show api key validate
    $('.wpic_api_key_validate').click(function () {
        $('.wpic_api_key_create_container').fadeOut(500, 'linear', function () {
            $('.wpic_api_key_validation_container').fadeIn(500);
        });
    });

    // Move to STEP 2
    $( '.wpic_api_key_validate' ).click( function () {
        $( ".progerss_bar-custom-screen .is-current" ).removeClass( "is-current" ).parent().children( ".wpic_step_2" ).addClass( "is-current" );
        $( ".wpic_progerss_bar .wpic_step_1 .wpic_heavy_check_mark" ).css( "display", "inline" );
    } );

    //validate account
    $('.wpic_api_key_submit').click(function () {
        var button = $('.wpic_api_key_submit');
        var api_key = $('.wpic_api_key').val();

        $('.invalid-feedback').remove();
        $('.wpic_api_key').removeClass('is-invalid');

        if (!api_key) {
            $('.wpic_api_key').focus();
            $('.wpic_api_key').addClass('is-invalid').after("<span class='invalid-feedback'>" + wpic.api_key_creat_required_field_error + "</span>");
        } else {
            var old_button_text = button.val();
            button.val(wpic.api_key_validate).attr('disabled', 'true');
            button.parent().find('.loader').show();

            var data = {
                action: 'wpic_validate_account',
                api_key: api_key
            }

            $.ajax({
                url: ajaxurl,
                data: data,
                type: 'post',
                success: function (data) {
                    button.parent().find('.loader').hide();
                    if (data == 'valid') {
                        button.val(wpic.api_key_validate_accepted).removeAttr('disabled');
                        button.parent().find('.api-key-valid').show();
                        $( ".progerss_bar-custom-screen .is-current" ).removeClass( "is-current" ).parent().children( ".wpic_step_3" ).addClass( "is-current" );
                        $( ".wpic_progerss_bar .wpic_step_2 .wpic_heavy_check_mark" ).css( "display", "inline" );
                        setTimeout(function () {
                            window.location = '?page=crush-pics-settings&api_key=true';
                        }, 2000);
                    } else {
                        button.val(old_button_text).removeAttr('disabled');
                        $('.wpic_api_key').addClass('is-invalid').after("<span class='invalid-feedback'>" + wpic.api_key_validate_error + "</span>");
                    }
                }, complete: function () {

                }
            });
        }
    });

    //show create account
    $('.wpic_api_key_create').click(function () {
        $('.wpic_api_key_validation_container').fadeOut(500, 'linear', function () {
            $('.wpic_api_key_create_container').fadeIn(500);
        });
    });

    // Move to STEP 1
    $( '.wpic_api_key_create' ).click( function () {
        $( ".progerss_bar-custom-screen .is-current" ).removeClass( "is-current" ).parent().children( ".wpic_step_1" ).addClass( "is-current" );
        $( ".wpic_progerss_bar .wpic_step_1 .wpic_heavy_check_mark" ).hide();
    });

    //creat account
    $('.wpic_create_account_submit').click(function () {
        var button = $('.wpic_create_account_submit');
        var email = $('.wpic_email').val();
        var password = $('.wpic_password').val();
        var password_confirm = $('.wpic_password_confirm').val();
        var erros = false;

        //clear all errors
        $('.invalid-feedback').remove();
        $('.wpic_email,.wpic_password,.wpic_password_confirm').removeClass('is-invalid');

        //validation
        //email
        if (!email) {
            $('.wpic_email').addClass('is-invalid').after("<span class='invalid-feedback'>" + wpic.api_key_creat_required_field_error + "</span>");
            erros = true;
        } else if (!validateEmail(email)) {
            $('.wpic_email').addClass('is-invalid').after("<span class='invalid-feedback'>" + wpic.api_key_creat_email_field_error + "</span>");
            erros = true;
        }

        //password and password confirm
        if (!password || password.length < 6) {
            $('.wpic_password').addClass('is-invalid').after("<span class='invalid-feedback'>" + wpic.api_key_creat_password_field_error + "</span>");
            erros = true;
        }

        if (password && password.length >= 6 && password_confirm != password) {
            $('.wpic_password_confirm').addClass('is-invalid').after("<span class='invalid-feedback'>" + wpic.api_key_creat_password_confirm_field_error + "</span>");
            erros = true;
        }

        if (!erros) {

            var old_button_text = button.val();
            button.val(wpic.api_key_create).attr('disabled', 'true');

            button.parent().find('.loader').show();

            var data = {
                action: 'wpic_create_account',
                email: email,
                password: password
            }

            $.ajax({
                url: ajaxurl,
                data: data,
                type: 'post',
                success: function (data) {
                    if (data == 'done') {
                        window.location = '?page=crush-pics&register=true';
                    } else if (data == 'exist') {
                        window.location = '?page=crush-pics&email=' + email;
                    } else {
                        $('.wpic_email').addClass('is-invalid').after("<span class='invalid-feedback'>" + wpic.api_key_create_general_error + "</span>");
                    }
                }, complete: function () {
                    button.val(old_button_text).removeAttr('disabled');
                    button.parent().find('.loader').hide();
                }
            });
        }
    });
    if ($('body').hasClass('upload-php')) {
        $('#the-list tr').each(function () {
            var row = $(this);
            var id = row.find('.wp_image_compress').attr('data-id');
            var size = 'full';
            var check_interval = setInterval(check_status, 1000, id, size, row);
            row.attr('data-interval', check_interval);
        });
    }

    $(document).on('click', '#compress_all_btn', function (e) {
        var upgrade = $('.main-card-container').attr('data-upgrade');
        if (upgrade == 'yes') {
            e.preventDefault();
            jQuery('#upgrade_error').modal('show');
        }
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

            button.closest("tr").find('.queued').css('display', 'inline-flex');
            button.closest('tr').find('.not-crushed-status').hide();
            button.closest('tr').find('.already-crushed').hide();
            button.closest('tr').find('.restored-status').hide();

            if (size == 'full') {
                $('tr.parent-details-banner-' + id).find('.wp_image_compress').hide();
                $('tr.parent-details-banner-' + id).find('.queued').css('display', 'inline-flex');
                $('tr.parent-details-banner-' + id).find('.not-crushed-status').hide();
                $('tr.parent-details-banner-' + id).find('.already-crushed').hide();
                $('tr.parent-details-banner-' + id).find('.restored-status').hide();
            }

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
                    if (data.quota_status == 'yes') {
                        $('.upgrade-plan-container').show();
                    } else {
                        $('.upgrade-plan-container').hide();
                    }
                    if (data && data.crushed_id) {
                        button.closest("tr").find('.queued').hide();
                        button.closest("tr").find('.crush-spinner').show();
                        var crushed_id = data.crushed_id;
                        button.attr('data-crushed_id', crushed_id);
                        if (size == 'full') {
                            $('tr.parent-details-banner-' + id).find('.queued').hide();
                            $('tr.parent-details-banner-' + id).find('.crush-spinner').show();
                        }
                    } else {
                        if (data && data.error) {
                            jQuery('#general-error-status-msg .modal-body').text(data.error);
                            jQuery('#general-error-status-msg').modal('show');
                        }
                        button.closest("tr").find('.queued').hide();
                        button.closest("tr").find('.crush-spinner').hide();
                        button.show();
                        if (size == 'full') {
                            $('tr.parent-details-banner-' + id).find('.queued').hide();
                            $('tr.parent-details-banner-' + id).find('.crush-spinner').hide();
                            $('tr.parent-details-banner-' + id).find('.wp_image_compress').show();
                        }
                        if (status && status == 'restored') {
                            button.closest('tr').find('.restored-status').css('display', 'inline-flex');
                            button.closest('tr').find('.not-crushed-status').hide();
                            button.closest('tr').find('.already-crushed').hide();

                            if (size == 'full') {
                                $('tr.parent-details-banner-' + id).find('.restored-status').css('display', 'inline-flex');
                                $('tr.parent-details-banner-' + id).find('.not-crushed-status').hide();
                                $('tr.parent-details-banner-' + id).find('.already-crushed').hide();

                            }
                        } else {
                            button.closest('tr').find('.restored-status').hide();
                            button.closest('tr').find('.not-crushed-status').css('display', 'inline-flex');

                            if (size == 'full') {
                                $('tr.parent-details-banner-' + id).find('.restored-status').hide();
                                $('tr.parent-details-banner-' + id).find('.not-crushed-status').css('display', 'inline-flex');
                            }
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
        button.closest("tr").find('.restore-spinner').show();
        button.closest('tr').find('.crushed-status').hide();
        button.closest('tr').find('.saved-image').hide();
        if (size == 'full') {
            $('tr.parent-details-banner-' + id).find('.wp_image_restore').hide();
            $('tr.parent-details-banner-' + id).find('.restore-spinner').show();
            $('tr.parent-details-banner-' + id).find('.crushed-status').hide();
            $('tr.parent-details-banner-' + id).find('.saved-image').hide();
        }

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
                if (size == 'full') {
                    $('tr.parent-details-banner-' + id).find('.restore-spinner').hide();
                }
                if (data.image_id && data.image_url) {
                    button.closest('tr').find('.restored-status').css('display', 'inline-flex');
                    button.closest('tr').find('.wp_image_compress').show();
                    button.closest('tr').find('.wp_image_compress').attr('data-guid', data.image_url);
                    button.closest('tr').find('.wp_image_compress').attr('data-id', data.image_id);
                    button.closest('tr').find('.wp_image_compress').attr('data-status', 'restored');
                    button.closest('tr').find('.wp_image_compress').attr('data-size', size);
                    if (size == 'full') {
                        $('tr.parent-details-banner-' + id).find('.restored-status').css('display', 'inline-flex');
                        $('tr.parent-details-banner-' + id).find('.wp_image_compress').show();
                        $('tr.parent-details-banner-' + id).find('.wp_image_compress').attr('data-guid', data.image_url);
                        $('tr.parent-details-banner-' + id).find('.wp_image_compress').attr('data-id', data.image_id);
                        $('tr.parent-details-banner-' + id).find('.wp_image_compress').attr('data-status', 'restored');
                        $('tr.parent-details-banner-' + id).find('.wp_image_compress').attr('data-size', size);
                    }

                    fill_image_details(id, 'image_id', size);
                } else {
                    button.show();
                    button.closest('tr').find('.crushed-status').css('display', 'inline-flex');
                    button.closest('tr').find('.saved-image').css('display', 'inline-flex');
                    if (size == 'full') {
                        $('tr.parent-details-banner-' + id).find('.wp_image_restore').show();
                        $('tr.parent-details-banner-' + id).find('.crushed-status').css('display', 'inline-flex');
                        $('tr.parent-details-banner-' + id).find('.saved-image').css('display', 'inline-flex');
                    }
                }

            }, complete: function () {
            }
        });
    });


    //show image details
    $(document).on('click', '.image_details', function () {
        var button = $(this);
        var id = button.attr('data-id');
        var size = button.attr('data-size');
        button.hide();
        button.closest("tr").find('.image_close').show();
        if (size == 'banner-full') {
            if (button.parents('tr.image-child').hasClass('last-child-item')) {
                button.closest('tr.parent-details-banner-item-' + id).toggleClass('last-child-padding');
            }
            button.closest('.table-image-container').find('tr.parent-details-' + id).slideDown();
        } else if (size == 'full') {
            button.closest('tbody').find('tr.parent-item-' + id).slideDown();
            button.closest('.table-image-container').find('tr.parent-details-banner-item-' + id).slideDown();
            button.closest('.table-image-container').find('.crush-image-details.parent-details-info-' + id).slideDown();
        } else {
            if (button.parents('tr.image-child').hasClass('last-child-item')) {
                button.closest('tr.last-child-item').toggleClass('last-child-padding');
            }
            button.closest('.table-image-container').find('tr.item-details-' + id).slideDown();
        }
    });

    //hide image details
    $(document).on('click', '.image_close', function () {
        var button = $(this);
        var id = button.attr('data-id');
        var size = button.attr('data-size');
        button.hide();
        if (size == 'banner-full') {
            button.closest('.table-image-container').find('tr.parent-details-' + id).slideUp(0, function () {
                if (button.parents('tr.image-child').hasClass('last-child-item')) {
                    button.closest('tr.parent-details-banner-item-' + id).toggleClass('last-child-padding');
                }
            });
        } else if (size == 'full') {
            button.closest('.table-image-container').find('tr.last-child-item.parent-item-' + id).toggleClass('last-child-padding');
            button.closest('.table-image-container').find('tr.parent-details-banner-item-' + id).toggleClass('last-child-padding');
            button.closest('.table-image-container').find('tr.parent-details-' + id).slideUp();
            button.closest('.table-image-container').find('tr.item-details-container-' + id).slideUp();
            button.closest('tbody').find('tr.parent-item-' + id).slideUp();
            button.closest('.table-image-container').find('tr.parent-details-banner-item-' + id).slideUp();
            button.closest('.table-image-container').find('.crush-image-details.parent-details-info-' + id).slideUp();
            button.closest('.table-image-container').find('.image-details-' + id).show();
            button.closest('.table-image-container').find('.image-close-' + id).hide();
        } else {

            button.closest('.table-image-container').find('tr.item-details-' + id).slideUp(0, function () {
                if (button.parents('tr.image-child').hasClass('last-child-item')) {
                    button.closest('tr.last-child-item').toggleClass('last-child-padding');
                }
            });
        }

        button.closest("tr").find('.image_details').show();

    });
    //compress all

    $(document).on('click', '.stop_crush_all', function (e) {
        e.preventDefault();
        var data = {
            action: 'wpic_image_stop_compress_all',
        };

        jQuery.ajax({
            url: ajaxurl,
            data: data,
            type: 'post',
            success: function (data) {
                if (data) {
                    window.location.replace(data);
                }

            }, complete: function () {
            }
        });


    });
    if ($('.images_table_container').hasClass('crush_all_container')) {
        $('.container-count-actions').removeClass('d-block').addClass('d-none');
        var crush_all_interval = setInterval(crush_all_check_status, 1000);
        $('.crush_all').attr('data-interval', crush_all_interval);
    } else {
        $('.check_status').each(function () {
            var row = $(this);
            var id = row.attr('data-id');
            var size = row.attr('data-size');
            var check_interval = setInterval(check_status, 1000, id, size, row);
            row.attr('data-interval', check_interval);

        });
    }

});
function compress_all() {
    var data = {
        action: 'wpic_image_compress_all',
    };

    jQuery.ajax({
        url: ajaxurl,
        data: data,
        type: 'post',
        success: function (data) {
            if (data.percent && data.next && data.total) {
                jQuery("#upload_process").html("<div style='width:" + data.percent + "';background:#40ad6e; ;height:35px;'>&nbsp;</div>");
                jQuery("#information").html("<div style='text-align:center; font-weight:bold'>" + data.percent + ' is processed ' + data.next + ' is created from ' + data.total + "</div>");
                jQuery("span.count_total").append("'" + data.percent + "'");
            }
            if (data.rest > 0) {

                compress_all();
            }
        }, complete: function () {
        }
    });
}

function update_quota_used_card() {
    var data = {
        action: 'wpic_update_quota_used_card'
    };

    jQuery.ajax({
        url: ajaxurl,
        data: data,
        success: function (data) {
            if (data) {
                jQuery('.quota-used-card .used').text(data.quota_usage + ' MB');
                jQuery('.quota-used-card .quota').text('/ ' + data.bytes + ' MB');
                jQuery('.quota-used-card .progress-bar').attr('style', 'width:' + data.precentage + '%');
                if (data.status == 'valid') {
                    jQuery('.main-card-container').attr('data-upgrade', 'no')
                    jQuery('.upgrade-plan-container').hide();
                } else if (data.status == 'upgrade') {
                    jQuery('.main-card-container').attr('data-upgrade', 'yes')
                    jQuery('.upgrade-plan-container').show();
                    jQuery('.upgrade-plan-url').attr('href', data.upgrade_url);
                }
            }
        }
    });
}


function validateEmail($email) {
    var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
    return emailReg.test($email);
}

function check_compress(crushed_id, button) {
    if (crushed_id && button) {
        var old_status = button.attr('data-status');
        var is_restored = button.closest("tr").attr('data-is_restored');
        button.closest('tr').find('.not-crushed-status').hide();
        button.closest('tr').find('.already-crushed').hide();

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
                    button.closest('tr').find('.not-crushed-status').hide();
                    button.closest('tr').find('.already-crushed').hide();

                    if (data.image_size == 'full') {
                        jQuery('tr.parent-details-banner-' + data.image_id).find('.not-crushed-status').hide();
                        jQuery('tr.parent-details-banner-' + data.image_id).find('.already-crushed').hide();

                    }
                    if (data.status == 'crushed') {
                        button.closest("tr").find('.crush-spinner').hide();
                        button.closest('tr').find('.not-crushed-status').hide();
                        button.closest('tr').find('.already-crushed').hide();

                        button.closest('tr').find('.crushed-status').css('display', 'inline-flex');
                        if (data.image_size == 'full') {
                            jQuery('tr.parent-details-banner-' + data.image_id).find('.crush-spinner').hide();
                            jQuery('tr.parent-details-banner-' + data.image_id).find('.not-crushed-status').hide();
                            jQuery('tr.parent-details-banner-' + data.image_id).find('.already-crushed').hide();
                            jQuery('tr.parent-details-banner-' + data.image_id).find('.crushed-status').css('display', 'inline-flex');
                        }
                        if (data.backup == 'yes') {
                            button.closest('tr').find('.wp_image_restore').show();
                            button.closest('tr').find('.restored-status').hide();
                            button.closest('tr').find('.wp_image_restore').attr('data-backup', data.image_backup_path);
                            button.closest('tr').find('.wp_image_restore').attr('data-guid', data.image_url);
                            button.closest('tr').find('.wp_image_restore').attr('data-id', data.image_id);
                            if (data.image_size == 'full') {
                                jQuery('tr.parent-details-banner-' + data.image_id).find('.wp_image_restore').show();
                                jQuery('tr.parent-details-banner-' + data.image_id).find('.restored-status').hide();
                                jQuery('tr.parent-details-banner-' + data.image_id).find('.wp_image_restore').attr('data-backup', data.image_backup_path);
                                jQuery('tr.parent-details-banner-' + data.image_id).find('.wp_image_restore').attr('data-guid', data.image_url);
                                jQuery('tr.parent-details-banner-' + data.image_id).find('.wp_image_restore').attr('data-id', data.image_id);
                            }
                        }
                        if (data.saved) {
                            button.closest('tr').find('.saved-image').css('display', 'inline-flex');
                            button.closest('tr').find('.saved-image').text(data.saved);
                            if (data.image_size == 'full') {
                                jQuery('tr.parent-details-banner-' + data.image_id).find('.saved-image').css('display', 'inline-flex');
                                jQuery('tr.parent-details-banner-' + data.image_id).find('.saved-image').text(data.saved);
                                if ( data.backup == 'yes' ) {
                                    jQuery('tr.parent-details-banner-' + data.image_id).find('.image-preview-link').attr({
                                        "data-before" : data.image_url,
                                        "data-after" : data.upload_dir + data.image_backup_path,
                                    });
                                    jQuery('tr.parent-details-banner-' + data.image_id).find('.image-item').hide();
                                    jQuery('tr.parent-details-banner-' + data.image_id).find('.image-preview-link').css('display', 'inline-flex');
                                }
                            }
                        }
                        update_quota_used_card();
                        var interval = button.attr('data-interval');
                        clearInterval(interval);
                    } else {
                        button.closest("tr").find('.crush-spinner').hide();
                        button.show();
                        if (data.image_size == 'full') {
                            jQuery('tr.parent-details-banner-' + data.image_id).find('.crush-spinner').hide();
                            jQuery('tr.parent-details-banner-' + data.image_id).find('.wp_image_compress').show();
                        }
//                        if ((old_status && old_status == 'restored') || is_restored == 'yes') {
//                            button.closest('tr').find('.restored-status').css('display', 'inline-flex');
//                            button.closest('tr').find('.not-crushed-status').hide();
//                            button.closest('tr').find('.already-crushed').hide();
//
//                            if (data.image_size == 'full') {
//                                jQuery('tr.parent-details-banner-' + data.image_id).find('.restored-status').css('display', 'inline-flex');
//                                jQuery('tr.parent-details-banner-' + data.image_id).find('.not-crushed-status').hide();
//                                jQuery('tr.parent-details-banner-' + data.image_id).find('.already-crushed').hide();
//                            }
//                        } else {
                        button.closest('tr').find('.restored-status').hide();
                        button.closest('tr').find('.not-crushed-status').hide();
                        button.closest('tr').find('.already-crushed').css('display', 'inline-flex');
                        if (data.image_size == 'full') {
                            jQuery('tr.parent-details-banner-' + data.image_id).find('.restored-status').hide();
                            jQuery('tr.parent-details-banner-' + data.image_id).find('.not-crushed-status').hide();
                            jQuery('tr.parent-details-banner-' + data.image_id).find('.already-crushed').css('display', 'inline-flex');

                        }
                        // }
                        var interval = button.attr('data-interval');
                        clearInterval(interval);
                        //show modal
                        // jQuery('#error-status-msg').modal('show');
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
        var old_status = row.attr('data-status');
        var is_restored = row.attr('data-is_restored');
        var check_interval = row.attr('data-interval');
        var data = {
            action: 'wpic_image_check_status',
            id: id,
            size: size
        };
        if (old_status == 'enqueued') {
            jQuery.ajax({
                url: ajaxurl,
                data: data,
                type: 'post',
                success: function (data) {
                    if (data.status) {
                        row.find('.not-crushed-status').hide();
                        row.find('.already-crushed').hide();
                        if (data.status == 'enqueued') {
                            row.attr('data-process', 'yes');
                            row.find('.crush-spinner').show();
                            row.find('.not-crushed-status').hide();
                            row.find('.already-crushed').hide();
                            row.find('.restored-status').hide();
                            row.find('.crushed-status').hide();
                            row.find('.wp_image_restore').hide();
                            row.find('.wp_image_compress').hide();
                        } else if (data.status == 'crushed') {
                            row.find('.wp_image_compress').hide();
                            row.find('.crush-spinner').hide();
                            row.find('.not-crushed-status').hide();
                            row.find('.already-crushed').hide();
                            row.find('.crushed-status').css('display', 'inline-flex');
                            if (data.backup == 'yes') {
                                row.find('.wp_image_restore').show();
                                row.find('.restored-status').hide();
                                row.find('.wp_image_restore').attr('data-backup', data.image_backup_path);
                                row.find('.wp_image_restore').attr('data-guid', data.image_url);
                                row.find('.wp_image_restore').attr('data-id', data.image_id);
                            }
                            if (data.saved) {
                                row.find('.saved-image').css('display', 'inline-flex');
                                row.find('.saved-image').text(data.saved);
                            }
                            clearInterval(check_interval);
                        } else if (data.status == 'error') {
                            row.find('.crush-spinner').hide();
                            row.find('.wp_image_compress').show();
//                            if ((old_status && old_status == 'restored') || is_restored == 'yes') {
//                                row.find('.restored-status').css('display', 'inline-flex');
//                                row.find('.not-crushed-status').hide();
//                                row.find('.already-crushed').hide();
//                            } else {
                            row.find('.restored-status').hide();
                            row.find('.not-crushed-status').hide();
                            row.find('.already-crushed').css('display', 'inline-flex');

                            // }
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
                        if (!jQuery('body').hasClass('upload-php')) {

                            fill_image_details(id, 'image_id', size);
                        }
                    } else {
                        clearInterval(check_interval);
                    }
                }, complete: function () {

                }
            });
        }
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
                    $('.parent-details-info-' + data.image_id).find('.crushed-no').html('');
                    $('.parent-details-info-' + data.image_id).find('.total-version').html('');
                    if (size == 'full') {
                        $('.parent-details-' + data.image_id).find('.crushed_size_value').text('-');
                        $('.parent-details-' + data.image_id).find('.saved_value').text('-');
                        $('.parent-details-' + data.image_id).find('.compression_type_value').text('-');
                        $('.parent-details-' + data.image_id).find('.image_history_list').html('');

                        if (data.crushed_size) {
                            $('.parent-details-' + data.image_id).find('.crushed_size_value').text(data.crushed_size);
                        }
                        if (data.saved) {
                            $('.parent-details-' + data.image_id).find('.saved_value').text(data.saved);
                        }
                        if (data.compression_type) {
                            $('.parent-details-' + data.image_id).find('.compression_type_value').text(data.compression_type);
                        }
                        if (data.history_html) {
                            $('.parent-details-' + data.image_id).find('.image_history_list').html(data.history_html);
                        }
                    } else {
                        $('.item-details-' + size + '-' + data.image_id).find('.crushed_size_value').text('-');
                        $('.item-details-' + size + '-' + data.image_id).find('.saved_value').text('-');
                        $('.item-details-' + size + '-' + data.image_id).find('.compression_type_value').text('-');
                        $('.item-details-' + size + '-' + data.image_id).find('.image_history_list').html('');
                        $('.parent-details-' + data.image_id).find('.crushed-no').html('');
                        $('.parent-details-' + data.image_id).find('.total-version').html('');
                        if (data.crushed_size) {
                            $('.item-details-' + size + '-' + data.image_id).find('.crushed_size_value').text(data.crushed_size);
                        }
                        if (data.saved) {
                            $('.item-details-' + size + '-' + data.image_id).find('.saved_value').text(data.saved);
                        }
                        if (data.compression_type) {
                            $('.item-details-' + size + '-' + data.image_id).find('.compression_type_value').text(data.compression_type);
                        }
                        if (data.history_html) {
                            $('.item-details-' + size + '-' + data.image_id).find('.image_history_list').html(data.history_html);
                        }
                    }
                    $('.parent-details-info-' + data.image_id).find('.crushed-no').html(data.crushed_no);
                    $('.parent-details-info-' + data.image_id).find('.total-version').html(data.total_size_no);

                    $('.total-images-no').html(data.total_images_no);
                    $('.crushed-images-no').html(data.crushed_images_no);
                    $('.un-crushed-images-no').html(data.un_crushed_images_no);
                    if (parseInt(data.un_crushed_images_no) > 0) {
                        $('.end_compress_container').hide(0, function () {
                            if (!$('.images_table_container').hasClass('crush_all_container')) {
                                $('.container-count-actions').removeClass('d-none').addClass('d-block');
                            }
                        });
                    } else {
                        $('.end_compress_container').show(0, function () {
                            $('.container-count-actions').removeClass('d-block').addClass('d-none');
                        });
                    }

                    $('.crush-all-images-no').html(data.un_crushed_images_no);

                }

            }, complete: function () {

            }
        });

    }

}

function crush_all_check_status() {
    var status;
    var data = {
        action: 'wpic_image_check_crush_all'
    };
    jQuery.ajax({
        url: ajaxurl,
        data: data,
        type: 'post',
        success: function (data) {
            if (data.status) {

                if (data.quota_status == 'yes') {
                    jQuery('.upgrade-plan-container').show();
                }
                status = data.status;
                jQuery('.crush-progressing-bar').show();
                jQuery('.container-count-actions').removeClass('d-block').addClass('d-none');

                if (data.percent && data.total && data.iteration) {
                    jQuery('.container-count-actions').removeClass('d-block').addClass('d-none');

                    jQuery("#upload_process").html("<div style=\"width:" + data.percent + ";background:#6371c7; ;height:35px;\">&nbsp;</div>");
                    jQuery("#information").html("<div><span class='font-weight-bold'>" + data.iteration + "</span> / " + data.total + "</div>");
                }
                if (data.status == 'end') {
                    if (data.un_crushed_images_no > 0) {
                        jQuery('.crush-progressing-bar,.stop_compress_container,.end_compress_container').hide(0, function () {
                            jQuery('.container-count-actions .crush-all-images-no').text(data.un_crushed_images_no);
                            jQuery('.container-count-actions').removeClass('d-none').addClass('d-block');
                        });

                    } else {
                        jQuery('.container-count-actions').removeClass('d-block').addClass('d-none');
                        jQuery('.crush-progressing-bar,.stop_compress_container').hide(0, function () {
                            jQuery('.end_compress_container').show();
                        });
                    }

                    var crush_all_interval = jQuery('.crush_all').attr('data-interval');
                    clearInterval(crush_all_interval);
                } else if (data.status == 'stop') {
                    jQuery('.container-count-actions').removeClass('d-block').addClass('d-none');
                    jQuery('.crush-progressing-bar,.end_compress_container').hide(0, function () {
                        jQuery('.stop_compress_container').show();
                    });
                    var crush_all_interval = jQuery('.crush_all').attr('data-interval');
                    clearInterval(crush_all_interval);
                }

                update_quota_used_card();
            } else {
                var crush_all_interval = jQuery('.crush_all').attr('data-interval');
                clearInterval(crush_all_interval);
            }

        }, complete: function () {
            if (status == 'end') {
                reload_image_table();
            }

        }
    });


}

function reload_image_table() {
    var data = {
        action: 'wpic_reload_image_table'
    };
    jQuery.ajax({
        url: ajaxurl,
        data: data,
        type: 'post',
        success: function (data) {
            if (data) {
                jQuery('.images_table_container').html('');
                jQuery('.images_table_container').html(data);
                jQuery('.images_table_container').toggleClass('crush_all_container');
                jQuery( '.overlay-txt' ).hide();
                update_quota_used_card();
//                jQuery('.check_status').each(function () {
//                    var row = jQuery(this);
//                    var id = row.attr('data-id');
//                    var size = row.attr('data-size');
//                    var check_interval = setInterval(check_status, 1000, id, size, row);
//                    row.attr('data-interval', check_interval);
//                });
            }

        }, complete: function () {
        }
    });
}
