<?php



get_header();

$pagePostID = htmlspecialchars($_GET["pid"]);
$pageAction =  htmlspecialchars($_GET["action"]);


?>
<main class="ncmaz-myNcmazEditorSubmissionPostPage relative">

    <div class="relative">
        <div class="nc-HeadBackgroundCommon hidden lg:block absolute h-[400px] max-h-full top-0 left-0 right-0 w-full bg-primary-100 dark:bg-neutral-800 bg-opacity-25 dark:bg-opacity-40" data-nc-id="HeadBackgroundCommon"></div>
        <div class="container relative pt-10 pb-16 lg:pt-14 lg:pb-24">
            <div class="mx-auto">
                <div class="ncmaz-mySubmissionPostPage__content my-5">
                    <?php
                    if (have_posts()) :
                        the_post();

                        the_content();
                    endif;
                    ?>
                </div>

                <?php if (is_user_logged_in()) : ?>

                    <!-- ====CREATE NEW POST==== -->
                    <?php if ($pageAction === 'create') : ?>
                        <?php if (is_user_logged_in() && current_user_can('edit_posts')) : ?>
                            <div data-is-react-component="TiptapEditorPostSubmission"></div>
                        <?php else : ?>
                            <?php
                            $AlertProps = (object)[];
                            $AlertProps->children = __('Sorry, you don\'t have permission to submission the post', 'ncmaz');
                            $AlertProps->type = "error";
                            ?>
                            <div data-is-react-component="Alert" data-component-props="<?php echo esc_attr(json_encode($AlertProps)); ?>"></div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- ====UPDATE THE POST==== -->
                    <?php if ($pageAction === 'edit') : ?>
                        <?php if (!empty((int)$pagePostID)) : ?>
                            <?php if (is_user_logged_in() && !empty(get_edit_post_link($pagePostID))) : ?>
                                <?php
                                $EditorPostSubmission = (object)[];
                                $EditorPostSubmission->action = "edit";
                                $EditorPostSubmission->postDatabaseID = (int)$pagePostID;
                                ?>
                                <div data-is-react-component="TiptapEditorPostSubmission" data-component-props="<?php echo esc_attr(json_encode($EditorPostSubmission)); ?>"></div>
                            <?php else : ?>
                                <?php
                                $AlertProps = (object)[];
                                $AlertProps->children = __('Sorry, you don\'t have permission to edit this post', 'ncmaz');
                                $AlertProps->type = "error";
                                ?>
                                <div data-is-react-component="Alert" data-component-props="<?php echo esc_attr(json_encode($AlertProps)); ?>"></div>
                            <?php endif; ?>
                        <?php else : ?>
                            <?php
                            $AlertProps = (object)[];
                            $AlertProps->children = __('No posts have been selected for editing, please check again.', 'ncmaz');
                            $AlertProps->type = "error";
                            ?>
                            <div data-is-react-component="Alert" data-component-props="<?php echo esc_attr(json_encode($AlertProps)); ?>"></div>
                        <?php endif; ?>


                    <?php endif; ?>


                <?php else : ?>
                    <?php
                    $AlertProps = (object)[];
                    $AlertProps->children = __('Please login to be able to submit post.', 'ncmaz');
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
