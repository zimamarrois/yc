<div class="page page-login">

    <div class="card panel narrow">
        <div class="card-header">
            <?php echo lang('login_header') ?>
        </div>
        <div class="card-body">
            <?php if($wrong_pass):  ?>
                <div class="alert alert-danger"><?php echo lang('login_wrong_pass'); ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label for="plL"><?php echo lang('login_login') ?></label>
                    <input type="text" class="form-control" id="plL" name="login" value="<?php echo htmlspecialchars($username) ?>">
                </div>
                <div class="form-group">
                    <label for="plP"><?php echo lang('login_pass') ?></label>
                    <input type="password" class="form-control" id="plP" name="password" value="">
                </div>
                
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="plr" name="remember_me" 
                               autocomplete="off" <?php echo ($remember_me ? 'checked' : false) ?>>
                        <label class="custom-control-label" for="plr">
                            <?php echo lang('login_remember') ?>
                        </label>
                    </div>
                </div>
                
                
                 <div class="form-group">
                    <button type="submit" class="btn btn-primary mx-auto d-block"><?php echo lang('login_login_button') ?></button>
                 </div>

                <p class="version text-muted my-0 small">v. <?php echo config_item('yc_version') ?></p>
            </form>

        </div>
    </div>

</div>

