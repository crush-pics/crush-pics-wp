
<div class="container py-5 wpic_page_containter">


    <!-- Start list -->

    <ul class="list-group list-group-horizontal align-items-baseline page-actions">

        <li>

            <span class="text-muted mb-0 display-3 pr-3"><?php _e('Summary', 'wp-image-compression'); ?></span>

        </li>

        <li>

            <button class="small border-right px-3 text-uppercase font-weight-bold border-left upgrade-plan-btn" type="button">

                <?php _e('Upgrade Plan', 'wp-image-compression'); ?>

            </button>

        </li>

        <li>

            <a href="<?php echo admin_url('admin.php?page=crush-pics-settings'); ?>" class="small border-right-0 px-3 text-uppercase font-weight-bold">

                <?php _e('Settings', 'wp-image-compression'); ?>

            </a>

        </li>


    </ul>


    <!-- Start blocks -->
    <?php
    $show_upgrade = 'display:none';
    if ($upgrade_plan == 'yes')
        $show_upgrade = '';
    ?>

    <div class="row custom-cards main-card-container" data-upgrade = <?php echo $upgrade_plan; ?>>

        <div class="col-lg-8  ">
            <div class="row no-gutters">
                <div class="col-md-6 card mw-100 border-bottom-0 border-right py-2 py-lg-5 quota-plan-container" >


                    <div class="quota-used-card card-body d-flex align-items-center justify-content-center flex-column">

                        <section class="text-center">

                            <div class="mb-2 d-flex flex-wrap align-items-center justify-content-center">

                                <span class="custom-font used"><?php echo $quota_usage; ?> MB</span>

                                <span class="pl-1 text-muted custom-size quota">/ <?php echo $bytes; ?> MB</span>

                            </div>

                            <span class="text-uppercase h5 text-muted font-weight-bold"><?php _e('Quota used', 'wp-image-compression'); ?></span>

                            <div class="progress progress-thin mt-2">

                                <div class="progress-bar" role="progressbar" style="width: <?php echo $quota_usage_precentage; ?>%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>

                            </div>

                        </section>

                    </div>


                </div>

                <div class="col-md-6 card border-bottom-0 border-left-0 border-top border-right py-lg-5 py-2 mw-100 quota-plan-container" >


                    <div class="card-body d-flex align-items-center justify-content-center flex-column">

                        <section class="text-center">

                            <div class="mb-2 d-flex flex-wrap justify-content-center">

                                <span class="custom-font"><?php echo $compression_type; ?></span>

                            </div>

                            <span class="text-uppercase h5 text-muted font-weight-bold"><?php _e('Compression type', 'wp-image-compression'); ?></span>


                        </section>

                    </div>


                </div>

                <div class="d-flex justify-content-center w-100 border-top bg-white border flex-wrap quota-plan-container" >


                    <ul class="list-group list-group-horizontal justify-content-center py-2 my-1">

                        <li class="mb-0">

                            <span class="mb-0 border-right px-3 font-weight-bold h5">

                                <span class="text-muted"> <?php _e('CURRENT PLAN', 'wp-image-compression'); ?></span>

                                <span class="text-uppercase">: <?php echo $plan_name; ?></span>

                            </span>

                        </li>

                        <li class="mb-0">

                            <span class="mb-0 border-right px-3 font-weight-bold h5">

                                <span class="text-muted"> <?php _e('RENEWS IN', 'wp-image-compression'); ?></span>

                                <span class="text-uppercase">: <?php echo $next_charge_days; ?></span>

                            </span>

                        </li>

                        <li class="mb-0">

                            <span class="mb-0 px-3 font-weight-bold h5 d-flex align-items-center">

                                <span class="text-muted mr-3"> <?php _e('AUTOMATIC', 'wp-image-compression'); ?> </span>

                                <!-- Default switch -->

                                <div class="custom-control custom-switch">

                                    <label class="switch">

                                        <input  <?php checked('yes', $compression_auto); ?>  name="compression_auto" type="checkbox" value="yes" id="change_compression_auto">

                                        <span class="slider round"></span>

                                    </label>

                                </div>

                            </span>

                        </li>

                    </ul>

                </div>
                <div class="col-md-12 card mw-100 border-right py-2 py-lg-5 upgrade-plan-container" style="<?php echo $show_upgrade; ?>">
                    <div class="quota-used-card card-body d-flex align-items-center justify-content-center flex-column">
                        <section class="text-center ">
                            <span class="h2 text-muted"><?php _e('Your quota has been exhausted', 'wp-image-compression'); ?></span>
                            <button class="btn btn-primary btn-md mt-2 upgrade-plan-url upgrade-plan-btn" type="button"><?php _e('Upgrade Plan', 'wp-image-compression'); ?></button>
                        </section>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 align-self-stretch d-flex flex-wrap">

            <div class="card mw-100 w-100">

                <div class="card-body d-flex align-items-center justify-content-center flex-column">
                    <section class="text-center crush_all w-100">
                        <?php
                        if ($crush_all == 'yes') {
                            ?>
                            <div class="crush-progressing-bar w-100">
                                <div class="info-container d-flex mb-2 flex-wrap justify-content-between">
                                    <div class="text-muted"><?php _e('Compressing', 'wp-image-compression'); ?></div>
                                    <div id="information" ></div>
                                </div>
                                <div class=" ui-progressbar ui-widget ui-widget-content ui-corner-all w-100" id="upload_process" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                </div>
                                <a class="stop_crush_all btn btn btn-primary btn-md mt-3 py-1 d-block w-50 mx-auto" href="#"><?php _e('Stop', 'wp-image-compression'); ?></a>
                            </div>
                            <div class="end_compress_container" style="display:none">
                                <img src="<?php echo WPIC_URL . 'assets/img/img-all-compressed.svg'; ?>"/>
                                <p class="custom-font"><?php _e('All images compressed', 'wp-image-compression'); ?>!</p>
                                <p class=""><?php echo __('Well done', 'wp-image-compression') . '-' . __('your site has been fully optimized with Crush.pics', 'wp-image-compression'); ?>!</p>
                            </div>
                            <div class="stop_compress_container" style="display:none">
                                <p class="custom-font"><?php _e('Sorry something went wrong, please try again later.', 'wp-image-compression'); ?>!</p>
                            </div>
                            <div class="container-count-actions d-none">
                                <div class="mb-2  d-flex flex-wrap justify-content-center all-images-count">
                                    <span class="custom-font crush-all-images-no"><?php echo number_format($images_count - $crushed_images_count); ?></span>
                                </div>
                                <span class="text-uppercase h5 text-muted d-block justify-content-center crush-all-text" ><?php _e('IMAGES READY FOR COMPRESSION', 'wp-image-compression'); ?></span>
                                <a href="<?php echo add_query_arg(array('process' => 'crush_all')); ?>" class="btn btn-primary btn-md mt-2" id="compress_all_btn"><?php _e('Crush all images', 'wp-image-compression'); ?></a>
                            </div>
                        <?php } else { ?>
                            <?php if ($images_count - $crushed_images_count > 0) { ?>
                                <div class="container-count-actions d-block">
                                    <div class="mb-2 d-flex flex-wrap justify-content-center all-images-count">
                                        <span class="custom-font crush-all-images-no"><?php echo number_format($images_count - $crushed_images_count); ?></span>
                                    </div>
                                    <span class="text-uppercase h5 text-muted d-block justify-content-center crush-all-text"><?php _e('IMAGES READY FOR COMPRESSION', 'wp-image-compression'); ?></span>
                                    <a href="<?php echo add_query_arg(array('process' => 'crush_all')); ?>" class="btn btn-primary btn-md mt-2" id="compress_all_btn"><?php _e('Crush all images', 'wp-image-compression'); ?></a>
                                </div>
                                <div class="end_compress_container" style="display: none;">
                                    <img src="<?php echo WPIC_URL . 'assets/img/img-all-compressed.svg'; ?>"/>
                                    <p class="custom-font"><?php _e('All images compressed', 'wp-image-compression'); ?>!</p>
                                    <p class=""><?php echo __('Well done', 'wp-image-compression') . '-' . __('your site has been fully optimized with Crush.pics', 'wp-image-compression'); ?>!</p>
                                </div>
                            <?php } elseif ($images_count - $crushed_images_count <= 0 && $media_count != 0) { ?>
                                <div class="end_compress_container">
                                    <img src="<?php echo WPIC_URL . 'assets/img/img-all-compressed.svg'; ?>"/>
                                    <p class="custom-font"><?php _e('All images compressed', 'wp-image-compression'); ?>!</p>
                                    <p class=""><?php echo __('Well done', 'wp-image-compression') . '-' . __('your site has been fully optimized with Crush.pics', 'wp-image-compression'); ?>!</p>
                                </div>
                                <div class="container-count-actions d-none">
                                    <div class="mb-2 d-block flex-wrap justify-content-center all-images-count">
                                        <span class="custom-font crush-all-images-no"><?php echo number_format($images_count - $crushed_images_count); ?></span>
                                    </div>
                                    <span class="text-uppercase h5 text-muted d-flex justify-content-center crush-all-text" ><?php _e('IMAGES READY FOR COMPRESSION', 'wp-image-compression'); ?></span>
                                    <a href="<?php echo add_query_arg(array('process' => 'crush_all')); ?>" class="btn btn-primary btn-md mt-2" id="compress_all_btn"><?php _e('Crush all images', 'wp-image-compression'); ?></a>
                                </div>
                            <?php } else { ?>
                                <span class=" h5 text-muted d-block crush-all-text"><?php _e('No images available for bulk compression', 'wp-image-compression'); ?></span>
                            <?php } ?>
                        <?php } ?>
                    </section>

                </div>

            </div>

        </div>


    </div>

    <!-- Start images -->
    <?php $crush_all_class = $crush_all == 'yes' ? ' crush_all_container' : ''; ?>

    <section class="py-4 images_table_container">

        <?php include WPIC_PATH . 'inc/views/image-list-template.php'; ?>


    </section>


