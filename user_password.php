<div class="page page-user-pass fade">

    <div class="card panel form">
        <div class="card-header">
            <?php echo lang('user_pass_header'); ?>
        </div>
        <div class="card-body">
            <form>
                <div class="alert alert-danger d-none" role="alert"></div>
                <div class="form-group">
                    <label for="ppPass"><?php echo lang('user_pass_old'); ?></label>
                    <input type="password" class="form-control" id="ppPass" name="old_password" data-val-required data-val-pass>
                </div>
                <div class="form-group">
                    <label for="ppPassNew"><?php echo lang('user_pass_new'); ?></label>
                    <input type="password" class="form-control" id="ppPassNew" name="new_password1" data-val-required data-val-pass>
                    <small  class="form-text text-muted">
                        <?php echo lang('user_edit_pass_help'); ?>
                    </small>
                </div>
                <div class="form-group">
                    <label for="ppPassRep"><?php echo lang('user_pass_repeat'); ?></label>
                    <input type="password" class="form-control" id="ppPassRep" name="new_password2" 
                           data-val-required data-val-pass data-val-equalto="#ppPassNew"
                           
                           >
                </div>
            </form>

        </div>

        <div class="card-footer">
            <button type="button" class="btn btn-secondary ml-auto mr-2 btn-sm" data-save><?php echo lang('save_changes'); ?></button>
            <button type="button" class="btn btn-primary btn-sm " data-save-redirect="users"><?php echo lang('save_back_changes'); ?></button>
        </div>
    </div>


    <div class="form-buttons">
        <button type="button" class="btn btn-secondary btn-block btn-sm" data-save><?php echo lang('save_changes'); ?></button>
        <button type="button" class="btn btn-primary btn-block btn-sm" data-save-redirect="main"><?php echo lang('save_back_changes'); ?></button>
    </div>

</div>

