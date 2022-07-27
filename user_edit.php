<script>
    yc_data('user' ,  <?php  echo json_encode ( $user_vo->to_send_array() ); ?>);
</script>

<div class="page page-user-edit fade">
    <div class="panel card form">
        <div class="card-header">
            <?php if ($add_mode): ?>
                <?php echo lang('user_edit_add'); ?>
            <?php else: ?>
                <?php echo lang('user_edit_edit'); ?><span class="ml-1"><?php echo $user_login; ?></span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form>
                <div class="alert alert-danger d-none" role="alert"></div>

                <div class="form-group">
                    <label for="pUElogin" class="required"><?php echo lang('user_edit_login'); ?></label>
                    <input id="pUElogin" type="text" class="form-control" 
                           name="username" <?php echo ($login_disabled ? 'disabled' : '' ); ?>
                           data-val-required data-val-minlen="3" data-val-maxlen="12" data-val-username maxlength="12"
                           />

                    <?php if ($login_disabled): ?>
                        <small class="form-text text-muted">
                            <?php echo lang('user_edit_login_disabled'); ?><span class="ml-1"><?php echo $user_login; ?></span>
                        </small>
                    <?php else: ?>
                        <small class="form-text text-muted">
                            <?php echo lang('user_edit_login_help'); ?>
                        </small>
                    <?php endif; ?>
                </div>

                <div class="form-group <?php echo ($add_mode ? 'd-none': ''); ?>">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="pUEChangeP" name="pass_change">
                        <label class="custom-control-label" for="pUEChangeP"><?php echo lang('user_edit_pass_change'); ?></label>
                    </div>		
                </div>	

                <div class="form-group">
                    <label for="pUEChangePI" class="required"><?php echo lang('user_edit_pass'); ?></label>
                    <input type="password" class="form-control" id="pUEChangePI" 
                           name="password" <?php echo (!$add_mode ? 'disabled' : '' ); ?>
                           data-val-required data-val-pass
                           >
                    <small  class="form-text text-muted">
                        <?php echo lang('user_edit_pass_help'); ?>
                    </small>
                </div>		
                <input type="hidden" name="id" />
            </form>
        </div>

        <div class="card-footer">
            <button type="button" class="btn btn-secondary ml-auto mr-2 btn-sm" data-save><?php echo lang('save_changes'); ?></button>
            <button type="button" class="btn btn-primary btn-sm " data-save-redirect="users"><?php echo lang('save_back_changes'); ?></button>
        </div>
    </div>


    <div class="form-buttons">
        <button type="button" class="btn btn-secondary btn-block btn-sm" data-save><?php echo lang('save_changes'); ?></button>
        <button type="button" class="btn btn-primary btn-block btn-sm" data-save-redirect="users"><?php echo lang('save_back_changes'); ?></button>
    </div>

</div>



