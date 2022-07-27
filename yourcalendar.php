<?php

//main Your Calendar config
$config['yc_version'] = '1.00';
$config['yc_languages'] = array(
    'en' => 'english', //names must be lower case
    'pl' => 'polski'
);
$config['yc_allow_iframe'] = true;
$config['yc_hide_main_event_label'] = false; //hides main event label on events popup

$config['yc_storage_path'] = realpath(APPPATH . '../storage') . '/';
$config['yc_uploads_path'] = realpath($config['yc_storage_path'] . 'uploads') . '/';
$config['yc_temp_path'] = realpath($config['yc_storage_path'] . 'temp') . '/';
$config['yc_cache_path'] = realpath($config['yc_storage_path'] . 'cache') . '/';
$config['yc_img_generator_url'] = 'img/i.php';


//order of icons sections
$config['icons_order'] = array(
    'icons_people',
    'icons_absence',
    'icons_arrows',
    'icons_multimedia',
    'icons_numbers',
    'icons_reservation',
    'icons_vehicles',
    'icons_animals',
    'icons_other',
);

$config['icons'] = array(
 'icons_absence' => array(
'airplane_take_off','beach','beach_umbrella','being_sick','holiday','hospital','hospital_bed','sun_lounger'
 ),
 'icons_animals' => array(
'corgi','dog_1','dog_collar','elephant','german_shepherd','horse','horseback_riding','shiba_inu'
 ),
 'icons_arrows' => array(
'arrow','arrow_pointing_left_1','back_to','chevron_down','chevron_left','chevron_right','chevron_up','decrease','down_3','down_arrow','down_button','down_left','increase','left_2','low_importance','next_page','right_2','slide_up','thick_arrow_pointing_up','up_2','up_right'
 ),
 'icons_multimedia' => array(
'android_tablet','calculator','circled_play','compact_camera','computer_support','envelope','film_roll','flash_on','imac','keyboard','laptop','lens','micro_sd','mouse_right_click','music_video','musical_notes','print','radio_station','secured_letter','slr_small_lens','small_lens','softbox','sound','switch_camera','unsplash','video_call','video_projector','vintage_camera','workstation'
 ),
 'icons_numbers' => array(
'circled_0','circled_1','circled_2','circled_3','circled_4','circled_5','circled_6','circled_7','circled_8','circled_9'
 ),
 'icons_other' => array(
'about','audit','box_important','cancel','checked','clock','close_window','cutlery','delete','food','initiate_money_transfer','lunchbox','maintenance','megaphone','money_bag','moon1','moon2','moon3','moon4','moon5','moon6','moon7','moon8','moon9','new_document','note','ok','paper_money','restaurant','shutdown','star_half','star_half_empty','survey','syringe','tableware','timesheet','vomited'
 ),
 'icons_people' => array(
'female1','female11','female2','female3','female4','female5','female6','female7','female8','female9','business_building','collaboration','conference','conference_foreground_selected','connected_people','couple','coworking','crowd','cycling','driver','elevator','employee','exercise','family','fast_track_female','group_task','leadership','man','management','meeting','meeting_room','mother','party','people','people_working_together','pushups','restaurant_table','romance','soccer','squats','staff','students','tango','teamwork','ticket_purchase','tourism_1','track_and_field','training','traveler','update_user','user_groups','valet_parking','video_conference','waiting_room','workspace','male1','male10','male11','male12','male2','male3','male4','male6','male7','male8','team1','team2','team3'
 ),
 'icons_reservation' => array(
'booking','close_sign','door_closed','door_hanger','lock','no_entry','open_door','open_sign','padlock','password_1','unavailable'
 ),
 'icons_vehicles' => array(
'airplane_mode_off','airplane_mode_on','airport','ambulance','brake_warning','bus','car','car_battery','car_fire','car_rental','car_roof_box','car_service','car_top_view','cargo_ship','carpool','container_truck','convertible','crashed_car','delivery','door_ajar','engine_oil','engine_oil_1','flat_tire','fork_lift','gas_pump','gas_station','in_transit','motorcycle','pickup','sedan','semi_truck','shuttle','submarine','subway','suv','taxi','tire','tire_iron','titanic','tow_truck','traffic_jam','trailer_unloading','tram_side_view'
 ),
);