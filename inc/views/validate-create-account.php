<?php
if (!empty($_GET['email'])) {
    $class = "validation-pop-email";
} else {
    $class = '';
}
?>
<div class="wpic_background_container">
    <div class="wpic_welcome_container wpic_page_containter" style="<?php if ( $is_register_done || $is_email_exist ): ?>display: none;<?php endif; ?>">
        <div class="wpic_padding_containter">
            <div class="container-fluid ml-md-n2">
                <div class="row justify-content-center">
                    <div class="col-md-6 bg-white p-3 p-md-5 py-5">
                        <div class="px-3 px-md-5 welcome-text">
                            <img class="mb-5 ml-auto mr-auto d-block pb-3" src="<?php echo WPIC_URL . 'assets/img/crush-pics.svg'; ?>" />
                            <h1 class="wpic_greetings"><?php _e( '👋  Greetings from the team at Crush.pics!', 'wp-image-compression' ); ?></h1>
                            <p><strong class="text-dark"><?php _e( "We're on a mission ", 'wp-image-compression' ); ?></strong><?php _e( "to make the web a faster place and we're excited for you to join us!", 'wp-image-compression' ); ?></p>
                            <p><strong class="text-dark"><?php _e( 'So far ', 'wp-image-compression' ); ?></strong><?php _e( 'we’ve optimized over ', 'wp-image-compression' ); ?><strong class="text-dark"><?php _e( '100 million ', 'wp-image-compression' ); ?></strong><?php _e( 'images for thousands of businesses worldwide. We be hope you’ll trust us with some of yours next.', 'wp-image-compression' ); ?></p>
                            <p><?php _e( 'Thanks again for choosing Crush.pics — we really appreciate your business.', 'wp-image-compression' ); ?></p>
                            <p><?php _e( 'Click ', 'wp-image-compression' ); ?><strong class="text-dark"><?php _e( 'Let’s go! ', 'wp-image-compression' ); ?></strong><?php _e( 'to start our simple two-step setup wizard.', 'wp-image-compression' ); ?></p>
                            <a href="javascript:void(0)" class="btn btn-primary w-100 wpic_let_s_go"><?php _e( 'Let’s go!', 'wp-image-compression' ); ?></a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <div class="wpic_api_key_validation_container wpic_page_containter <?php echo $class; ?>" style="<?php if (!$is_register_done && !$is_email_exist): ?>display: none;<?php endif; ?>">
        <div class="wpic_padding_containter">
            <div class="container-fluid ml-md-n2">
                <div class="row justify-content-center">
                    <div class="col-md-6 bg-white validate-custom-screen">
                        <div class="">
                            <img class="ml-auto mr-auto d-block" src="<?php echo WPIC_URL . 'assets/img/crush-pics.svg'; ?>" />
                            <p class="validate-form-text">
                                <?php if ($is_email_exist): ?>
                                    <?php _e('The email address', 'wp-image-compression'); ?><strong class="text-dark"> “<?php echo $is_email_exist; ?>” </strong><?php _e('is already associated with a Crush.pics account.', 'wp-image-compression'); ?>
                                <?php elseif ($is_register_done): ?>
                                    <strong class="text-dark"><?php _e('Check your inbox', 'wp-image-compression'); ?> - </strong><?php _e('We just sent you an email with your account details and API Key. Follow the instructions to get started.', 'wp-image-compression'); ?>
                                <?php else: ?>
                                    <strong class="text-dark"><?php _e('Let’s validate your API Key - ', 'wp-image-compression'); ?></strong><?php _e("Copy & paste it in the field below  then click the Validate button to log into your Crush.pics account.", 'wp-image-compression'); ?>
                                <?php endif; ?>
                            </p>

                            <form>

                                <div class="form-row">
                                    <label for="wpic_api_key"><?php _e('API Key', 'wp-image-compression'); ?></label>
                                    <input type="text" class="form-control wpic_api_key" id="wpic_api_key"/>                                
                                </div>

                                <div class="position-relative validate-btn-container">
                                    <span class="loader" style="display: none;"></span>
                                    <span class="api-key-valid" style="display: none;">&#10003;</span>
                                    <input type="button" class="btn btn-primary w-100 wpic_api_key_submit" value="<?php _e('Validate', 'wp-image-compression'); ?>"/>
                                </div>
                                <?php if ($is_email_exist): ?>
                                    <p class="m-3 my-4 text-center">
                                        <span class="ml-n4 mb-5 border border-secondary position-absolute pl-3 mt-2"></span>
                                        <span class="mx-4 text-muted"><?php _e('Dont’ know your API Key?', 'wp-image-compression'); ?></span>
                                        <span class="mr-n4 mb-5 border border-secondary position-absolute pr-3 mt-2"></span>
                                    </p>
                                    <a href="<?php echo $crush_webapp_url; ?>" target="_blank" class="btn btn-outline-primary w-100"> <?php _e('Login to Crush.pics to retrieve API key', 'wp-image-compression'); ?> </a>                            
                                <?php endif; ?>
                                <a href="javascript:void(0)" class="d-block text-center font-weight-bold wpic_api_key_create"><?php _e('Back to "Create Account"', 'wp-image-compression'); ?></a>

                            </form>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="wpic_api_key_create_container wpic_page_containter" style="display: none;">
        <div class="wpic_padding_containter">
            <div class="container-fluid ml-md-n2">
                <div class="row justify-content-center">
                    <div class="col-md-6 bg-white p-3 p-md-5 py-5">
                        <div class="px-3 px-md-5">
                            <img class="mb-5 ml-auto mr-auto d-block pb-3" src="<?php echo WPIC_URL . 'assets/img/crush-pics.svg'; ?>" />
                            <p class=" welcome-form-text h2 font-weight-normal custom-line-height">
                                <strong class="text-dark"><?php _e('Welcome to Crush.pics! ', 'wp-image-compression'); ?></strong><?php _e("Please enter your email address and create a password to open your Crush.pics account.", 'wp-image-compression'); ?>
                            </p>

                            <form class="mt-5">
                                <div class="form-row mx-0 my-3">
                                    <label for="wpic_email">
                                        <?php _e('Email Address', 'wp-image-compression'); ?>
                                    </label>
                                    <span data-toggle="tooltip" data-placement="bottom" data-html="true" title="" class="text-muted ml-2 wpic_email_explanation" data-original-title="<div class='text-left py-3'><div class='title wpic_font_size_0_875rem'><b>Why do I need this?</b></div> <div class='body'>We found that enabling direct access to our compression engine ensures the most seamless experience possible, and allows easy access to additional compression quota if you need it</div><hr class='wpic_email_explanation_line'><div class='body wpic_font_size_0_75rem'>* We value your privacy, and will never share your email. You can remove your email from our database at any time by deactivating the plugin.</div></div>" span=""><a class="text-muted ml-2"><?php _e('Why do I need this?', 'wp-image-compression'); ?></a></span>
                                    <input type="email" class="form-control wpic_email" id="wpic_email" aria-describedby="emailHelp"/>                                
                                </div>

                                <div class="form-row mx-0 my-3">
                                    <label for="wpic_password">
                                        <?php _e('Password', 'wp-image-compression'); ?><span class="text-muted ml-2">(<?php _e('Containing letters & numbers, min 6 characters', 'wp-image-compression'); ?>)</span>
                                    </label>
                                    <input type="password" class="form-control wpic_password" id="wpic_password"/>                                
                                </div>

                                <div class="form-row mx-0 my-3">
                                    <label for="wpic_password_confirm">
                                        <?php _e('Confirm Password', 'wp-image-compression'); ?>
                                    </label>
                                    <input type="password" class="form-control wpic_password_confirm" id="wpic_password_confirm"/>                                
                                </div>

                                <div class="position-relative mt-5">
                                    <span class="loader" style="display: none;"></span>
                                    <input type="button" class="btn btn-primary w-100 wpic_create_account_submit" value="<?php _e('Create my Crush.pics account', 'wp-image-compression'); ?>"/>
                                </div>
                                <p class="text-muted mt-3 mb-0">
                                    <?php _e('By creating a Crush.pics account, you consent to and fully accept our', 'wp-image-compression'); ?>
                                    <a href="#" class="text-muted" ><?php _e('Privacy Policy', 'wp-image-compression'); ?></a>
                                    <?php _e('and', 'wp-image-compression'); ?>
                                    <a href="#" class="text-muted" ><?php _e('Terms of Service', 'wp-image-compression'); ?></a>.
                                </p>

                                <a href="javascript:void(0)" class="d-block text-center font-weight-bold pt-4 wpic_api_key_validate"><?php _e("Already have an API Key?", 'wp-image-compression'); ?></a>
                            </form>

                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
