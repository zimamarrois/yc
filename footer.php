</div> <!-- #container -->

<noscript class="text-danger">This page requires that you have Javascript enabled on your browser.</noscript>

<footer id="footer"><a href="http://flexphperia.net/yc" target="_blank">Your Calendar</a>&nbsp;/&nbsp;<a href="http://flexphperia.net" target="_blank">flexphperia.net</a></footer>

<div id="uiBlocker" style="display:none;">
    <div class="preloader"></div>
</div>

<?php if (!@$no_js): ?>
<script>
    yc_data('siteUrl', '<?php echo site_url() . '/'; ?>');
    yc_data('isErrorPage', <?php echo json_encode(@$is_error_page ? 1 : 0); ?>);
    yc_data('pagePath', '<?php echo $this->path; ?>');
    yc_data('imgGeneratorUrl', '<?php echo base_url(config_item('yc_img_generator_url')); ?>');  
    yc_data('checkVersion', <?php  echo json_encode ( $this->check_version ); ?>); 
    yc_data('version', '<?php echo $this->config->item('yc_version'); ?>');
    yc_data('lang' , {
        valFillFields : '<?php echo lang('validation_fill_fields'); ?>',
        valRequired : '<?php echo lang('validation_required'); ?>',
        valMinLength : '<?php echo lang('validation_min_length'); ?>',
        valMaxLength : '<?php echo lang('validation_max_length'); ?>',
        valPassword : '<?php echo lang('validation_password'); ?>',
        valUsername : '<?php echo lang('validation_username'); ?>',
        valEqualTo : '<?php echo lang('validation_equal_to'); ?>',
        valMin : '<?php echo lang('validation_min'); ?>',
        valMax : '<?php echo lang('validation_max'); ?>',
        valStep : '<?php echo lang('validation_step'); ?>',

        errorAjaxCallError : '<?php echo lang('ajax_error_call_error'); ?>',       
        errorIframe : <?php echo json_encode(lang('error_iframe')); ?>,   
        no_changes : '<?php echo lang('no_changes'); ?>',  
        unsaved : '<?php echo lang('unsaved'); ?>',
        newVersionMessage : '<?php echo lang('version_check'); ?>',
    });
</script>


    <?php if (ENVIRONMENT == 'development'):  
        require_once FCPATH.'public/js/dev/development.php';
    ?>
    <?php else: ?>
        <script src="<?php echo base_url('public/js/plugins-min.js?v'.$this->config->item('yc_version')); ?>" ></script>
        <script src="<?php echo base_url('public/js/main-min.js?v'.$this->config->item('yc_version')); ?>" ></script>
    <?php endif; ?>
  
<?php 
    //render registered scripts
    $this->controller->render_scripts();
    $this->controller->render_append_to_body();
?>
            
<?php endif; ?>
   

<?php if (!config_item('yc_allow_iframe')): ?>
<script>
    if (window.self !== window.top) {
        $('body').html('<a class="error-iframe" target="_blank" href="'+ document.location.href +'">'+ YourCalendar.lang.errorIframe +'</a>');
        throw new Error("iframe found"); 
    }
</script>            
<?php endif;?>
            
   
</body>
</html>