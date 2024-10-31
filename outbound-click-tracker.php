<?PHP

/*
Plugin Name: Outbound Click Tracker
Plugin URI: http://www.BlogsEye.com/outbound-click-tracker/
Description: Tracks clicks on outbound links. Find out where your readers are going.
Version: 1.4
Author: Keith P. Graham
Author URI: http://www.BlogsEye.com/

This software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/


/************************************************************
*	kpg_outbound_click_admin_menu()
*	Adds the admin menu
*************************************************************/
function kpg_outbound_click_admin_menu() {
   add_options_page('Outbound Click Tracker', 'Outbound Click Tracker', 'manage_options', 'outbound-click-tracker/outbound-click-tracker-options.php');
}
 function kpg_outbound_click_trackit() {
 // this is the tracker itself - insert a click into the db
 
	// check to see if we are turned off
	
	$updateData=get_option('kpg_obclicktracker_options');
	if ($updateData==null) $updateData=array();
	$kpg_obl_track=$updateData['track'];
	if ($kpg_obl_track==null) $kpg_obl_track='N';
	if ($kpg_obl_track!='Y') return;

 	global $wpdb; // useful db functions

	// rss check
	if (array_key_exists('oblt_rss',$_GET)) {
		$counter=$_GET['oblt_rss'];
		if ($counter<1) $counter=5;
		// this returns the list 
		$table_name = $wpdb->prefix . "obk_tracker";
		$sql="select count(*) as CNT, URL from $table_name where 
			DATE_ADD(NOW(), INTERVAL -$counter DAY) < time group by URL order by CNT DESC";	
		$rows=$wpdb->get_results($sql);
		$lim=0;
		header("Content-type: application/xml");
		$s= '<';
		$s.= "?xml version='1.0' encoding='windows-1252'?";
		$s.=  '>';
		echo $s;
		$updated = date('Y-m-d\TH:i:s\Z');
		$fid=time();
		$now = date("D, d M Y H:i:s T");
		?><rss version="2.0">
	<channel>
		<title>Outbound link Tracker Stats (Last <?php echo $counter; ?> days)</title>
		<link><?php echo get_bloginfo('url'); ?>?oblt_rss=<?php echo $counter; ?></link>
		<description><?php echo get_bloginfo('name'); ?></description>
    <?php
		foreach($rows as $row) {
			$u=$row->URL;
			$uu=$u;
			$c=$row->CNT;
			if (strlen($u)>48) $u=substr($u,0,48).'...';
			//echo("<li>($c) <a href=\"$uu\">$u</a></li>");
		?><item>
			<title><?php echo $u; ?></title>
			<link><?php echo $uu; ?></link>
			<pubDate><?php echo $now; ?></pubDate>
			<description><?php echo $uu; ?>(<?php echo $c; ?> clicks)</description>
			<guid isPermaLink="false"><?php echo $uu; ?></guid>
		</item>


<?php			
		
			$lim++;
			if ($lim>32) break;
		}
	?></channel>
</rss><?php

		exit();
	
	}
	
	
	
	if (!array_key_exists('kpg_link',$_GET)) return;
		// we are in the money
	$link=$_GET['kpg_link'];
	if ($link==null||$link=='') return;
	$page=$_GET['kpg_page'];
	if ($page==null||$page=='') return;
	$ref=$_GET['kpg_txt'];
	if ($ref==null) $ref='';
	$ref=stripslashes($ref);
	$ref=strip_tags($ref);
	$ref=$wpdb->escape($ref);
	$link=$wpdb->escape($link);
	$page=$wpdb->escape($page);
	$ip=$_SERVER['REMOTE_ADDR'];
	// we have data!
	// need to update this data
	// coming in is kpg_link and kpg_page

	$kpg_obl_track='Y';
	$table_name = $wpdb->prefix . "obk_tracker";
	// if the table doesn't exist then create it
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE " . $table_name . " (
			  time DATETIME NOT NULL,
			  page VARCHAR(150) NOT NULL,
			  url VARCHAR(150) NOT NULL,
			  referer VARCHAR(150),
			  userip VARCHAR(20)
			);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	// check to see if the older version of the db without the ip and ref columns exists
	if($wpdb->get_var("SHOW COLUMNS FROM '$table_name' like 'userip'") != 'userip') {
		$sql="ALTER TABLE " . $table_name . " add referer VARCHAR(255),add userip VARCHAR(20)";
		$wpdb->query($sql);
		//echo $sql;
	}
	$sql="insert into $table_name (time,page,url,userip,referer) values (now(),'$page','$link','$ip','$ref')";
	
	$wpdb->query($sql);
	
	exit();
}
/************************************************************
* 	kpg_outbound_click_uninstall()
*	Cleans up the mess when the plugin is uninstalled
*
*************************************************************/

