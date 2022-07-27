<script>
    yc_data('group' ,  <?php  echo json_encode ( $group_vo->to_send_array() ); ?>);
</script>

<div class="page page-perm-group-edit fade">


    <div class="panel card form">
        <div class="card-header">
            <?php if ($add_mode): ?>
                <?php echo lang('perm_group_edit_add'); ?>
            <?php else: ?>
                <?php echo lang('perm_group_edit_edit'); ?><span class="ml-1"><?php echo htmlspecialchars($group_vo->name); ?></span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form>
                <div class="section">
                    <div class="form-group">
                        <label for="PpgeN" class="required"><?php echo lang('perm_group_edit_name'); ?></label>
                        <input id="PpgeN" type="text" class="form-control" name="name" 
                               data-val-required data-val-minlen="3" 
                               data-val-maxlen="30" 
                               maxlength="30" <?php echo ($group_vo->id == 2 ? 'disabled': '' );?> />
                    </div>
                </div>
                <?php $this->load->view('parts/perm_form') ?>
                <input type="hidden" name="id" />
            </form>
        </div>

        <div class="card-footer">
            <button type="button" class="btn btn-secondary ml-auto mr-2 btn-sm" data-save><?php echo lang('save_changes'); ?></button>
            <button type="button" class="btn btn-primary btn-sm " data-save-redirect="permissions/groups"><?php echo lang('save_back_changes'); ?></button>
        </div>
    </div>

    <div class="form-buttons">
        <button type="button" class="btn btn-secondary btn-block btn-sm" data-save><?php echo lang('save_changes'); ?></button>
        <button type="button" class="btn btn-primary btn-block btn-sm" data-save-redirect="permissions/groups"><?php echo lang('save_back_changes'); ?></button>
    </div>



</div>
