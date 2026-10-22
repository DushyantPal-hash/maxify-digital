<?php
$template       = new OnecomTemplate();
$is_premium     = $template->groupone_is_premium( 'all_plugins' );

//$oc_body_class = ! $prev_scan ? 'oc-nps' : '';

?>
<div class="gv-activated ocsh-wrap">
	<div id="oc-error-toast" class="gv-toast-container"></div>
    <div class="gv-p-fluid gv-pb-0"><h3><?php echo __( 'Health Monitor' , 'onecom-wp' ); ?></h3>
        <p class="gv-mt-sm gv-text-sm"><?php echo __( 'Monitor essential security and performance checkpoints and fix them if needed.' , 'onecom-wp' ); ?></p>
    </div>
    <div class="gv-p-fluid gv-pt-lg">
		<div id="oc-hm-root"></div>
    </div>
</div>