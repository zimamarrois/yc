<script>
    yc_data('user_perms' ,  <?php  echo json_encode ( $user_perms_vo->to_send_array() ); ?>);
    yc_data('groups' ,  <?php  echo json_encode ( array_values($groups) ); //reset array keys  ?>);
</script>

<div class="page page-perm-user-edit fade">

    <div class="panel card form">
        <div class="card-header">
            <?php echo lang('perm_user_edit_edit'); ?><span class="ml-1"><?php echo htmlspecialchars($user_perms_vo->username); ?></span>
        </div>
        <div class="card-body">
            <form>
                <div class="section">   
                    <h5>
                        <?php echo lang('perm_user_groups'); ?>
                        <small class="form-text text-muted"> <?php echo lang('perm_user_groups_help'); ?></small>
                    </h5>
                    <?php foreach ($groups as $group_vo): $rand = rand(); ?>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" 
                                   class="custom-control-input "
                                   id="e-<?php echo $rand ?>" 
                                   name="groups[]" <?php echo($group_vo->id == 2 ? 'disabled' : '') ?> value="<?php echo $group_vo->id ?>" >
                            <label class="custom-control-label" for="e-<?php echo $rand ?>"><?php echo htmlspecialchars($group_vo->name) ?></label>
                            <?php if ($group_vo->id == 2): ?>
                            <small class="inherited-text form-text text-muted mt-0"><?php echo lang('perm_user_group_notlogged_help'); ?></small>
                            <?php endif; ?>
                        </div>	
                    <?php endforeach; ?>
                </div>
                <?php $this->load->view('parts/perm_form') ?>
                <input type="hidden" name="id" />
            </form>
        </div>

        <div class="card-footer">
            <button type="button" class="btn btn-secondary ml-auto mr-2 btn-sm" data-save><?php echo lang('save_changes'); ?></button>
            <button type="button" class="btn btn-primary btn-sm " data-save-redirect="permissions/users"><?php echo lang('save_back_changes'); ?></button>
        </div>
    </div>

    <div class="form-buttons">
        <button type="button" class="btn btn-secondary btn-block btn-sm" data-save><?php echo lang('save_changes'); ?></button>
        <button type="button" class="btn btn-primary btn-block btn-sm" data-save-redirect="permissions/users"><?php echo lang('save_back_changes'); ?></button>
    </div>



</div>
