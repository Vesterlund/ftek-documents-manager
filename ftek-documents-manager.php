<?php
/*
Plugin Name: Ftek Documents Manager
Author Name: Ingrid Strandberg
License: GPLv2
Version: 2.1.0
Description: Ladda upp sektionsmötesprotokoll, med mera.
GitHub Plugin URI: Fysikteknologsektionen/ftek-documents-manager
*/

 /**
 * Based on plugin File Manager
 * by  Aftabul Islam
 * License: GPLv2
 * Description: Manage your file the way you like. You can upload, delete, copy, move, rename, compress, extract files. You don't need to worry about ftp. It is realy simple and easy to use.
 *
 * */

// Directory Seperator
if( !defined( 'DS' ) ){

	PHP_OS == "Windows" || PHP_OS == "WINNT" ? define("DS", "\\") : define("DS", "/");

}
// Including elFinder class
require_once('elFinder' . DS . 'elFinder.php');

// Including bootstarter
require_once('BootStart' . DS . '__init__.php');

if (!class_exists('FM')) {
  class FM extends FM_BootStart {

	public function __construct($name){

	  // Adding Menu
	  $this->menu_data = array(
		  'type' => 'menu',
		  );

	  // Adding Ajax
	  $this->add_ajax('connector'); // elFinder ajax call
	  $this->add_ajax('valid_directory'); // Checks if the directory is valid or not

	  parent::__construct($name);

	}

	/**
	 *
	 * File manager connector function
	 *
	 * */
	public function connector(){
		$userCap = -1;
	  // Checks if the current user have enough authorization to operate.
		if (current_user_can('manage_styret_files')) {
			$userCap = 0;
		}elseif (current_user_can('finform_files')) {
			$userCap = 1;
		}elseif (current_user_can('fnollk_files')) {
			$userCap = 2;
		}else {
			die();
		}

		//Todo: Prevent from starting if user has MORE than one manage files capability

	  //~ Holds the list of avilable file operations.
	  $file_operation_list = array(
		  'open', // Open directory
		  'ls',   // File list inside a directory
		  'tree', // Subdirectory for required directory
		  'parents', // Parent directory for required directory
		  'tmb', // Newly created thumbnail list
		  'size', // Count total file size
		  'mkdir', // Create directory
		  'mkfile', // Create empty file
		  'rm', // Remove dir/file
		  'rename', // Rename file
		  'duplicate', // Duplicate file - create copy with "copy %d" suffix
		  'paste', // Copy/move files into new destination
		  'upload', // Save uploaded file
		  'get', // Return file content
		  'put', // Save content into text file
		  'archive', // Create archive
		  'extract', // Extract files from archive
		  'search', // Search files
		  'info', // File info
		  'dim', // Image dimmensions
		  'resize', // Resize image
		  'url', // content URL
		  'ban', // Ban a user
		  'copy', // Copy a file/folder to another location
		  'cut', // Cut for file/folder
		  'edit', // Edit for files
		  'upload', // Upload A file
		  'download', // download A file
		  );

	  // Disabled file operations
	  $file_operation_disabled = array( 'url', 'info' );

	  // Allowed mime types
	  $mime_allowed = array(
		  'text',
		  'image',
		  'video',
		  'audio',
		  'application',
		  'model',
		  'chemical',
		  'x-conference',
		  'message',

		  );

	  $mime_denied = array();

	  $permittedPath = get_option('ftekdm_path_settings')['path-' . $userCap];

	  $opts = array(
		  'bind' => array(
			'*' => 'logger'
			),
		  'debug' => true,
		  'roots' => array(
			array(
			  'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
			  'path'          => '/var/www/html/wp-content/uploads/ftek-documents' . $permittedPath,                     // path to files (REQUIRED)
			  'URL'           => site_url() . '/wp-content/uploads/ftek-documents' . $permittedPath,                  // URL to files (REQUIRED)
			  'uploadDeny'    => $mime_denied,                // All Mimetypes not allowed to upload
			  'uploadAllow'   => $mime_allowed,               // Mimetype `image` and `text/plain` allowed to upload
			  'uploadOrder'   => array('allow', 'deny'),      // allowed Mimetype `image` and `text/plain` only
			  'accessControl' => 'access',
			  'disabled'      => $file_operations_disabled    // List of disabled operations
			  //~ 'attributes'
			  )
			)
		  );

	  $elFinder = new FM_EL_Finder();
	  $elFinder = $elFinder->connect($opts);
	  $elFinder->run();

	  die();
	}

  }
}
/**
 *
 * @function logger
 *
 * Logs file file manager actions
 *
 * */
