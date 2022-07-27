<div class="page page-edit fade" data-item-id="<?php echo $item_id; ?>">
    
    <?php $this->load->view('parts/cal_navigation'); ?>

    <?php if (!$this->settings_vo->editor_hide_tips): ?>
        <div class="tips text-muted">
            <p><small><?php echo lang('edit_tip1'); ?></small></p> 
            <p><small><?php echo lang('edit_tip2'); ?></small></p>
        </div>
    <?php endif; ?>


    <?php 
     foreach ($cal_data as $data)
     {
         $cal_renderer->calendar(
                 $data['year'], 
                 $data['month'], 
                 $data['events'],
                 $items,
                 true,
                 true
                 );
     }
    
    ?>


    <?php $this->load->view('parts/modal_events_edit'); ?>
    <?php $this->load->view('parts/modal_confirm'); ?>
    
    <?php $this->load->view('parts/panel_selection'); ?>
    <?php $this->load->view('parts/month_selector'); ?>




</div>


