<div class="page page-edit-select fade">
    <div class="panel card">
        <div class="card-header">
            <?php echo lang('edit_select_header'); ?>
        </div>
        <div class="card-body">

            <div class="mb-3 <?php echo (count($item_types) == 1 ? 'd-none' : '') ?>">
                <label for="pes"><?php echo lang('edit_select_filter_label'); ?></label>
                <select id="piF" class="custom-select" data-filter>
                  <option value="all" selected><?php echo lang('edit_select_filter_all'); ?></option>
                  <?php foreach ($item_types as $item_type_vo): ?>
                  <option value="<?php echo $item_type_vo->id ?>"><?php echo htmlspecialchars($item_type_vo->name) ?></option> 
                  <?php endforeach; ?>
                </select>
            </div>	
            
            <div class=" <?php echo (count($list_data['items']) == 1 ? 'd-none' : '') ?>">
                <input type="search" class="form-control " id="pis" placeholder="<?php echo lang('edit_select_search'); ?>"  name="search">
            </div>
            
            <?php $this->load->view('parts/item_list'); ?>
        </div>
    </div>
</div>