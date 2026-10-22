<?php
global $ncmaz_redux_demo;
$socials = [
    'facebook'  => '',
    'twitter'   => '',
    'google'    => '',
];

if (defined('_NCMAZ_FRONTEND_VERSION')) {
    $socials['facebook'] =  $ncmaz_redux_demo['nc-general-settings--nextend-social-login-facebook'];
    $socials['twitter'] =  $ncmaz_redux_demo['nc-general-settings--nextend-social-login-twitter'];
    $socials['google'] =  $ncmaz_redux_demo['nc-general-settings--nextend-social-login-google'];
}

if (!$socials['facebook'] && !$socials['twitter'] && !$socials['google']) {
    return '';
}
?>

<div class="space-y-5 text-sm">
    <div class="text-center">
        <span> <?php echo esc_html__('Or sign in with social', 'ncmaz'); ?> </span>
    </div>
    <div>

        <div class="flex items-center flex-wrap space-x-3 justify-center">
            <!-- FACEBOOK -->
            <?php if (!!$socials['facebook']) : ?>
                <a href="<?php echo esc_url($socials['facebook']); ?>" data-plugin="nsl" data-action="connect" data-redirect="current" data-provider="google" data-popupwidth="600" data-popupheight="600" class="flex items-center justify-center text-xl lg:text-h4 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-full lg:w-20 w-12 lg:h-12 h-8 border-2 text-[#4267b3] border-[#4267b3]">
                    <i class="lab la-facebook-f"></i>
                </a>
            <?php endif; ?>

            <!-- TWITTER -->
            <?php if (!!$socials['twitter']) : ?>
                <a href="<?php echo esc_url($socials['twitter']); ?>" data-plugin="nsl" data-action="connect" data-redirect="current" data-provider="twitter" data-popupwidth="600" data-popupheight="600" class="flex items-center justify-center text-xl lg:text-h4 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-full lg:w-20 w-12 lg:h-12 h-8 border-2 text-[#1d9bf0] border-[#1d9bf0]">
                    <i class="lab la-twitter"></i>
                </a>
            <?php endif; ?>

            <!-- GOOGLE -->
            <?php if (!!$socials['google']) : ?>
                <a href="<?php echo esc_url($socials['google']); ?>" data-plugin="nsl" data-action="connect" data-redirect="current" data-provider="google" data-popupwidth="600" data-popupheight="600" class="flex items-center justify-center text-xl lg:text-h4 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-full lg:w-20 w-12 lg:h-12 h-8 border-2 text-[#df2e1c] border-[#df2e1c]">
                    <i class="lab la-google-plus"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>