<?php if (isloggedin()) : ?>
    <section role="sidebar" class="left-sidebar<?php echo ($showsidebar) ? ' open' : ''?>">
        <?php echo theme_institutes_ceu_get_sidebar_topmenu(); ?>
        <div class="side-pre-box clearfix">
            <div class="sidebar-title"><?php echo get_string('settings', 'theme_institutes_ceu'); ?><i class="ion-ios-close-outline" onclick="toggleSidePre();" title="<?php echo get_string('close', 'theme_institutes_ceu'); ?>"></i></div>
            <?php echo $OUTPUT->blocks('side-pre', $sidepre); ?>
        </div>
    </section>
<?php endif; ?>