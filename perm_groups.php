<div class="page page-perm-groups fade">
    <div class="panel card">
        <div class="card-header">
            <?php echo lang('perm_groups_header'); ?>
        </div>
        <div class="card-body">
            <a href="<?php echo site_url('permissions/group_edit') ?>" class="btn btn-primary btn-sm"><?php echo lang('perm_groups_add'); ?></a>
            <?php $this->load->view('parts/item_list'); ?>
        </div>
    </div>
    <?php $this->load->view('parts/modal_confirm'); ?>
</div>


