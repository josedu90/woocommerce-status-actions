<div class="submitbox" id="submitpost">
    <div id="major-publishing-actions">
        <?php $is_core_status = wc_sa_is_core_status($post->post_name); ?>
        <?php if(!$is_core_status): ?>
        <div id="delete-action">
            <a class="submitdelete" href="<?php echo home_url() ?>/wp-admin/admin.php?page=wc_sa_delete_status&status_id[0]=<?php echo $post->ID ?>"><?php _e('Delete status', 'woocommerce_status_actions') ?></a>
        </div>
        <?php endif; ?>
        <?php if($is_core_status): ?>
            <a type="button" class="button wc-action-button reset-to-default"
               href="<?php echo admin_url('admin.php?page=wc-settings&tab=wc_sa_settings&reset_default=' . $post->ID); ?>"><?php _e("Reset", "woocommerce_status_actions"); ?></a>
        <?php endif; ?>
        <div id="publishing-action">
            <span class="spinner"></span>
            <input name="original_publish" type="hidden" id="original_publish" value="Update">
            <?php if (!isset($_GET['action'])) { ?>
                <input type="submit" name="publish" id="publish" class="button button-primary button-large"
                       value="<?= __('Create Status', 'wc_packages') ?>">
            <?php } else { ?>
                <input name="save" type="submit" class="button button-primary button-large" id="publish"
                       value="<?php _e('Update Status', 'woocommerce_status_actions') ?>">
            <?php } ?>
        </div>
        <div class="clear"></div>
    </div>
</div>