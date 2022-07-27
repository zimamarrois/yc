<div class="page page-users fade">

    <div class="panel card">
      <div class="card-header">
            <?php echo lang('users_header'); ?>
      </div>
      <div class="card-body">
           <?php if ($can_add): ?>
            <a href="<?php echo site_url('users/edit') ?>" class="btn btn-primary btn-sm"><?php echo lang('users_add'); ?></a>
          <?php endif; ?>
          <?php $this->load->view('parts/item_list' ); ?>
      </div>
    </div>
    
     <?php $this->load->view('parts/modal_confirm' ); ?>
</div>