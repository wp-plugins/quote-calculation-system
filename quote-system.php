<?php
/*
Plugin Name: Quote Calculation System
Plugin URI: 
Description: Save location and vehicle Calculate and calculate rate based on pickup and drop off location.
Version: 1.0
Author: Bikram
Author URI: http://bikramkaji.com.np/
License: GPL2
*/

$pluginsUrl =  plugin_dir_url(__FILE__);
function quote_my_script() {
	global $pluginsUrl;
	wp_enqueue_script( 'jquery' );	
	wp_register_script('quote_js', $pluginsUrl . 'js/quote.js', array(), '1.0' );
	wp_enqueue_script( 'quote_js' );	
	
	wp_register_style('quote_css', $pluginsUrl . 'css/quote.css', array(), '1.0' );
	wp_enqueue_style( 'quote_css' );	
}
add_action('init', 'quote_my_script');

add_action('admin_menu', 'quote_add_menu_pages');

function quote_add_menu_pages() {
	add_menu_page('Quote System', 'Quote System', 'manage_options', 'quote_system_page', 'quote_system_page_fn',plugins_url('/images/quote-icon.png', __FILE__) );
	add_submenu_page('quote_system_page', 'Manage Vehicle', 'Manage Vehicle', 'manage_options', 'quote_system_page', 'quote_system_page_fn');
	add_submenu_page('quote_system_page', 'Add Vehicle', 'Add Vehicle', 'manage_options', 'quote_system_add', 'quote_system_add_fn');
	
	add_submenu_page('quote_system_page', 'Manage Location', 'Manage Location', 'manage_options', 'quote_system_page_location', 'location_list');
	add_submenu_page('quote_system_page', 'Add Location', 'Add Location', 'manage_options', 'location_add', 'location_add');
	
	add_submenu_page('quote_system_page', 'Manage Location Rate', 'Manage Location Rate', 'manage_options', 'quote_system_page_location_rate', 'location_rate_list');
	add_submenu_page('quote_system_page', 'Add Location Rate', 'Add Location Rate', 'manage_options', 'location_rate_add', 'location_rate_add');

	add_submenu_page('quote_system_page', 'Options', 'Options', 'manage_options', 'quote_system_option', 'quote_system_extra_option');
	
	add_action( 'admin_init', 'register_quote_settings' );
	
}

function register_quote_settings() {
	register_setting( 'quote-settings-group', 'post-url' );
	register_setting( 'quote-settings-group', 'reservation-url' );
	register_setting( 'quote-settings-group', 'currency-symbol' );
	
}
function quote_system_extra_option() {

	$post_url = get_option('post-url');
	$reservation_url = get_option('reservation-url');
	$currencySymbol = get_option('currency-symbol');
	?>
	<div class="wrap">
	<h2>Quote Extra Setting</h2>
	<form method="post" action="options.php">
		<?php settings_fields( 'quote-settings-group' ); ?>
		<table class="form-table">
			<tr valign="top">
			<th scope="row">Post Url</th>
				<td><input type="text" name="post-url" id="post-url" class="regular-text" value="<?php echo $post_url?>" /></td>
			</tr>
			<tr valign="top">
			<th scope="row">Reservation Url</th>
				<td><input type="text" name="reservation-url" id="reservation-url" class="regular-text" value="<?php echo $reservation_url?>" /></td>
			</tr>
			<tr valign="top">
			<th scope="row">Currency Symbol</th>
				<td><input type="text" name="currency-symbol" id="currency-symbol" class="small-text" value="<?php echo $currencySymbol?>" /></td>
			</tr>
		</table>
		
		<p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
	</div>
	<?php 
}

