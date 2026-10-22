<?php
get_header();
// $pageAction =  htmlspecialchars($_GET["action"]);
?>
<main class="ncmaz-myNcmazEditorEditProfilePage relative">

    <div class="relative">
        <div class="container relative pt-10 pb-16 lg:pt-14 lg:pb-24">
            <div class="mx-auto">
                <div class="ncmaz-myNcmazEditorEditProfilePage__content my-5">
                    <?php
                    if (have_posts()) :
                        the_post();

                        the_content();
                    endif;
                    ?>
                </div>

                <!-- ====EDIT PROFILE==== -->
                <?php if (is_user_logged_in()) : ?>
                    <div class="nc-NcmazAccountPage">
                        <div class="max-w-2xl">
                            <h2 class="text-3xl sm:text-4xl font-semibold">
                                <?php esc_html_e('Account settings', 'ncmaz'); ?>
                            </h2>
                            <span class="block mt-3 text-neutral-500 dark:text-neutral-400">
                                <?php esc_html_e('You can set preferred display name, create your profile URL and
                                manage other personal settings.', 'ncmaz'); ?>
                            </span>
                        </div>
                        <div class="mt-10" data-is-react-component="NcmazAccountPage"></div>
                    </div>

                <?php else : ?>
                    <?php
                    $AlertProps = (object)[];
                    $AlertProps->children = __('Please login to manage your account.', 'ncmaz');
                    $AlertProps->type = "error";
                    ?>
                    <div data-is-react-component="Alert" data-component-props="<?php echo esc_attr(json_encode($AlertProps)); ?>"></div>
                    <!-- BUTTON SIGNIN OPEN MODAN SIGN IN -->
                    <br>
                    <div class="flex space-x-3">
                        <a class="nc-Button relative h-auto inline-flex items-center justify-center rounded-full transition-colors text-sm sm:text-base font-medium px-4 py-3 sm:px-6  border border-neutral-200 dark:border-neutral-700" rel="noopener noreferrer" href="<?php echo esc_url(get_home_url('/')); ?>">
                            <?php echo esc_html__('Return Home Page', 'ncmaz'); ?>
                        </a>
                        <a data-ncmaz-open-modal="ncmaz-modal-form-sign-in" class="nc-Button relative h-auto inline-flex items-center justify-center rounded-full transition-colors text-sm sm:text-base font-medium px-4 py-3 sm:px-6  ttnc-ButtonPrimary disabled:bg-opacity-70 bg-primary-6000 hover:bg-primary-700 text-neutral-50  focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-6000 dark:focus:ring-offset-0" href="/wp-admin">
                            <?php echo esc_html__('Sign in', 'ncmaz'); ?>
                        </a>

                    </div>

                <?php endif; ?>

            </div>


        </div>


    </div>

</main>
<?php
get_footer();
