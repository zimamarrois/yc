<script>
    yc_data('event_type' ,  <?php  echo json_encode ( $event_type_vo->to_send_array() ); ?>);
</script>

<div class="page page-event-type-edit fade">
    <div class="panel card form">
        <div class="card-header">
            <?php if ($add_mode): ?>
                <?php echo lang('event_type_edit_add'); ?>
            <?php else: ?>
                <?php echo lang('event_type_edit_edit'); ?><span class="ml-1"><?php echo htmlspecialchars($event_type_vo->name); ?></span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form>
                <div class="alert alert-danger d-none" role="alert"></div>
                <div class="form-group">
                    <label for="pEteN" class="required"><?php echo lang('event_type_edit_name'); ?></label>
                    <input id="pEteN" type="text" name="name" class="form-control" data-val-required data-val-maxlen="30"  data-val-minlen="3" maxlength="30" />
                </div>
                <div class="form-group">
                    <label class="required"><?php echo lang('event_type_edit_icon'); ?></label>
                    <?php $this->load->view('parts/icon_selector', array('field_name' => 'icon', 'data_attrs' => 'data-val-required data-val-delegate-sibling=".icon-list"' )); ?>
                </div>
                <input type="hidden" name="id" />
            </form>
        </div>

        <div class="card-footer">
            <button type="button" class="btn btn-secondary ml-auto mr-2 btn-sm" data-save><?php echo lang('save_changes'); ?></button>
            <button type="button" class="btn btn-primary btn-sm " data-save-redirect="event_types"><?php echo lang('save_back_changes'); ?></button>
        </div>
    </div>


    <div class="form-buttons">
        <button type="button" class="btn btn-secondary btn-block btn-sm" data-save><?php echo lang('save_changes'); ?></button>
        <button type="button" class="btn btn-primary btn-block btn-sm" data-save-redirect="event_types"><?php echo lang('save_back_changes'); ?></button>
    </div>
</div>

