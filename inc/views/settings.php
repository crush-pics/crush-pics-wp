<div class="m-md-4 m-2 wpic_page_containter">
    <div class="container p-2 bg-white">
        <div class="container settings <?php if ($api_key_added): ?>settings-validator<?php endif; ?> ">
            <?php if ($api_key_added): ?>
                <div class="mr-auto ml-auto text-center pb-3">
                    <img class="mb-4" src="<?php echo WPIC_URL . 'assets/img/crush-pics.svg'; ?>" />
                    <h1 class="font-weight-bold mb-2"><?php _e('Welcome to Crush.pics!', 'wp-image-compression'); ?></h1>
                    <p class="welcome-desc"><?php _e('Choose your base settings below and then click the Save button to go to your dashboard.', 'wp-image-compression'); ?></p>
                </div>
            <?php else: ?>
                <div class="border-bottom settings-page-title">
                    <h2 class="mb-0 custom-size">
                        <span class="font-weight-bold"><?php _e('Crush.pics', 'wp-image-compression'); ?>:</span>
                        <span class="text-muted font-weight-normal"> <?php _e('Application Settings', 'wp-image-compression'); ?></span>
                        </h1>
                </div>
            <?php endif; ?>


            <form method="post" action="options.php">
                <?php settings_fields('wpic-plugin-settings-group'); ?>
                <?php do_settings_sections('wpic-plugin-settings-group'); ?>
                <?php if (!$api_key_added): ?>
                    <div class="row border-bottom py-4">

                        <div class="col-md-4">

                            <h2 class="font-weight-bold mb-2"><?php _e('API Key', 'wp-image-compression'); ?></h2>
                            <p class="mb-md-0 display-h3 text-muted">
                                <?php _e('Something about API Key.', 'wp-image-compression'); ?>
                            </p>

                        </div>

                        <div class="col-md-8">
                            <div class="form-group mb-0">
                                <textarea class="form-control api_key_value" id="dashboardtextarea" rows="5"><?php echo $api_key; ?></textarea>

                                <input type="button" class="api_key_revalidate btn btn-primary mt-3 d-block ml-auto" value="<?php _e('Verify key', 'wp-image-compression'); ?>"/>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="row border-bottom  setting-custom-padding">
                    <div class="col-xl-4">

                        <h2 class="font-weight-bold mb-2">
                            <?php _e('Compression Type', 'wp-image-compression'); ?>
                            <span data-toggle="tooltip" data-placement="right" data-html="true" title="<div class='text-left py-3'><div class='title'><b>Balanced</b> (Recommended)</div> <div class='body'>Significant reduction in size, with minimal sacrifice to image quality.</div><hr><div class='title'><b>Conservative</b></div><div class='body'>Moderate size saving with zero sacrifice to image quality.</div><hr><div class='title'><b>Custom</b></div><div class='body'>Choose custom compression levels for each type of image.</div></div>" class="field-tooltip">
                                <img src="<?php echo WPIC_URL . 'assets/img/info-icon-01.png'; ?>"/>
                            </span>
                        </h2>

                        <p class="mb-xl-0 display-h3 text-muted">
                            <?php _e('All new images will be compressed with selected compression.', 'wp-image-compression'); ?>
                        </p>

                    </div>
                    <div class="col-xl-8">
                        <div class="row compression_type_row">
                            <div class="col-md-4 compression_type_col">
                                <div class="border <?php if ($compression_type == 'balanced'): ?>compression_type_selected<?php endif; ?> align-items-center d-flex rounded custom-box  mb-2">
                                    <div class="custom-control custom-radio">
                                        <input <?php checked('balanced', $compression_type); ?> id="ct_balanced" class="compression_type_radio custom-control-input" name="compression_type" type="radio" value="balanced"/>
                                        <label class="custom-control-label" for="ct_balanced">
                                            <span class="ml-2"><?php _e('Balanced', 'wp-image-compression'); ?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 compression_type_col">
                                <div class="border <?php if ($compression_type == 'lossless'): ?>compression_type_selected<?php endif; ?> align-items-center d-flex rounded custom-box  mb-2">
                                    <div class="custom-control custom-radio">
                                        <input <?php checked('lossless', $compression_type); ?> id="ct_conservative" class="compression_type_radio custom-control-input" name="compression_type" type="radio" value="lossless"/>

                                        <label class="custom-control-label" for="ct_conservative">
                                            <span class="ml-2"><?php _e('Conservative', 'wp-image-compression'); ?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 compression_type_col">

                                <div class="border <?php if ($compression_type == 'lossy'): ?>compression_type_selected<?php endif; ?> align-items-center d-flex rounded custom-box  mb-2 flex-md-column">
                                    <div class="justify-content-around w-100 text-center d-md-flex">

                                        <div class="custom-control custom-radio">
                                            <input <?php checked('lossy', $compression_type); ?> id="ct_custom" name="compression_type"  class="compression_type_radio custom-control-input" type="radio" value="lossy"/>

                                            <label class="custom-control-label" for="ct_custom">
                                                <span class="ml-2"><?php _e('Custom', 'wp-image-compression'); ?></span>
                                            </label>
                                        </div>


                                        <!-- Button trigger modal -->
                                        <button type="button" class="bg-white border-0 text-uppercase font-weight-bold custom-modal" data-toggle="modal" data-target="#custom-sizes-details">
                                            <?php _e('Edit', 'wp-image-compression'); ?>
                                        </button>


                                    </div>

                                </div>
                                <?php if ($compression_custom_size_details): ?>
                                    <div class="d-flex flex-wrap justify-content-between pt-2 details">
                                        <input value="<?php echo isset($compression_custom_size_details['jpeg']) ? $compression_custom_size_details['jpeg'] : ''; ?>" type="hidden" name="compression_type_custom_jpeg" class="compression_type_custom_jpeg"/>
                                        <input value="<?php echo isset($compression_custom_size_details['png']) ? $compression_custom_size_details['png'] : ''; ?>" type="hidden" name="compression_type_custom_png" class="compression_type_custom_png"/>
                                        <input value="<?php echo isset($compression_custom_size_details['gif']) ? $compression_custom_size_details['gif'] : ''; ?>" type="hidden" name="compression_type_custom_gif" class="compression_type_custom_gif"/>
                                        <?php
                                        foreach ($compression_custom_size_details as $type => $quality):
                                            if (!$quality)
                                                $quality = '-';
                                            else
                                                $quality = $quality . '%';
                                            ?>
                                            <span class="custom_quality_<?php echo $type; ?>_divider"><?php echo '<strong class="text-uppercase">' . $type . ': </strong> <span class="text-muted custom_quality_' . $type . '" >' . $quality; ?></span></span>
                                            <?php if ($type != 'gif'): ?>
                                                <span class="vertical-divider"></span>
                                                <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row my-2 border-bottom setting-custom-padding">

                    <div class="col-md-4">

                        <h2 class="font-weight-bold mb-2"><?php _e('Automation', 'wp-image-compression'); ?></h2>
                        <p class="mb-md-0 display-h3 text-muted">
                            <?php _e('Automatically compress every new image you upload to Wordpress.', 'wp-image-compression'); ?>
                        </p>

                    </div>

                    <div class="col-md-8">

                        <label class="switch">
                            <input <?php checked('yes', $compression_auto); ?> name="compression_auto" type="checkbox" value="yes">
                            <span class="slider round"></span>
                        </label>
                    </div>

                </div>

                <div class="row my-2 border-bottom  setting-custom-padding">

                    <div class="col-md-4">

                        <h2 class="font-weight-bold mb-2"><?php _e('Backup', 'wp-image-compression'); ?></h2>
                        <p class="mb-md-0 display-h3 text-muted">
                            <?php _e('Keep your original images in a separate folder before optimization process.', 'wp-image-compression'); ?>
                        </p>

                    </div>

                    <div class="col-md-8">

                        <label class="switch">
                            <input  <?php checked('yes', $compression_backup); ?> name="compression_backup" type="checkbox" value="yes">
                            <span class="slider round"></span>
                        </label>
                    </div>

                </div>


                <div class="row my-2 border-bottom  setting-custom-padding">

                    <div class="col-md-4">

                        <h2 class="font-weight-bold mb-2">
                            <?php _e('Keep EXIF data', 'wp-image-compression'); ?>
                            <span data-toggle="tooltip" data-placement="right" data-html="true" title="<div class='text-left py-3 body'>If you are a photographer, you may be interested in this option if you are displaying info like the model of your camera on your pages. Also, keeping EXIF data can fix some colormetric problems.</div>" class="field-tooltip">
                                <img src="<?php echo WPIC_URL . 'assets/img/info-icon-01.png'; ?>"/>
                            </span>
                        </h2>
                        <p class="mb-md-0 display-h3 text-muted">
                            <?php _e('EXIF data is information stored in your pictures. E.g. shutter speed, exposure compensation, ISO.', 'wp-image-compression'); ?>
                        </p>

                    </div>

                    <div class="col-md-8">

                        <label class="switch">
                            <input <?php checked('yes', $compression_exif); ?> name="compression_exif" type="checkbox" value="yes">
                            <span class="slider round"></span>
                        </label>
                    </div>

                </div>

                <div class="row my-2 border-bottom  setting-custom-padding">

                    <div class="col-md-4">

                        <h2 class="font-weight-bold mb-2">
                            <?php _e('Files included in Compression', 'wp-image-compression'); ?>
                            <span data-toggle="tooltip" data-placement="right" data-html="true"  title="<div class='text-left py-3 body'>The original size is automatically optimized by Crush.pics. Remember each additional image size will affect your quota usage!</div>" class="field-tooltip">
                                <img src="<?php echo WPIC_URL . 'assets/img/info-icon-01.png'; ?>"/>
                            </span>
                        </h2>
                        <p class="mb-md-0 display-h3 text-muted">
                            <?php _e('Choose what size to optimize different image sizes created by Wordpress.', 'wp-image-compression'); ?>
                        </p>

                    </div>

                    <div class="col-md-8">
                        <?php foreach ($image_sizes as $size => $details): ?>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" <?php if (in_array($size, $compression_sizes)): ?>checked=""<?php endif; ?> class="custom-control-input" id="compression_size_<?php echo $size; ?>" name="compression_sizes[]" value="<?php echo $size; ?>">
                                <label class="custom-control-label text-muted" for="compression_size_<?php echo $size; ?>">
                                    <?php echo $size; ?>:
                                    <span class="text-muted"><?php echo $details['width'] . 'x' . $details['height']; ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>

                    </div>

                </div>
                <div class=" ml-auto d-flex flex-wrap <?php if (!$api_key_added): ?>justify-content-end<?php else: ?>justify-content-center<?php endif; ?> setting-actions">
                    <?php if (!$api_key_added): ?>
                        <a href="?page=crush-pics" class="btn btn-outline-secondary w-25 mr-2"><?php _e('Close', 'wp-image-compression'); ?></a>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary w-50 px-3"><?php _e('Save', 'wp-image-compression'); ?></button>
                </div>
                <?php // if ($api_key_added): ?>
                <input type="hidden" name="api_key" value="true"/>
                <?php // endif; ?>

            </form>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="custom-sizes-details" tabindex="-1" role="dialog" aria-labelledby="EditMoadlTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="EditMoadlTitle"><?php _e('Choose Quality', 'wp-image-compression'); ?></h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body body">
                <div class="jpeg">
                    <span class="d-block mb-3 font-weight-bold"><?php _e('JPEG Quality', 'wp-image-compression'); ?> <span class="value font-weight-bold"><?php echo $compression_type_custom_jpeg ? $compression_type_custom_jpeg . '%' : '0%'; ?></span></span>
                    <div class="jpeg_quality"></div>
                    <div class="d-flex">
                        <span class="left"><?php _e('Smaller file size', 'wp-image-compression'); ?></span>
                        <span class="right"><?php _e('Higher image quality', 'wp-image-compression'); ?></span>
                    </div>
                </div>
                <div class="png">
                    <span class="d-block mb-3 font-weight-bold"><?php _e('PNG Quality', 'wp-image-compression'); ?> <span class="value font-weight-bold"><?php echo $compression_type_custom_png ? $compression_type_custom_png . '%' : '0%'; ?></span></span>
                    <div class="png_quality"></div>
                    <div class="d-flex">
                        <span class="left"><?php _e('Smaller file size', 'wp-image-compression'); ?></span>
                        <span class="right"><?php _e('Higher image quality', 'wp-image-compression'); ?></span>
                    </div>
                </div>
                <div class="gif">
                    <span class="d-block mb-3 font-weight-bold"><?php _e('GIF Quality', 'wp-image-compression'); ?> <span class="value font-weight-bold"><?php echo $compression_type_custom_gif ? $compression_type_custom_gif . '%' : '0%'; ?></span></span>
                    <div class="gif_quality"></div>
                    <div class="d-flex">
                        <span class="left"><?php _e('Smaller file size', 'wp-image-compression'); ?></span>
                        <span class="right"><?php _e('Higher image quality', 'wp-image-compression'); ?></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php _e('Close', 'wp-image-compression'); ?></button>
                <button type="button" class="btn btn-primary compression_quality_save"><?php _e('Save changes', 'wp-image-compression'); ?></button>
            </div>
        </div>
    </div>
</div>
