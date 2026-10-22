<?php /* Display services section content if enabled */ ?>
<?php if (get_post_meta(get_the_ID(), 'generic_featured_section_switch', true) != 'off'): ?>
    <section class="section home-services white-bg text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="section-title">
                        <h2>
                            <?php echo get_post_meta(get_the_ID(), 'generic_section_title', true); ?>                               
                        </h2>
                    </div>
                    <div class="section-content">
                        <?php echo nl2br(get_post_meta(get_the_ID(), 'generic_section_description', true)); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php

 endif;