function quote_db_install () {
   global $wpdb;
   global $quote_db_version;

   $table_name = $wpdb->prefix . "qt_system";
   $table_name2 = $wpdb->prefix . "qt_location";
   $table_name3 = $wpdb->prefix . "qt_location_rate";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name || $wpdb->get_var("show tables like '$table_name2'") != $table_name2) {
      
	$sql1 = "CREATE TABLE " . $table_name . " (
	`id` BIGINT(20) NOT NULL AUTO_INCREMENT, 
	`title` VARCHAR(255) NULL,
	`no_of_person` VARCHAR(255) NULL, 
	`lauggages` VARCHAR(255) NOT NULL, 
	`handbag` VARCHAR(255) NOT NULL DEFAULT '0', 
	`image_url` VARCHAR(255) NOT NULL, 
	`date_upload` VARCHAR(100) NULL, 
	PRIMARY KEY (`id`)) ENGINE = InnoDB";
	
	$sql2 = "CREATE TABLE " . $table_name2 . " (
	`id` BIGINT(20) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NULL,
	PRIMARY KEY (`id`)) ENGINE = InnoDB";
	
	$sql3 = "CREATE TABLE " . $table_name3 . " (
	`id` BIGINT(20) NOT NULL AUTO_INCREMENT,
	`from_location` VARCHAR(255) NULL,
	`to_location` VARCHAR(255) NOT NULL,
	`type_of_vechile` VARCHAR(255) NOT NULL,
	`one_way` VARCHAR(255) NOT NULL,
	`return_both` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`)) ENGINE = InnoDB";
	
	
     require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql1);
     	dbDelta($sql2);
     	dbDelta($sql3);
   }
}

register_activation_hook(__FILE__,'quote_db_install');
/*vechile section starts*/
if (isset($_GET['delete'])) {
	if ($_REQUEST['id'] != '')
	{
		$table_name = $wpdb->prefix . "qt_system";
		$upload_dir = wp_upload_dir();
		$image_file_path = $upload_dir['basedir'].'/';
		$sql = "SELECT * FROM ".$table_name." WHERE id =".$_REQUEST['id'];
		$vehicle_info = $wpdb->get_results($sql);
		if (!empty($vehicle_info))
		{ @unlink($image_file_path.$vehicle_info[0]->image_url);}
		$delete = "DELETE FROM ".$table_name." WHERE id = ".$_REQUEST['id']." LIMIT 1";
		$results = $wpdb->query( $delete );
		$msg = "Delete Successfully!!!"."<br />";
	}
}
if (isset($_POST['submit_button'])) {
	if ($_POST['action'] == 'update')
	{
		$err = "";
		$msg = "";
		$upload_dir = wp_upload_dir();
		$image_file_path = $upload_dir['basedir'].'/';
			if ($_FILES["image_file"]["name"] != "" ){
				if( 
					($_FILES["image_file"]["type"] == "image/gif")
				|| ($_FILES["image_file"]["type"] == "image/jpeg")
				|| ($_FILES["image_file"]["type"] == "image/pjpeg")
				|| ($_FILES["image_file"]["type"] == "image/png")
				&& ($_FILES["image_file"]["size"] < 1024*1024*1))
				  {
					if ($_FILES["image_file"]["error"] > 0)
					{
						$err .= "Return Code: " . $_FILES["image_file"]["error"] . "<br />";
					}
				  else
					{
					if (file_exists($image_file_path . $_FILES["image_file"]["name"]))
					  {
					  $err .= $_FILES["image_file"]["name"] . " already exists. ";
					  }
					else
					  {
						$image_file_name = time().'_'.$_FILES["image_file"]["name"];
						$fstatus = move_uploaded_file($_FILES["image_file"]["tmp_name"], $image_file_path . $image_file_name);
						if ($fstatus == true){
							$msg = "File Uploaded Successfully!!!"."<br />";
						}
					  }
					}
				  }
				else
				{
					$err .= "Invalid file type or max file size exceded" . "<br />";
				}
			}
			else
			{
				$err .= "Please input image file". "<br />";
			}// end if image file
		
		if ($err == '')
		{
			$table_name = $wpdb->prefix . "qt_system";
			$insert = "INSERT INTO " . $table_name .
			" (title, no_of_person, lauggages, image_url, handbag, date_upload) " .
			"VALUES ('" . 
			$wpdb->escape( $_REQUEST['title']) . "','" .
			$wpdb->escape( $_REQUEST['no_of_person']) . "','" .
			$wpdb->escape( $_REQUEST['lauggages']) . "','" . 
			$image_file_name . "'," . 
			$_REQUEST['handbag'] . ",'" .
			time() . "'" .
			")";
			$results = $wpdb->query( $insert );
			if (!$results)
				$err .= "Fail to update database" . "<br />";
			else
				$msg .= "Update Successfull!!!" . "<br />";
		}
	}// end if update
	
	if ( $_REQUEST['action'] == 'edit' and $_REQUEST['id'] != '' )
	{
		$err = "";
		$msg = "";

		$lauggages = $_REQUEST['lauggages'];
		
		$upload_dir = wp_upload_dir();
		$image_file_path = $upload_dir['basedir'].'/';
		$table_name = $wpdb->prefix . "qt_system";
		$sql = "SELECT * FROM ".$table_name." WHERE id =".$_REQUEST['id'];
		$vehicle_info = $wpdb->get_results($sql);
		$image_file_name = $vehicle_info[0]->image_url;
		$update = "";
		
		$imgExtArray = array('image/gif','image/jpeg','image/pjpeg','image/png');
		$type= 1;
		if ($_FILES["image_file"]["name"] != ""){
			if( in_array($_FILES["image_file"]["type"],$imgExtArray) && $_FILES["image_file"]["size"] <= 1024*1024*1 )
			  {
				if ($_FILES["image_file"]["error"] > 0)
				{
					$err .= "Return Code: " . $_FILES["image_file"]["error"] . "<br />";
				}
			  else
				{
				if (file_exists($image_file_path . $_FILES["image_file"]["name"]))
				  {
				  $err .= $_FILES["image_file"]["name"] . " already exists. ";
				  }
				else
				  {
					$image_file_name = time().'_'.$_FILES["image_file"]["name"];
					$fstatus = move_uploaded_file($_FILES["image_file"]["tmp_name"], $image_file_path . $image_file_name);
					
					if ($fstatus == true){
						$msg = "File Uploaded Successfully!!!".'<br />';
						@unlink($image_file_path.$vehicle_info[0]->image_url);
						$update = "UPDATE " . $table_name . " SET " . 
						"image_url='" .$image_file_name . "'" . 
						" WHERE id=" . $_REQUEST['id'];
						$results1 = $wpdb->query( $update );
					}
				  }
				}
			  }
			else
			{
				$err .= "Invalid file type or max file size exceded";
			}
		}
		
		$update = "UPDATE " . $table_name . " SET " . 
		"title='" .$wpdb->escape( $_POST['title']) . "'," . 
		"no_of_person='" . $_POST['no_of_person'] . "'," .
		"lauggages='" . $_POST['lauggages'] . "'," . 
		"handbag='" .$_POST['handbag'] . "'," . 
		"date_upload='" .time(). "'" .
		" WHERE id=" . $_POST['id'];
		if ($err == '')
		{
			$table_name = $wpdb->prefix . "qt_system";
			$results3 = $wpdb->query( $update );
			
			if (!$results3){
				$err .= "Update Fail!!!". "<br />";
			}
			else
			{
				$msg = "Update Successfull!!!". "<br />";
			}
		}
		
	} // end edit
}
/* Vechile section ends */


/* Location section processing ends */
if (isset($_GET['delete_location'])) {
	if ($_REQUEST['id'] != '')
	{
		$table_name = $wpdb->prefix . "qt_location";
		$delete = "DELETE FROM ".$table_name." WHERE id = ".$_REQUEST['id']." LIMIT 1";
		$results = $wpdb->query( $delete );
		$msg = "Location Delete Successfully!!!"."<br />";
	}
}
if (isset($_POST['submit_button_location'])) {
	if ($_POST['action'] == 'update')
	{
		$err = "";
		$msg = "";
		if ($_REQUEST['name'] ==''){
			$err .= "Please input name". "<br />";
		}// end if name

		if ($err == '')
		{
			$table_name = $wpdb->prefix . "qt_location";
			$insert = "INSERT INTO " . $table_name .
			" (name) " .
			"VALUES ('" . $_REQUEST['name'] . "')";
			$results = $wpdb->query( $insert );
			if (!$results)
				$err .= "Fail to update Location name in  database" . "<br />";
			else
				$msg .= "Location Name Update Successfull" . "<br />";
		}
	}// end if update

	if ( $_REQUEST['action'] == 'edit' and $_REQUEST['id'] != '' )
	{
		$err = "";
		$msg = "";
		$name = $_REQUEST['name'];
		$table_name = $wpdb->prefix . "qt_location";
		$sql = "SELECT * FROM ".$table_name." WHERE id =".$_REQUEST['id'];
		$location_info = $wpdb->get_results($sql);
		$update = "";
		if ($_REQUEST['name'] ==''){
			$err .= "Please input name". "<br />";
		}// end if image file

		$update = "UPDATE " . $table_name . " SET " .
				"name='" .$wpdb->escape( $_POST['name']) . "'" .
				" WHERE id=" . $_POST['id'];
		echo $update;
		if ($err == '')
		{
			$table_name = $wpdb->prefix . "qt_location";
			$results4 = $wpdb->query( $update );
				
			if (!$results4){
				$err .= "Location Update Fail!!!". "<br />";
			}
			else
			{
				$msg = "Location Update Successfull!!!". "<br />";
			}
		}

	} // end edit
}
/* Location section process ends */

/* Location rate processing starts */
if (isset($_GET['delete_location_rate'])) {
	if ($_REQUEST['id'] != '')
	{
		$table_name = $wpdb->prefix . "qt_location_rate";
		$delete = "DELETE FROM ".$table_name." WHERE id = ".$_REQUEST['id']." LIMIT 1";
		$results = $wpdb->query( $delete );
		$msg = "Location Rate Deleted Successfully!!!"."<br />";
	}
}
if (isset($_POST['submit_button_location_rate'])) {
	if ($_POST['action'] == 'update')
	{
		$err = "";
		$msg = "";
		if ($_REQUEST['from_location'] ==''){
			$err .= "Please input From location". "<br />";
		}// end if name

		if ($err == '')
		{
			$table_name = $wpdb->prefix . "qt_location_rate";
			$insert = "INSERT INTO " . $table_name .
			" (from_location, to_location, type_of_vechile, one_way, return_both) " .
			"VALUES ('" . $_REQUEST['from_location'] . "',
			'" .$_REQUEST['to_location'] . "',
			'" .$_REQUEST['type_of_vechile'] . "',
			'" .$_REQUEST['one_way'] . "',
			'" .$_REQUEST['return_both'] . "'" . 
			")";
			//echo $insert;
			$results = $wpdb->query( $insert );
			if (!$results)
				$err .= "Fail to update Location Rate in  database" . "<br />";
			else
				$msg .= "Location Rate Update Successfull" . "<br />";
		}
	}// end if update

	if ( $_REQUEST['action'] == 'edit' and $_REQUEST['id'] != '' )
	{
		$err = "";
		$msg = "";
		$name = $_REQUEST['name'];
		$table_name = $wpdb->prefix . "qt_location_rate";
		$sql = "SELECT * FROM ".$table_name." WHERE id =".$_REQUEST['id'];
		$location_info = $wpdb->get_results($sql);
		$update = "";
		if ($_REQUEST['from_location'] ==''){
			$err .= "Please input name". "<br />";
		}// end if image file

		$update = "UPDATE " . $table_name . " SET " .
				"from_location='" . $_POST['from_location'] . "'," .
				"to_location='" . $_POST['to_location'] . "'," .
				"type_of_vechile='" .$_POST['type_of_vechile'] . "'," .
				"one_way='" .$_POST['one_way'] . "'," .
				"return_both='" .$_POST['return_both'] . "'" .
				" WHERE id=" . $_POST['id'];
		
		//echo $update;
		if ($err == '')
		{
			$table_name = $wpdb->prefix . "qt_location";
			$results4 = $wpdb->query( $update );

			if (!$results4){
				$err .= "Location Rate Update Fail!!!". "<br />";
			}
			else
			{
				$msg = "Location Rate Update Successfull!!!". "<br />";
			}
		}

	} // end edit
}
/* Location rate processing ends */


/* Vechile adding section starts */
function quote_system_add_fn() {
	global $err,$msg;
	if (isset($_GET['mode'])) {
		if ( $_REQUEST['mode'] != '' and $_REQUEST['mode'] == 'edit' and  $_REQUEST['id'] != '' )
		{
			$page_title = 'Edit Vehicle';
			$uptxt = 'Upload Vehicle';
			global $wpdb;
			$table_name = $wpdb->prefix . "qt_system";
			//$image_file_path = "../wp-content/uploads/";
			$upload_dir = wp_upload_dir();
			$image_file_path = $upload_dir['baseurl'].'/';
			$sql = "SELECT * FROM ".$table_name." WHERE id =".$_REQUEST['id'];
			$vehicle_info = $wpdb->get_results($sql);
			if (!empty($vehicle_info))
			{
				$id = $vehicle_info[0]->id;
				$title = $vehicle_info[0]->title;
				$image_url = $image_file_path.$vehicle_info[0]->image_url;
				$no_of_person =$vehicle_info[0]->no_of_person;
				$lauggages =$vehicle_info[0]->lauggages;
				$handbag =$vehicle_info[0]->handbag;
			}
		}
	}
	else
	{
		$page_title = 'Add New Vehicle';
		$title = "";
		$image_url = "";
		$no_of_person = "";
		$lauggages = "";
		$handbag = "";
		$uptxt = 'Upload Vehicle';
	
	}
?>
<div class="wrap">
<?php if($msg!='' or $err!='') 	echo '<div id="message" class="updated fade">'. $msg.$err.'</div>'; ?>
<h2><?php echo $page_title;?></h2>
<form method="post" enctype="multipart/form-data" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <table class="form-table">
        <tr valign="top">
			<th scope="row">Vehicle Title</th>
			<td>
				<input type="text" name="title" id="title" class="regular-text" value="<?php echo $title?>" />
			</td>
        </tr>
        <tr valign="top">
			<th scope="row"><?php echo $uptxt;?></th>
			<td>
				<?php if (isset($_GET['mode'])) { ?>
					<br /><img src="<?php echo $image_url?>" border="0" width="100"  height="100" /><br />
				<?php } ?>
				<input type="file" name="image_file" id="image_file" value="" />
			</td>
			
			<tr valign="top">
				<th scope="row">No_of_person</th>
				<td><input type="text" name="no_of_person" id="no_of_person" class="small-text" value="<?php echo $no_of_person?>" /><br /></td>
        	</tr>
			<tr valign="top">
				<th scope="row">Lauggages</th>
				<td><input type="text" name="lauggages" id="lauggages" class="small-text" value="<?php echo $lauggages?>" /><br /></td>
        	</tr>
		
        	<tr valign="top">
				<th scope="row">Hand Bag</th>
				<td><input type="text" name="handbag" id="handbag" class="small-text" value="<?php echo $handbag?>" /></td>
        	</tr>
        </tr>
    </table>
	<?php if (isset($_GET['mode']) ) { ?>
	<input type="hidden" name="action" value="edit" />
	<input type="hidden" name="id" id="id" value="<?php echo $id;?>" />
	<?php } else {?>
	<input type="hidden" name="action" value="update" />
	<?php } ?>
    <p class="submit">
    <input type="submit" id="submit_button" name="submit_button" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
</form>
</div>
<?php 
} 
/* vechile adding section ends */

/* location adding starts */
function location_add() {
	global $err,$msg;
	if (isset($_GET['mode'])) {
		if ( $_REQUEST['mode'] != '' and $_REQUEST['mode'] == 'edit' and  $_REQUEST['id'] != '' )
		{
			$page_title = 'Edit Location';
			$uptxt = 'Upload Location';
			global $wpdb;
			$table_name = $wpdb->prefix . "qt_location";
			$sql = "SELECT * FROM ".$table_name." WHERE id =".$_REQUEST['id'];
			$location_info = $wpdb->get_results($sql);
			if (!empty($location_info))
			{
				$id = $location_info[0]->id;
				$location_name = $location_info[0]->name;
			}
		}
	}
	else
	{
		$page_title = 'Add New Location';
		$name = "";
	}
	?>
<div class="wrap">
<?php
if($msg!='' or $err!='')
	echo '<div id="message" class="updated fade">'. $msg.$err.'</div>';
?>
<h2><?php echo $page_title;?></h2>
<form method="post" enctype="multipart/form-data" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <table class="form-table">
        <tr valign="top">
			<th scope="row">Location Name</th>
			<td><input type="text" name="name" id="name" class="regular-text" value="<?php echo $location_name?>" /></td>
        </tr>
    </table>
	<?php if (isset($_GET['mode']) ) { ?>
	<input type="hidden" name="action" value="edit" />
	<input type="hidden" name="id" id="id" value="<?php echo $id;?>" />
	<?php } else {?>
	<input type="hidden" name="action" value="update" />
	<?php } ?>
    <p class="submit">
    <input type="submit" id="submit_button_location" name="submit_button_location" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
</form>
</div>
<?php 
}

function location_list() {
	global $wpdb;
	$table_name = $wpdb->prefix . "qt_location";
	$sql = "SELECT * FROM ".$table_name." WHERE 1 ";
	$location_info = $wpdb->get_results($sql);
	?>
	<div class="wrap">
	<h2>Manage Location</h2>
	<script type="text/javascript">
	function show_confirm_location(name, id)
	{
		var rpath1 = "";
		var rpath2 = "";
		var r=confirm('Are you confirm to delete "'+name+'"');
		if (r==true)
		{
			rpath1 = '<?php echo $_SERVER['REQUEST_URI']; ?>';
			rpath2 = '&delete_location=y&id='+id;
			window.location = rpath1+rpath2;
		}
	}
	</script>
		<table class="widefat page fixed" cellspacing="0">
			<thead>
			<tr valign="top">
				<th class="manage-column column-title" scope="col" width="200">Name</th>
				<th class="manage-column column-title" scope="col" width="50">Edit</th>
				<!-- <th class="manage-column column-title" scope="col" width="50">Delete</th> -->
			</tr>
			</thead>
			<tbody>
			<?php foreach($location_info as $lctinfo){ ?>
			<tr valign="top">
				<td>
					<?php echo $lctinfo->name;?>
				</td>
				<td>
					<a href="?page=location_add&mode=edit&id=<?php echo $lctinfo->id;?>"><strong>Edit</strong></a>
				</td>
				<td>
					<?php /*?><a onclick="show_confirm_location('<?php echo $lctinfo->name?>','<?php echo $lctinfo->id;?>');" href="#delete_location"><strong>Delete</strong></a><?php */?>
				</td>
			</tr>
			<?php }?>
			</tbody>
			<tfoot>
			<!-- <tr valign="top">
				<th class="manage-column column-title" scope="col" width="200">Title</th>
				<th class="manage-column column-title" scope="col" width="100">Vehicle</th>
				<th class="manage-column column-title" scope="col" width="80">Lauggagges</th>
				<th class="manage-column column-title" scope="col" width="80">Hand Bag</th>
				<th class="manage-column column-title" scope="col" width="50">Edit</th>
				<th class="manage-column column-title" scope="col" width="50">Delete</th>
			</tr> -->
			</tfoot>
		</table>
	</div>
	<?php
}

/* location adding ends */



/* Location Based Rate Ends */
function location_rate_add() {
	global $err,$msg;
	if (isset($_GET['mode'])) {
		if ( $_REQUEST['mode'] != '' and $_REQUEST['mode'] == 'edit' and  $_REQUEST['id'] != '' )
		{
			$page_title = 'Edit Location Rate';
			global $wpdb;
			$table_name = $wpdb->prefix . "qt_location_rate";
			$sql = "SELECT * FROM ".$table_name." WHERE id =".$_REQUEST['id'];
			$location_rate_info = $wpdb->get_results($sql);
			if (!empty($location_rate_info))
			{
				$id = $location_rate_info[0]->id;
				$from_location = $location_rate_info[0]->from_location;
				$to_location = $location_rate_info[0]->to_location;
				$type_of_vechile = $location_rate_info[0]->type_of_vechile;
				$one_way = $location_rate_info[0]->one_way;
				$return_both = $location_rate_info[0]->return_both;
			}
		}
	}
	else
	{
		$page_title = 'Add New Location Rate';
		$name = "";
	}
	?>
<div class="wrap">
<?php
if($msg!='' or $err!='')
	echo '<div id="message" class="updated fade">'. $msg.$err.'</div>';
?>
<h2><?php echo $page_title;?></h2>
<form method="post" enctype="multipart/form-data" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <table class="form-table">
        <tr valign="top">
			<th scope="row">From Location</th>
			<td><?php /*?><input type="text" name="from_location" id="from_location" class="regular-text" value="<?php echo $from_location?>" /><?php */ ?>
				<!-- creating dynamic dropdown list for location -->
				<?php 
					global $wpdb;
					$table_name = $wpdb->prefix . "qt_location";
					$sql = "SELECT * FROM ".$table_name." WHERE 1 ";
					$location_info = $wpdb->get_results($sql); ?>
					<select name=from_location><option value=''>Select From Location</option>
	    			<?php foreach($location_info as $lctinfo){ ?>
		    			<option value="<?php echo $lctinfo->id ?>" <?php if ($lctinfo->id == $from_location) echo 'selected="selected"' ?>><?php echo $lctinfo->name ?></option>
					<?php } ?>
			    	</select>
				<!-- ends -->
			</td>
        </tr>
        <tr valign="top">
			<th scope="row">To Location</th>
			<td><?php /*?><input type="text" name="to_location" id="to_location" class="regular-text" value="<?php echo $to_location?>" /><?php */?>
				<?php 
					global $wpdb;
					$table_name = $wpdb->prefix . "qt_location";
					$sql = "SELECT * FROM ".$table_name." WHERE 1 ";
					$location_info = $wpdb->get_results($sql); ?>
					<select name=to_location><option value=''>Select To Location</option>
	    			<?php foreach($location_info as $lctinfo){ ?>
		    			<option value="<?php echo $lctinfo->id ?>" <?php if ($lctinfo->id == $to_location) echo 'selected="selected"' ?>><?php echo $lctinfo->name ?></option>
					<?php } ?>
			    	</select>
			</td>
        </tr>
        <tr valign="top">
			<th scope="row">Type Of Vechile</th>
			<td><?php /*?><input type="text" name="type_of_vechile" id="type_of_vechile" class="regular-text" value="<?php echo $type_of_vechile?>" /><?php */ ?>
				<?php 
					global $wpdb;
					$table_name = $wpdb->prefix . "qt_system";
					$sql = "SELECT * FROM ".$table_name." WHERE 1 ";
					$vechileType = $wpdb->get_results($sql);
					/*echo "<select name=type_of_vechile><option value='.'>Select Type Of Vechile</option>";
	    			foreach($vechileType as $vType){
	    				echo "<option value='$vType->id'>".$vType->title."</option>";
					}
				    	echo "</select><p>";*/ ?>
					<select name=type_of_vechile><option value=''>Select Type Of Vechile</option>
		    			<?php foreach($vechileType as $vType){ ?>
			    			<option value="<?php echo $vType->id ?>" <?php if ($vType->id == $type_of_vechile) echo 'selected="selected"' ?>><?php echo $vType->title ?></option>
						<?php } ?>
			    	</select>
			</td>
        </tr>
		<tr valign="top">
			<th scope="row">One Way</th>
			<td><input type="text" name="one_way" id="one_way" class="regular-text" value="<?php echo $one_way?>" /></td>
        </tr>
        <tr valign="top">
			<th scope="row">Return</th>
			<td><input type="text" name="return_both" id="return_both" class="regular-text" value="<?php echo $return_both?>" /></td>
        </tr>
        
    </table>
	<?php if (isset($_GET['mode']) ) { ?>
	<input type="hidden" name="action" value="edit" />
	<input type="hidden" name="id" id="id" value="<?php echo $id;?>" />
	<?php } else {?>
	<input type="hidden" name="action" value="update" />
	<?php } ?>
    <p class="submit">
    <input type="submit" id="submit_button_location_rate" name="submit_button_location_rate" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
</form>
</div>
<?php 
}

function location_rate_list() {
	global $wpdb;
	$table_name = $wpdb->prefix . "qt_location_rate";
	$sql = "SELECT * FROM ".$table_name." WHERE 1 ";
	$location_rate_info = $wpdb->get_results($sql);
	?>
	<div class="wrap">
	<h2>Manage Location Rate</h2>
	<script type="text/javascript">
	function show_confirm_location_rate(from_location, to_location, id)
	{
		var rpath1 = "";
		var rpath2 = "";
		var r=confirm('Are you confirm to delete location rate from "'+from_location+'" to "'+to_location+'"');
		if (r==true)
		{
			rpath1 = '<?php echo $_SERVER['REQUEST_URI']; ?>';
			rpath2 = '&delete_location_rate=y&id='+id;
			window.location = rpath1+rpath2;
		}
	}
	</script>
		<table class="widefat page fixed" cellspacing="0">
			<thead>
			<tr valign="top">
				<th class="manage-column column-title" scope="col" width="100">From Location</th>
				<th class="manage-column column-title" scope="col" width="100">To Location</th>
				<th class="manage-column column-title" scope="col" width="100">Type of Vechile</th>
				<th class="manage-column column-title" scope="col" width="50">One Way</th>
				<th class="manage-column column-title" scope="col" width="50">Return</th>
				<th class="manage-column column-title" scope="col" width="50">Edit</th>
				<th class="manage-column column-title" scope="col" width="50">Delete</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach($location_rate_info as $lctrateinfo){ ?>
			<tr valign="top">
				<td>
					<?php 
						$table_name = $wpdb->prefix . "qt_location";
						$sql = "SELECT * FROM ".$table_name." WHERE id =".$lctrateinfo->from_location;
						$location_info = $wpdb->get_row($sql);
						$fromLocation = $location_info->name;
					?>
					<?php echo $fromLocation;?>
				</td>
				<td>
					<?php 
						$table_name = $wpdb->prefix . "qt_location";
						$sql = "SELECT * FROM ".$table_name." WHERE id =".$lctrateinfo->to_location;
						$location_info = $wpdb->get_row($sql);
						$toLocation = $location_info->name;
					?>
					<?php echo $toLocation;?>
				</td>
				<td>
					<?php 
						$table_name = $wpdb->prefix . "qt_system";
						$sql = "SELECT * FROM ".$table_name." WHERE id =".$lctrateinfo->type_of_vechile;
						$vehicle_info = $wpdb->get_row($sql);
						$vehicleName = $vehicle_info->title;
					?>
					<?php echo $vehicleName;?>
				</td>
				<td>
					<?php echo $lctrateinfo->one_way;?>
				</td>
				<td>
					<?php echo $lctrateinfo->return_both;?>
				</td>
				<td>
					<a href="?page=location_rate_add&mode=edit&id=<?php echo $lctrateinfo->id;?>"><strong>Edit</strong></a>
				</td>
				<td>
					<a onclick="show_confirm_location_rate('<?php echo $fromLocation;?>','<?php echo $toLocation;?>','<?php echo $lctrateinfo->id;?>');" href="#delete_location_rate"><strong>Delete</strong></a>
				</td>
			</tr>
			<?php }?>
			</tbody>
		</table>
	</div>
	<?php
}

/* Location based rate ends */



function quote_system_page_fn() {
	
	global $wpdb;
	
	$upload_dir = wp_upload_dir();
	$image_file_path = $upload_dir['baseurl'].'/';
	$table_name = $wpdb->prefix . "qt_system";
	$sql = "SELECT * FROM ".$table_name." WHERE 1 ";
	$vehicle_info = $wpdb->get_results($sql);
	?>
	<div class="wrap">
	<h2>Manage Vehicle</h2>
	<script type="text/javascript">
	function show_confirm(title, id)
	{
		var rpath1 = "";
		var rpath2 = "";
		var r=confirm('Are you confirm to delete "'+title+'"');
		if (r==true)
		{
			rpath1 = '<?php echo $_SERVER['REQUEST_URI']; ?>';
			rpath2 = '&delete=y&id='+id;
			window.location = rpath1+rpath2;
		}
	}
	</script>
	
		<table class="widefat page fixed" cellspacing="0">
			<thead>
			<tr valign="top">
				<th class="manage-column column-title" scope="col" width="200">Title</th>
				<th class="manage-column column-title" scope="col" width="100">Vehicle</th>
				<th class="manage-column column-title" scope="col" width="100">No of Person</th>
				<th class="manage-column column-title" scope="col" width="80">Lauggagges</th>
				<th class="manage-column column-title" scope="col" width="80">Hand Bag</th>
				<th class="manage-column column-title" scope="col" width="50">Edit</th>
				<th class="manage-column column-title" scope="col" width="50">Delete</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach($vehicle_info as $vdoinfo){ ?>
			<tr valign="top">
				<td>
					<?php echo $vdoinfo->title;?>
				</td>
				<td>
					<img src="<?php echo $image_file_path.$vdoinfo->image_url;?>" border="0" width="100" height="100" />
				</td>
				<td>
					<?php echo $vdoinfo->no_of_person;?>
				</td>
				<td>
					<?php echo $vdoinfo->lauggages;?>
				</td>
				<td>
					<?php echo $vdoinfo->handbag;?>
				</td>
				<td>
					<a href="?page=quote_system_add&mode=edit&id=<?php echo $vdoinfo->id;?>"><strong>Edit</strong></a>
				</td>
				<td>
					<a onclick="show_confirm('<?php echo $vdoinfo->title?>','<?php echo $vdoinfo->id;?>');" href="#delete"><strong>Delete</strong></a>
				</td>
			</tr>
			<?php }?>
			</tbody>
			<tfoot>
			<!-- <tr valign="top">
				<th class="manage-column column-title" scope="col" width="200">Title</th>
				<th class="manage-column column-title" scope="col" width="100">Vehicle</th>
				<th class="manage-column column-title" scope="col" width="80">Lauggagges</th>
				<th class="manage-column column-title" scope="col" width="80">Hand Bag</th>
				<th class="manage-column column-title" scope="col" width="50">Edit</th>
				<th class="manage-column column-title" scope="col" width="50">Delete</th>
			</tr> -->
			</tfoot>
		</table>
	</div>
	<?php
}


function qt_system() {
	$postUrl = get_option('post-url');
	?>
	
	<form method="post" enctype="multipart/form-data" action="<?php echo str_replace( '%7E', '~', $postUrl); ?>" onsubmit="return validateForm()" name="quote-form" class="quote-form">
	
	<div class="label"> Select Pickup Address: </div>
	<div>
		<?php 
		global $wpdb;
		$table_name = $wpdb->prefix . "qt_location";
		$sql = "SELECT * FROM ".$table_name." WHERE 1 ";
		$location_info = $wpdb->get_results($sql); ?>
			<select name=from_location><option value=''>Select Pickup Location</option>
    		<?php foreach($location_info as $lctinfo){ ?>
    			<option value="<?php echo $lctinfo->id ?>"><?php echo $lctinfo->name ?></option>
			<?php } ?>
    		</select>
	</div>
	
	<div class="label">Select Destination Address:</div>
	<div>
		<?php 
		global $wpdb;
		$table_name = $wpdb->prefix . "qt_location";
		$sql = "SELECT * FROM ".$table_name." WHERE 1 ";
		$location_info = $wpdb->get_results($sql); ?>
			<select name=to_location><option value=''>Select Destination</option>
			<?php foreach($location_info as $lctinfo){ ?>
				<option value="<?php echo $lctinfo->id ?>"><?php echo $lctinfo->name ?></option>
			<?php } ?>
	    	</select>
	</div>
	
	<div>Passengers:</div>
	<div>
		<select style="width:80px;" id="passengers" name="passengers">
			<option value="1" selected="selected">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="5">5</option>
			<option value="6">6</option>
			<option value="7">7</option>
			<option value="8">8</option>
		</select>
	</div>
	<input type="submit" value="Calculate" class="quote_btn">	 
	</form>
	
	
	<?php 
	$out = ob_get_contents();
	ob_end_clean();
	return $out;
}

class quote_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'quote_widget', // Base ID
			'Quote System', // Name
			array( 'description' => __( 'Quote System Widget for sidebar' ) ) // Args
		);
	}

	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		echo qt_system();
		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}

} // class quote_Widget
add_action( 'widgets_init', create_function( '', 'register_widget( "quote_Widget" );' ) );

add_shortcode('quote-system', 'qt_system');

/********************************************************************************************************/
add_shortcode('quote-system-processing', 'qt_system_processing');
function qt_system_processing() {
	
	//echo "<pre>";
	//print_r($_POST);
	global $wpdb;
	$pluginsUrl =  plugin_dir_url(__FILE__);
	$currencySymbol = get_option('currency-symbol');//"$ ";
	$reservationUrl = get_option('reservation-url');
	
	$fromLocation = $_POST['from_location'];
	$toLocation = $_POST['to_location'];
	$passengerNumber = $_POST['passengers'];
	
	$table_name = $wpdb->prefix . "qt_location";
	$sql = "SELECT * FROM ".$table_name." WHERE id =".$fromLocation;
	$from_location = $wpdb->get_row($sql);
	$fromLocationLabel = $from_location->name;
	
	$sql = "SELECT * FROM ".$table_name." WHERE id =".$toLocation;
	$to_location = $wpdb->get_row($sql);
	$toLocationLabel = $to_location->name;
	
	$table_name = $wpdb->prefix . "qt_location_rate";
	$vechileTable = $wpdb->prefix . "qt_system";
	$sql = "SELECT key1.*, key2.title, key2.no_of_person, key2.lauggages, key2.image_url, key2.handbag FROM ".$table_name." key1
	INNER JOIN ". $vechileTable ." key2
	ON key2.id = key1.type_of_vechile
	WHERE key1.from_location=".$fromLocation. " and key1.to_location=" .$toLocation.
	" AND key2.no_of_person >=" .$passengerNumber
	;
	$location_rate_info = $wpdb->get_results($sql);
	?>
	
	<div class="quote-calculation">
		<div class="quote-label"><b>From :  </b><?php echo $fromLocationLabel;?></div>
		<div class="quote-label"><b>To :  </b><?php echo $toLocationLabel;?></div>
		<div class="clear"></div>
		<?php foreach($location_rate_info as $locationDisplay) { ?>
		
		<?php 
			$table_name = $wpdb->prefix . "qt_system";
			$sql = "SELECT * FROM ".$table_name." WHERE id =".$locationDisplay->type_of_vechile;
			$vehicle_info = $wpdb->get_row($sql);
			//for images of vechile
			$upload_dir = wp_upload_dir();
			$image_file_path = $upload_dir['baseurl'];
			$image_url = $image_file_path.'/'.$vehicle_info->image_url;
		?>
		
		<div class="quote-calculation-form">            
			<form name="quotefrm" method="post" action="<?php echo str_replace( '%7E', '~', $reservationUrl); ?>">
				<div class="vechile-image"><img width="146" src="<?php echo $image_url?>"></div>
				<div class="other-details">
					<div class="vechile-name"><?php echo $vehicle_info->title;?></div>
					<div class="vechile-capacity">
						<div class="men-detail"><div class="people-icon"></div> X <?php echo $vehicle_info->no_of_person?></div>
						<div class="lauggages-detail"><div class="luggages-icon"></div> X <?php echo $vehicle_info->lauggages; ?></div>
						<div class="hand-bag-detail"><div class="handbag-icon"></div> X <?php echo $vehicle_info->handbag; ?></div>
					</div>
					<div class="clear"></div>
					<div class="travel-type">								
						<h2 align="left" class="ttype"><input type="radio" checked="checked" value="<?php echo $locationDisplay->one_way;?>" name="journeytype"> One Way: <?php echo $currencySymbol.$locationDisplay->one_way;?></h2>
						<h2 align="left" class="ttype"><input type="radio" value="<?php echo $locationDisplay->return_both;?>" name="journeytype"> Return: <?php echo $currencySymbol.$locationDisplay->return_both;?></h2>
					</div>
		            <input type="hidden" value="<?php echo $vehicle_info->title;?>" name="vehicleName">
		            <input type="hidden" value="<?php echo $currencySymbol;?>" name="currencySymbol" >
		            <input type="hidden" value="<?php echo $locationDisplay->id;?>" name="locationRateId">
		            <input type="hidden" value="<?php echo $from_location->id;?>" name="from_location">
		            <input type="hidden" value="<?php echo $to_location->id;?>" name="to_location">
		            <input type="hidden" value="<?php echo $fromLocationLabel;?>" name="from_location_label">
		            <input type="hidden" value="<?php echo $toLocationLabel;?>" name="to_location_label">
		            
		            <div><input type="submit" value="Book Now" name="Book Now" class="quote_btn"></div>
				</div>
			</form>
		</div>
		<?php } ?>
	</div>
	<!-- Quote Calculation ends here -->

<?php 

}
