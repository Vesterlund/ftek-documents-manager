<?php
/**
 * 
 * Security check. No one can access without Wordpress itself
 * 
 * */
defined('ABSPATH') or die();



$allowedCapabilities = ftekdm_generate_capability_array();


$die = TRUE;
foreach($allowedCapabilities as $capability) {

	if(current_user_can($capability)) {
		$die = FALSE;
		break;
	}
}

if($die) die();

function ftekdm_generate_capability_array() {
	$options = get_option(FTEKDM_PATH_SETTINGS);
	$cString = $options['capability-list'];

	return explode(",",$cString);
}


?>

<div id='ftek-documents-manager-wrapper'>

</div>

<script>

PLUGINS_URL = '<?php echo plugins_url();?>';

jQuery(document).ready(function(){
	jQuery('#ftek-documents-manager-wrapper').elfinder({
		url: ajaxurl,
		customData:{action: 'connector'}
	});
});

</script>

<?php 

if( isset( $this->options->options['file_manager_settings']['show_url_path'] ) && !empty( $this->options->options['file_manager_settings']['show_url_path']) && $this->options->options['file_manager_settings']['show_url_path'] == 'hide' ){
	
?>
<style>
.elfinder-info-tb > tbody:nth-child(1) > tr:nth-child(2),
.elfinder-info-tb > tbody:nth-child(1) > tr:nth-child(3)
{
	display: none;
}
</style>
<?php
	
}

?>
