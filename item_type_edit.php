<script>
    yc_data('item_type', <?php echo json_encode($item_type_vo->to_send_array()); ?>);
</script>

<div class="page-item-type-edit fade">

    <div class="panel card form">
        <div class="card-header">
            <?php if ($add_mode): ?>
                <?php echo lang('item_type_edit_add'); ?>
            <?php else: ?>
                <?php echo lang('item_type_edit_edit'); ?><span class="ml-1"><?php echo htmlspecialchars($item_type_vo->name); ?></span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form>
                 <div class="alert alert-danger d-none" role="alert"></div>
                <div class="section">
                    <div class="form-group">
                        <label for="pITEN" class="required"><?php echo lang('item_type_edit_name'); ?></label>
                        <input id="pITEN" type="text" class="form-control" name="name" data-val-required data-val-minlen="3" data-val-maxlen="30" maxlength="30" />
                    </div>
                    <div class="form-group">
                        <label  class="required"> <?php echo lang('item_type_edit_icons'); ?></label>
                        <?php $this->load->view('parts/icon_selector', array('field_name' => 'icons', 'data_attrs' => 'data-val-required data-val-delegate-sibling=".icon-list"')); ?>
                        <small  class="form-text text-muted">
                            <?php echo lang('item_type_edit_icons_help'); ?>
                        </small>
                    </div>

                    <div class="form-group">
                        <label  class="required mb-0"> <?php echo lang('item_type_edit_events'); ?></label>
                        <small  class="form-text text-muted">
                                <?php echo lang('item_type_edit_events_help'); ?>
                        </small>
                        
                        <?php if (!count($event_types)): ?>
                        <div class="alert alert-warning">
                            <?php echo lang('item_type_edit_events_no'); ?>
                         </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-danger my-1 d-none" role="alert" data-no-events-selected><?php echo lang('item_type_edit_events_no_selected'); ?></div>
                        <?php 
                            foreach ($event_types as $event_type_vo)
                            {
                                $rand = mt_rand(1, 9999);
                                ?>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="et-<?php echo $rand; ?>" name="event_types[]" value="<?php echo $event_type_vo->id ?>" >
                                    <label class="custom-control-label" for="et-<?php echo $rand; ?>"><?php echo htmlspecialchars($event_type_vo->name) ?></label>
                                </div>
                                <?php
                            }
                        ?>

                    </div>
                    
                <div class="section">
                    <h5><?php echo lang('item_type_edit_desc_header'); ?></h5>
                    <button type="button" class="btn btn-link btn-sm mb-2 pl-0" data-desc-help>
                        <?php echo lang('item_type_edit_desc_help'); ?>
                    </button>
                       
                    <div class="form-group">
                        <label for="pITEd1l" ><?php echo lang('item_type_edit_desc1_label'); ?></label>
                        <input id="pITEd1l" type="text" class="form-control" name="desc1_label" data-val-minlen="3" data-val-maxlen="30" maxlength="30" />
                        <small  class="form-text text-muted">
                            <?php echo lang('item_type_edit_descs_help'); ?>
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="piTED1ls" name="desc1_label_show">
                            <label class="custom-control-label" for="piTED1ls">
                                <?php echo lang('item_type_edit_desc1_label_show'); ?>
                            </label>
                            <small class="form-text text-muted">
                                <?php echo lang('item_type_edit_desc_label_show_help'); ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="pITEd1t" ><?php echo lang('item_type_edit_desc1_type'); ?></label>
                        <select class="custom-select" id="pITEd1t" name="desc1_type">
                            <option value="short"><?php echo lang('item_type_edit_desc_type_short'); ?></option>
                            <option value="long"><?php echo lang('item_type_edit_desc_type_long'); ?></option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="pITEd2h" name="desc2_disabled">
                            <label class="custom-control-label" for="pITEd2h">
                                <?php echo lang('item_type_edit_desc2_disabled'); ?>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="pITEd2l" ><?php echo lang('item_type_edit_desc2_label'); ?></label>
                        <input id="pITEd2l" type="text" class="form-control" name="desc2_label" data-val-minlen="3" data-val-maxlen="30" maxlength="30" />
                        <small  class="form-text text-muted">
                            <?php echo lang('item_type_edit_descs_help'); ?>
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="piTED2ls" name="desc2_label_show">
                            <label class="custom-control-label" for="piTED2ls">
                                <?php echo lang('item_type_edit_desc2_label_show'); ?>
                            </label>
                            <small class="form-text text-muted">
                                <?php echo lang('item_type_edit_desc_label_show_help'); ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="pITEd2t" ><?php echo lang('item_type_edit_desc2_type'); ?></label>
                        <select class="custom-select" id="pITEd2t" name="desc2_type">
                            <option value="short"><?php echo lang('item_type_edit_desc_type_short'); ?></option>
                            <option value="long"><?php echo lang('item_type_edit_desc_type_long'); ?></option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="pITEdsst" ><?php echo lang('item_type_edit_desc_show_how'); ?></label>
                        <select class="custom-select" id="pITEdsst" name="desc_show_how">
                            <option value="asone"><?php echo lang('item_type_edit_desc_show_asone'); ?></option>
                            <option value="separate"><?php echo lang('item_type_edit_desc_show_separate'); ?></option>
                        </select>
                        <small  class="form-text text-muted">
                            <?php echo lang('item_type_edit_desc_show_type_help'); ?>
                        </small>
                    </div>
                </div>
                </div>


                <div class="section">
                    <h5> <?php echo lang('item_type_edit_fields'); ?></h5>
                    <button type="button" class="btn btn-link btn-sm mb-2 pl-0" data-pos-help>
                        <?php echo lang('item_type_edit_fields_help'); ?>
                    </button>
                    


                    <div class="dropdown" data-add-field>
                        <button class="btn btn-primary dropdown-toggle btn-sm mb-2" type="button" data-toggle="dropdown" >
                            <?php echo lang('item_type_edit_fields_add'); ?>
                        </button>
                        <div class="dropdown-menu"  >
                            <a class="dropdown-item" href="text"><?php echo lang('item_type_edit_field_text'); ?></a>
                            <a class="dropdown-item" href="image"><?php echo lang('item_type_edit_field_image'); ?></a>
                            <a class="dropdown-item" href="link"><?php echo lang('item_type_edit_field_link'); ?></a>
                        </div>
                    </div>
                    
                    
                    <div class="fields">
                        <div class="field d-none">

                            <div class="left">
                                <p data-type="text"><?php echo lang('item_type_edit_field_type'); ?> <?php echo lang('item_type_edit_field_text'); ?></p>
                                <p data-type="image"><?php echo lang('item_type_edit_field_type'); ?> <?php echo lang('item_type_edit_field_image'); ?></p>
                                <p data-type="link"><?php echo lang('item_type_edit_field_type'); ?> <?php echo lang('item_type_edit_field_link'); ?></p>
                                <div class="form-group">
                                    <label class="required" ><?php echo lang('item_type_edit_field_label'); ?></label>
                                    <input type="text" class="form-control" name="label" data-val-required data-val-minlen="3" data-val-maxlen="20" maxlength="20" />
                                </div>			
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="label_show" >
                                        <label class="custom-control-label"><?php echo lang('item_type_edit_field_label_show'); ?></label>
                                    </div>
                                </div>
                                <div class="form-group" >
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="allow_filtering" >
                                        <label class="custom-control-label"><?php echo lang('item_type_edit_field_filtering'); ?></label>
                                        <small  class="form-text text-muted">
                                            <?php echo lang('item_type_edit_field_filtering_help'); ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="form-group" >
                                    <label><?php echo lang('item_type_edit_field_size'); ?></label>
                                    <select class="custom-select" name="size">
                                        <option value="s" selected><?php echo lang('item_type_edit_field_size_s'); ?></option>
                                        <option value="m"><?php echo lang('item_type_edit_field_size_m'); ?></option>
                                        <option value="l"><?php echo lang('item_type_edit_field_size_l'); ?></option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label ><?php echo lang('item_type_edit_field_position'); ?></label>
                                    <select class="custom-select" name="position">
                                        <option value="a" selected>A</option>
                                        <option value="b">B</option>
                                        <option value="c">C</option>
                                        <option value="d">D</option>
                                    </select>
                                </div>
                            </div>

                            <div class="right">
                                <button type="button" class="btn btn-secondary btn-sm" data-move-up><?php echo lang('item_type_edit_field_up'); ?></button>
                                <button type="button" class="btn btn-secondary btn-sm" data-move-down><?php echo lang('item_type_edit_field_down'); ?></button>
                                <button type="button" class="btn btn-secondary btn-sm" data-delete><?php echo lang('delete'); ?></button>
                            </div>

                            <input type="hidden" name="id" />
                            <input type="hidden" name="type" />
                        </div>		
                    </div>

                    <input type="hidden" name="id" />
            </form>
        </div>
        <div class="card-footer">
            <button type="button" class="btn btn-secondary ml-auto mr-2 btn-sm" data-save><?php echo lang('save_changes'); ?></button>
            <button type="button" class="btn btn-primary btn-sm " data-save-redirect="item_types"><?php echo lang('save_back_changes'); ?></button>
        </div>
    </div>

    <div class="form-buttons">
        <button type="button" class="btn btn-secondary btn-block btn-sm" data-save><?php echo lang('save_changes'); ?></button>
        <button type="button" class="btn btn-primary btn-block btn-sm" data-save-redirect="item_types"><?php echo lang('save_back_changes'); ?></button>
    </div>

    <?php $this->load->view('parts/modal_fields_positions'); ?>
    <?php $this->load->view('parts/modal_desc_fields'); ?>

</div>