</div>



<!-- error webhook -->

<!--<div class="modal fade" id="error-status-msg" tabindex="-1" role="dialog" aria-labelledby="EditMoadlTitle" aria-hidden="true">

    <div class="modal-dialog" role="document">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title" id="EditMoadlTitle"><?php //_e('Error', 'wp-image-compression');   ?></h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                    <span aria-hidden="true">&times;</span>

                </button>

            </div>

            <div class="modal-body">

<?php //_e("Image can't compress, please try again later", 'wp-image-compression'); ?>

            </div>


        </div>

    </div>

</div>-->


<!-- general error modal -->

<div class="modal fade" id="general-error-status-msg" tabindex="-1" role="dialog" aria-labelledby="EditMoadlTitle" aria-hidden="true">

    <div class="modal-dialog" role="document">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title" id="EditMoadlTitle"><?php _e('Error', 'wp-image-compression'); ?></h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                    <span aria-hidden="true">&times;</span>

                </button>

            </div>

            <div class="modal-body">


            </div>


        </div>

    </div>

</div>

<!-- upgrade error webhook -->

<div class="modal fade" id="upgrade_error" tabindex="-1" role="dialog" aria-labelledby="EditMoadlTitle" aria-hidden="true">

    <div class="modal-dialog" role="document">

        <div class="modal-content">

            <div class="modal-header">

                <h2 class="modal-title" id="EditMoadlTitle"><?php _e('Out of Quota', 'wp-image-compression'); ?></h2>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                    <span aria-hidden="true">&times;</span>

                </button>

            </div>

            <div class="modal-body">
                <p><?php _e("Your quota for this month has run out. You’ll need to upgrade your plan to continue optimizing images.", 'wp-image-compression'); ?></p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php _e('Cancel', 'wp-image-compression'); ?></button>
                <button type="button" class="btn btn-primary upgrade-plan-btn" data-dismiss="modal"><?php _e('Explore other plans', 'wp-image-compression'); ?></button>
            </div>

        </div>

    </div>