if (!function_exists('logger'))   {
  function logger($cmd, $result, $args, $elfinder) {

	global $FileManager;

	$log = sprintf("[%s] %s: %s \n", date('r'), strtoupper($cmd), var_export($result, true));
	$logfile = $FileManager->upload_path . DS . 'log.txt';
	$dir = dirname($logfile);
	if (!is_dir($dir) && !mkdir($dir)) {
	  return;
	}
	if (($fp = fopen($logfile, 'a'))) {
	  fwrite($fp, $log);
	  fclose($fp);
	}
	return;

	foreach ($result as $key => $value) {
	  if (empty($value)) {
		continue;
	  }
	  $data = array();
	  if (in_array($key, array('error', 'warning'))) {
		array_push($data, implode(' ', $value));
	  } else {
		if (is_array($value)) { // changes made to files
		  foreach ($value as $file) {
			$filepath = (isset($file['realpath']) ? $file['realpath'] : $elfinder->realpath($file['hash']));
			array_push($data, $filepath);
		  }
		} else { // other value (ex. header)
		  array_push($data, $value);
		}
	  }
	  $log .= sprintf(' %s(%s)', $key, implode(', ', $data));
	}
	$log .= "\n";

	$logfile = $FileManager->upload_path . DS . 'log.txt';
	$dir = dirname($logfile);
	if (!is_dir($dir) && !mkdir($dir)) {
	  return;
	}
	if (($fp = fopen($logfile, 'a'))) {
	  fwrite($fp, $log);
	  fclose($fp);
	}
  }
}

global $FileManager;
$FileManager = new FM('Ftek Documents Manager');

define('FTEKDM_SETTINGS', 'ftekdm_settings');
define('FTEKDM_PATH_SETTINGS', 'ftekdm_path_settings');

add_action('admin_menu', 'ftekdm_admin_add_page');
function ftekdm_admin_add_page() {
	add_options_page(
		__('Ftek Document Manager Settings', 'ftekdm'),
		__('Ftek Document Manager', 'ftekdm'),
		'manage_options',
		FTEKDM_SETTINGS,
		'ftekdm_settings_page'
	);
}

function ftekdm_settings_page() {
	?>
		<div>
        <h2><?= __('Ftek Document Manager Settings', 'ftekcp')?></h2>
        <form action="options.php" method="post">

        <?php settings_fields(FTEKDM_SETTINGS); ?>
        <?php do_settings_sections(FTEKDM_SETTINGS); ?>
 
        <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
        </form></div>


	<?php
}





add_action('admin_init', 'ftekdm_admin_init');
function ftekdm_admin_init() {
	$ftekdm_perm_roles = ['manage_styret_files', 'finform_files', 'fnollk_files'];

	add_settings_section(
		FTEKDM_PATH_SETTINGS,
	  	__('Access to path', 'ftekdm'),
	  	function() {
			  echo __('Enter the path', 'ftekdm');
		},
		FTEKDM_SETTINGS
	);

	for ($i = 0; $i < count($ftekdm_perm_roles); $i++) {
		$role = $ftekdm_perm_roles[$i];

		add_settings_field(
			"ftekdm_role_$i",
			sprintf(__('Accessible path for %s','ftekdm'), $role),
			function() use($i) {
				ftekdm_field_roles($i);
			},
			FTEKDM_SETTINGS,
			FTEKDM_PATH_SETTINGS
		);
	}

	register_setting(FTEKDM_SETTINGS, FTEKDM_PATH_SETTINGS);
	
	
}

function ftekdm_field_roles($role_index) {
	$options = get_option(FTEKDM_PATH_SETTINGS);
	$path = $options['path-' . $role_index];
	$role = $options['role-' . $role_index];
	$name = FTEKDM_PATH_SETTINGS;

	echo __("Path", 'ftekdm');
	echo "<input type='text' id='ftekdm_role_{$role_index}_path' name='{$name}[path-$role_index]' value='$path'>";
	
	echo __("For role", 'ftekdm');
	echo "<input type='text' id='ftekdm_role_{$role_index}_role' name='{$name}[role-$role_index]' value='$role'>";

}