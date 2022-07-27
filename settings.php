<script>
    yc_data('settings' ,  <?php  echo json_encode ( $this->settings_vo->to_send_array() ); ?>);
</script>

<div class="page page-settings fade ">

<div class="panel card form">
    <div class="card-header"><?php echo lang('settings_header'); ?></div>
    <div class="card-body">
        <form>
           <div class="alert alert-danger d-none"></div>
            <div class="section">
                <h5><?php echo lang('settings_section_browse_header'); ?></h5>
                <div class="form-group">
                    <label for="pSBMN"><?php echo lang('settings_editor_month_num'); ?></label>
                    <input id="pSBMN" type="number" class="form-control" step="1" min="1" max="3" name="browse_month_num" data-val-required data-val-step="1" data-val-min="1" data-val-max="3"   />
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="pSER" name="show_empty_rows">
                        <label class="custom-control-label" for="pSER">
                            <?php echo lang('settings_show_empty_rows'); ?>
                        </label>
                        <small  class="form-text text-muted">
                            <?php echo lang('settings_show_empty_rows_help'); ?>
                        </small>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="pSDf" name="disable_filtering" >
                        <label class="custom-control-label" for="pSDf"><?php echo lang('settings_disable_filtering'); ?></label>
                        <small  class="form-text text-muted">
                            <?php echo lang('settings_disable_filtering_help'); ?>
                        </small>
                    </div>		
                </div>	
            </div>	
            <div class="section">
                <h5><?php echo lang('settings_section_edit_header'); ?></h5>
                <div class="form-group">
                    <label for="pSEMN"><?php echo lang('settings_editor_month_num'); ?></label>
                    <input id="pSEMN" type="number" class="form-control" step="1" min="1" max="3" name="editor_month_num" data-val-required data-val-step="1" data-val-min="1" data-val-max="3"   />
                </div>
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="pSht" name="editor_hide_tips">
                        <label class="custom-control-label" for="pSht"><?php echo lang('settings_editor_hide_tips'); ?></label>
                    </div>
                </div>
            </div>
            <div class="section">
                <h5><?php echo lang('settings_section_other_header'); ?></h5>
                
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="pSHL" name="hide_login_button" >
                        <label class="custom-control-label" for="pSHL"><?php echo lang('settings_hide_login_button'); ?></label>
                        <small  class="form-text text-muted">
                            <?php echo lang('settings_hide_login_button_help'); ?><span class="ml-1"><?php echo site_url('login'); ?></span><br/>
                            <?php echo lang('settings_refresh_help'); ?>
                        </small>
                    </div>		
                </div>		
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="pSHH" name="hide_header">
                        <label class="custom-control-label" for="pSHH"><?php echo lang('settings_hide_header'); ?></label>
                        <small  class="form-text text-muted">
                            <?php echo lang('settings_refresh_help'); ?>
                        </small>
                    </div>
                </div>	
                
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="pSHE" name="edit_one_only">
                        <label class="custom-control-label" for="pSHE"><?php echo lang('settings_edit_one_only'); ?></label>
                        <small  class="form-text text-muted">
                            <?php echo lang('settings_edit_one_only_help'); ?>
                            <span class="ml-1"><?php echo site_url('edit-select') ?></span><br/>
                            <?php echo lang('settings_refresh_help'); ?>
                        </small>
                    </div>
                </div>	
                
                <div class="form-group">
                    <label for="psL" ><?php echo lang('settings_language'); ?></label>
                    <select class="custom-select" id="psL" name="language">
                        <option value="auto"><?php echo lang('settings_language_auto'); ?></option>
                        <?php foreach($this->config->item('yc_languages') as $key => $value): ?>
                             <option value="<?php echo $key ?>"><?php echo $value ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small  class="form-text text-muted">
                        <?php echo lang('settings_refresh_help'); ?>
                    </small>
                </div>

                
                <div class="form-group">
                    <label><?php echo lang('settings_logo'); ?></label>
                    <?php $this->load->view('parts/uploader', array('field_name' => 'logo', 'required' => true )); ?>
                    <small  class="form-text text-muted">
                        <?php echo lang('settings_logo_help'); ?><br/>
                        <?php echo lang('settings_refresh_help'); ?>
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="psTi" class="required"><?php echo lang('settings_title_field'); ?></label>
                    <input id="psTi" type="text" class="form-control" name="title" data-val-required data-val-minlen="3" data-val-maxlen="30" maxlength="30" />
                    <small  class="form-text text-muted">
                        <?php echo lang('settings_title_field_help'); ?>
                    </small>
                </div>
            </div>
        </form>
    </div>

    <div class="card-footer">
        <button type="button" class="btn btn-secondary ml-auto mr-2 btn-sm" data-save><?php echo lang('save_changes'); ?></button>
        <button type="button" class="btn btn-primary btn-sm " data-save-redirect="/"><?php echo lang('save_back_changes'); ?></button>
    </div>
</div>

<div class="form-buttons">
    <button type="button" class="btn btn-secondary btn-block btn-sm" data-save><?php echo lang('save_changes'); ?></button>
    <button type="button" class="btn btn-primary btn-block btn-sm" data-save-redirect="/"><?php echo lang('save_back_changes'); ?></button>
</div>

</div>