<div class="page page-event-types fade">
    <div class="panel card">
        <div class="card-header">
            <?php echo lang('event_types_header'); ?>
        </div>
        <div class="card-body">
            <?php if ($can_add): ?>
            <a href="<?php echo site_url('event_types/edit') ?>" class="btn btn-primary btn-sm"><?php echo lang('event_types_add'); ?></a>
            <?php endif; ?>
            <?php $this->load->view('parts/item_list'); ?>
        </div>
    </div>
    <?php $this->load->view('parts/modal_confirm'); ?>
</div>