function kpg_outbound_click_uninstall() {
	if(!current_user_can('manage_options')) {
		die('Access Denied');
	}
	global $wpdb;
	$table_name = $wpdb->prefix . "obk_tracker";
	$sql="drop table $table_name";
	$wpdb->query($sql);
	delete_option('kpg_obclicktracker_options'); 
	return;
}

/************************************************************
* 	kpg_outbound_click_tracker()
*	Shows the javascript in the footer so that the links can be snagged
*
*************************************************************/
function kpg_outbound_click_tracker() {
	$f=WP_PLUGIN_URL.'/'.plugin_basename(__FILE__);
	$updateData=get_option('kpg_obclicktracker_options');
	$kpg_obl_track=$updateData['track'];
	if ($kpg_obl_track==null) $kpg_obl_track='';
	if ($kpg_obl_track!='N') $kpg_obl_track='Y';
	$kpg_obl_new=$updateData['new'];
	if ($kpg_obl_new==null) $kpg_obl_new='';
	if ($kpg_obl_new!='Y') $kpg_obl_new='N';
	if ($kpg_obl_new=='N'&&$kpg_obl_track=='N') return;
	$home=get_bloginfo('url');
?>
<script language="javascript" type="text/javascript">
	// <!--
	// javascript functions added by outbound-link-tracker
	function kpg_oct_action(event) {
		try {
			var b=document.getElementsByTagName("a");
			for (var i = 0; i < b.length; i++) {
				if (b[i].hostname != location.hostname) { // checks to see if link is an external link
	<?php
	// need to check to see if we are pushing outbound links to new window
	if ($kpg_obl_new=='Y') { // if we are opening links in new window
		?>
					b[i].target="_blank";
	<?php
	} // end if we are tracking links in new window
	if ($kpg_obl_track=='Y') {  // if we are tracking clicks
	
	?>
					if (b[i].addEventListener) {
						b[i].addEventListener("click", getExternalLink, false);
					} else {
						if (b[i].attachEvent) {
							b[i].attachEvent("onclick", getExternalLink);
						}
					}<?php } // end if we are tracking clicks
					?>
				}
			}
		} catch (ee) {}
	} // end of function that adds events and alters links
	<?php
	if ($kpg_obl_track=='Y') {  // if we are tracking link
	
	?>
	function getExternalLink(evnt) {
		try {
			var e = evnt.srcElement;
			if (e) {
				while (e!=null&&e.tagName != "A") {
					e = e.parentNode;
				}
			} else {
				e=this;
			}
			if (e.protocol=="http:") {
				var l=escape(e.href);
				var p=escape(document.location);
				var r=escape(e.innerHTML);
				var iname="<?php echo $home; ?>?kpg_link="+l+"&kpg_page="+p+"&kpg_txt="+r;
				var d=new Image();
				d.src=iname;
				var t=new Date().getTime();
				t=t+500;
				while (new Date().getTime()<t) {}
				return;
			} 
		} catch (ee) {
		}
		return;
	}

<?php } ?>

	// set the onload event
	if (document.addEventListener) {
		document.addEventListener("DOMContentLoaded", function(event) { kpg_oct_action(event); }, false);
	} else if (window.attachEvent) {
		window.attachEvent("onload", function(event) { kpg_oct_action(event); });
	} else {
		var oldFunc = window.onload;
		window.onload = function() {
			if (oldFunc) {
				oldFunc();
			}
				kpg_oct_action('load');
			};
	}
	 


	// -->
	</script>
<?php

}
  // Plugin added to Wordpress plugin architecture
	add_action( 'wp_footer', 'kpg_outbound_click_tracker' );
	add_action('init','kpg_outbound_click_trackit');
	// add the the options to the admin menu
	add_action('admin_menu', 'kpg_outbound_click_admin_menu');
if ( function_exists('register_uninstall_hook') )
	    register_uninstall_hook(__FILE__, 'kpg_outbound_click_uninstall');
	 
?>