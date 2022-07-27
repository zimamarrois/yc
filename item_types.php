<div class="page page-item-types fade">
    <div class="panel card">
        <div class="card-header">
            <?php echo lang('item_types_header'); ?>
        </div>
        <div class="card-body">
            <?php if ($can_add): ?>
            <a href="<?php echo site_url('item_types/edit') ?>" class="btn btn-primary btn-sm"><?php echo lang('item_types_add'); ?></a>
            <?php endif; ?>
            <p class="text-muted small mt-2 mb-0">
                <?php echo lang('item_types_order_help'); ?>
            </p>
            <?php $this->load->view('parts/item_list'); ?>
        </div>
    </div>
    <?php $this->load->view('parts/modal_confirm'); ?>
</div>