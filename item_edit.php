<script>
    yc_data(
            'item' , <?php echo json_encode($item_vo->to_send_array()); ?>
    );
</script>

<div class="page page-item-edit fade">
    <div class="panel card form">
        <div class="card-header">
            <?php if ($add_mode): ?>
                <?php echo lang('item_edit_add'); ?><span class="ml-1"><?php echo htmlspecialchars($item_vo->type->name); ?></span>
            <?php else: ?>
                <?php echo lang('item_edit_edit'); ?><span class="ml-1"><?php echo htmlspecialchars($item_vo->name); ?></span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form>
                <div class="alert alert-danger d-none" role="alert"></div>

                <div class="form-group ">
                    <label for="pIeN" class="required"><?php echo lang('item_edit_name'); ?></label>
                    <input id="pIeN" type="text" class="form-control" name="name" data-val-required data-val-minlen="3" data-val-maxlen="38" maxlength="38" />
                </div>
                <div class="form-group ">
                    <label class="required"><?php echo lang('item_edit_icon'); ?></label>
                    <?php $this->load->view('parts/icon_selector', 
                            array(
                                'field_name' => 'icon', 
                                'data_attrs' => 'data-val-required data-val-delegate-sibling=".icon-list"',
                                'icons' => explode (',' , $item_vo->type->icons ),
                                )); ?>
                </div>

                <?php if (count($item_vo->type->field_types)):  ?>
                    <div class="section">
                        <h5><?php echo lang('item_edit_fields'); ?></h5>	


                        <div class="fields">
                            <div class="field d-none">
                                <p data-type="text"><?php echo lang('item_edit_field_type'); ?> <?php echo lang('item_edit_field_text'); ?></p>
                                <p data-type="image"><?php echo lang('item_edit_field_type'); ?> <?php echo lang('item_edit_field_image'); ?></p>
                                <p data-type="link"><?php echo lang('item_edit_field_type'); ?> <?php echo lang('item_edit_field_link'); ?></p>
                                <p><?php echo lang('item_edit_field_label'); ?><span class="ml-1" data-label></span></p>

                                <div class="form-group" data-type="text">
                                    <label><?php echo lang('item_edit_field_value'); ?></label>
                                    <input type="text" class="form-control" name="value" maxlength="200" />
                                </div>	

                                <div class="form-group" data-type="link">
                                    <label ><?php echo lang('item_edit_field_link_value'); ?></label>
                                    <input type="text" class="form-control" name="value" maxlength="200" />
                                </div>	
                                <div class="form-group" data-type="link">
                                    <label ><?php echo lang('item_edit_field_link_text'); ?></label>
                                    <input type="text" class="form-control" name="options[text]" maxlength="50" />
                                </div>	

                                <div class="form-group" data-type="image">
                                    <label><?php echo lang('item_edit_field_image_value'); ?></label>
                                    <?php $this->load->view('parts/uploader', array('field_name' => 'value', 'clear_button' => true, 'required' => false )); ?>
                                    <small  class="form-text text-muted">
                                        <?php echo lang('item_edit_field_image_help'); ?>
                                    </small>
                                </div>

                                <input type="hidden" name="id" />
                                <input type="hidden" name="type[id]" />
                            </div>		
                        </div>
                    </div>
                <?php endif; ?>
                <input type="hidden" name="id" />
            </form>
        </div>

        <div class="card-footer">
            <button type="button" class="btn btn-secondary ml-auto mr-2 btn-sm" data-save><?php echo lang('save_changes'); ?></button>
            <button type="button" class="btn btn-primary btn-sm " data-save-redirect="items"><?php echo lang('save_back_changes'); ?></button>
        </div>
    </div>

    <div class="form-buttons">
        <button type="button" class="btn btn-secondary btn-block btn-sm" data-save><?php echo lang('save_changes'); ?></button>
        <button type="button" class="btn btn-primary btn-block btn-sm" data-save-redirect="items"><?php echo lang('save_back_changes'); ?></button>
    </div>

</div>