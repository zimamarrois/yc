<div class="page page-items fade">
    <div class="panel card">
        <div class="card-header">
            <?php echo lang('items_header'); ?>
        </div>
        <div class="card-body">
            
            <?php if (count($add_item_types) > 1): ?>
                <div class="dropdown">
                  <button class="btn btn-primary btn-sm dropdown-toggle mb-2" type="button" data-toggle="dropdown" >
                    <?php echo lang('items_add'); ?>
                  </button>
                  <div class="dropdown-menu" >
                    <?php foreach ($add_item_types as $item_type_vo): ?>
                 <a class="dropdown-item" href="<?php echo site_url('items/edit/0/'.$item_type_vo->id) ?>"><?php echo htmlspecialchars($item_type_vo->name) ?></a>
                    <?php endforeach; ?>
                  </div>
                </div>
            <?php elseif (count($add_item_types) == 1): ?>
                <a class="btn btn-primary btn-sm mb-2 " type="button" href="<?php echo site_url('items/edit/0/'.reset($add_item_types)->id) ?>" >
                    <?php echo lang('items_add'); ?>
                </a>
            <?php endif; ?>

            
            
            <?php if (count($filter_item_types) > 1): ?>
                <div class="mb-2">
                    <label for="piF"><?php echo lang('items_filter_label'); ?></label>
                    <select id="piF" class="custom-select" data-filter >
                      <option value="all" selected><?php echo lang('items_filter_all'); ?></option>
                      <?php foreach ($filter_item_types as $item_type_vo): ?>
                      <option value="<?php echo $item_type_vo->id ?>"><?php echo htmlspecialchars($item_type_vo->name) ?></option> 
                      <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            
            
            <small  class="form-text text-muted">
                <?php echo lang('items_linking_1'); ?><br/>
                <?php echo site_url(lang('items_linking_2')); ?><br/>
                <?php echo lang('items_linking_3'); ?>  
            </small>
            
            <?php $this->load->view('parts/item_list'); ?>
        </div>
    </div>
    <?php $this->load->view('parts/modal_confirm'); ?>
</div>