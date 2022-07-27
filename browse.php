<script>
    yc_data('item_to_show' ,  <?php  echo json_encode ( $item_to_show ); ?>);
</script>


<div class="page page-browse fade">

    <?php $this->load->view('parts/cal_navigation'); ?>
    
    <?php if (!$this->settings_vo->disable_filtering && (count($item_types) > 1 || !empty($filter_data))  ): ?>
        <div class="filter ">
            <div class="form-group <?php echo (count($item_types) == 1 ? 'd-none' : '') ?>">
                <label class="my-1" ><?php echo lang('cal_filter_item_header') ?></label>
                <select  data-no-results="<?php echo lang('no_results') ?>"  data-type-filter  >
                    <?php if (count($item_types) > 1 ): ?>
                        <option value="all"><?php echo lang('cal_filter_all') ?></option>
                    <?php endif; ?>
                    <?php foreach ($item_types as $item_type_vo): ?>
                        <option value="<?php echo $item_type_vo->id ?>"><?php echo htmlspecialchars($item_type_vo->name) ?></option>
                    <?php endforeach; ?>
                </select> 
            </div>	
            <?php if (!empty($filter_data)): ?>
                <div class="form-group" data-field-filters >
                    <label class="d-block my-1" ><?php echo lang('cal_filter_field_header') ?></label>
                    <?php foreach ($filter_data as $data): ?>
                        <?php foreach ($data['fields'] as $field): ?>
                            <select data-no-results="<?php echo lang('no_results') ?>"
                                    placeholder="<?php echo html_entity_decode (trim($field['field_type']->label, ':')) ?>" 
                                    data-item-type-id="<?php echo $data['item_type']->id ?>"
                                    data-field-type-id="<?php echo $field['field_type']->id ?>"
                                    data-field-filter >
                                <option value="all"><?php echo lang('cal_filter_all') ?></option>
                                <?php foreach ($field['values'] as $value): ?>
                                     <option><?php echo htmlspecialchars($value) ?></option>
                                <?php endforeach; ?>
                            </select>                
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>


    <div class="calendars">
        <?php 
        echo $calendars_html;
        ?>
    </div>
    

    <?php $this->load->view('parts/month_selector'); ?>

</div>