</div>

<!-- Redirect modal -->

<div class="modal fade" id="redirect-modal" tabindex="-1" role="dialog" aria-labelledby="EditMoadlTitle" aria-hidden="true">

    <div class="modal-dialog" role="document">

        <div class="modal-content">

            <div class="modal-header">

                <h2 class="modal-title" id="EditMoadlTitle"><?php _e('Upgrade Plan', 'wp-image-compression'); ?></h2>

                <button type="button" class="close close-redirect-modal" data-dismiss="modal" aria-label="Close">

                    <span aria-hidden="true">&times;</span>

                </button>

            </div>

            <div class="modal-body">
                <img src="<?php echo WPIC_URL . 'assets/img/icn-wordpress.svg'; ?>" /><img class="ml-2" src="<?php echo WPIC_URL . 'assets/img/icn-redirect.svg'; ?>" /><img class="ml-2" src="<?php echo WPIC_URL . 'assets/img/icn-crushlogo.svg'; ?>" />
                <p class="mt-4"><?php _e("Redirecting you to our secure ", 'wp-image-compression'); ?><strong class="text-dark"><?php _e("Crush.pics Web Portal", 'wp-image-compression'); ?></strong><?php _e(", where all plan management is handled.", 'wp-image-compression'); ?></p>
                <p><?php _e("Click ", 'wp-image-compression'); ?><strong class="text-dark"><?php _e("Continue to Web Portal", 'wp-image-compression'); ?></strong><?php _e(" below, then sign in with the email address and password you created when registering this plugin.", 'wp-image-compression'); ?></p>
                <p class="text-muted"><?php _e("If you can’t remember your password - you’ll be able reset it on the next screen.", 'wp-image-compression'); ?></p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-redirect-modal" data-dismiss="modal"><?php _e('Close', 'wp-image-compression'); ?></button>
                <button type="button" class="btn btn-primary" onclick="window.open('<?php echo $crush_webapp_url; ?>/plans/');"><?php _e('Continue to Web Portal', 'wp-image-compression'); ?></button>
            </div>

        </div>

    </div>

</div>
