<?php
/*
	WordPress 2.8 Plugin: Outbound Click Tracker 1.4	
	Copyright (c) 2009 Keith P. Graham 	
 
	File Information:  					
	- outbound-click-tracker				
	- wp-content/plugins/outbound-click-tracker/outbound-click-tracker-options.php 	
 
*/


// just a quick check to keep out the riff-raff
if(!current_user_can('manage_options')) {
	die('Access Denied');
}
global $wpdb; // useful db functions

$kpg_obl_track='Y';
$table_name = $wpdb->prefix . "obk_tracker";

// see if we are getting anything from the admin update post.
if(!empty($_POST['Submit'])) { // we have a post - need to change some options

	$kpg_obl_reset = $_POST['kpg_obl_reset'];
	if ($kpg_obl_reset==null) $kpg_obl_reset='';
	if ($kpg_obl_reset!='Y') $kpg_obl_reset='N';
	if ($kpg_obl_reset=='Y') {
		// when resetting  or resorting we don't set the variables.
		$sql="delete from $table_name";
		$wpdb->query($sql);
	}
	$kpg_obl_track = $_POST['kpg_obl_track'];
	if ($kpg_obl_track==null) $kpg_obl_track='';
	if ($kpg_obl_track!='Y') $kpg_obl_track='N';
	$kpg_obl_new = $_POST['kpg_obl_new'];
	if ($kpg_obl_new==null) $kpg_obl_new='';
	if ($kpg_obl_new!='Y') $kpg_obl_new='N';
	// out in data array
	$updateData=get_option('kpg_obclicktracker_options');
	if ($updateData ==null) $updateData = array();
	$updateData['track']=$kpg_obl_track;
	$updateData['new']=$kpg_obl_new;
	// save the results in repository
	update_option('kpg_obclicktracker_options', $updateData);
	// done

}	
	// check for an uninstall going on
	$mode = trim($_GET['mode']);
	if ($mode=='end-UNINSTALL') {
	
	} else {
	$table_name = $wpdb->prefix . "obk_tracker";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		$sql = "CREATE TABLE " . $table_name . " (
			  time DATETIME NOT NULL,
			  page VARCHAR(150) NOT NULL,
			  url VARCHAR(150) NOT NULL,
			  referer VARCHAR(255),
			  userip VARCHAR(20)
			);";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	// not deactivating and we have finished with any updates or housekeeping - get the variables and show them on the form
	$updateData=get_option('kpg_obclicktracker_options');
	if ($updateData==null) $updateData=array();
	$kpg_obl_track=$updateData['track'];
	if ($kpg_obl_track==null) $kpg_obl_track='';
	if ($kpg_obl_track!='N') $kpg_obl_track='Y';
	$kpg_obl_new=$updateData['new'];
	if ($kpg_obl_new==null) $kpg_obl_new='';
	if ($kpg_obl_new!='Y') $kpg_obl_new='N';
?>

<div class="wrap">
<h2>Outbound-click-Tracker Options </h2>
<p>The Outbound Click Tracker can detect and record when a user clicks on a link that leaves your domain. It cannot detect links that are embedded in iframes, such as Google Adsense. It records the time of the click, the page where the link was clicked and the target.</p>
<p>Use this form to enable or disable outbound click.  </p>
<form method="post" action="" name="kpg_obl">
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="Submit,kpg_obl_reset,kpg_obl_track" />
<input type="hidden" name="kpg_obl_reset" value="n" />

<?php wp_nonce_field('update-options'); ?>
<table class="form-table">
<tr valign="middle">
<td>Enable Outbound Click Tracking </td>
<td><input name="kpg_obl_track" type="checkbox" value="Y" <?php if ($kpg_obl_track=='Y') {?> checked <?php } ?>/></td>
<td>Unclick to disable the outbound click tracker without uninstalling it.</td>
</tr>
<tr valign="middle">
  <td>Open Outbound link in new Window</td>
  <td><input name="kpg_obl_new" type="checkbox" value="Y" <?php if ($kpg_obl_new=='Y') {?> checked <?php } ?>/></td>
  <td>This uses javascript to force external links to open in new window. It only works for links accessible by javascript and may not work for all links.</td>
</tr>
<tr valign="middle">
  <td>Delete Outbound Click History</td>
  <td><input name="kpg_obl_reset" type="checkbox" value="Y"/></td>
  <td>Warning: you can't undo this. Once gone the records are gone forever. The outbound click tracker history can grow large. You can delete all current records by checking this.</td>
</tr>
<tr valign="middle">
</table>

<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>


</form>
<p>
  <?php
// check to see if there are any good things happening
$table_name = $wpdb->prefix . "obk_tracker";

$sql="select COUNT(*) as CNT from $table_name";
$q=$wpdb->get_results($sql); 
$cnt=0;
foreach($q as $CNT) $cnt=$CNT->CNT;
//print_r($q);

if ($cnt==0) {
?>
</p>
<h3>No data to display</h3
><?php
} else {
// get the last days average clicks
?>
<h3>List of most popular outbound click in last 5 days</h3>

<?php
	$sql="select count(*) as CNT, URL from $table_name where 
		DATE_ADD(NOW(), INTERVAL -5 DAY) < time group by URL order by CNT DESC";	
	$rows=$wpdb->get_results($sql);
	$lim=0;
	echo "<ol>";
	 foreach($rows as $row) {
		$u=$row->URL;
		$uu=$u;
		$c=$row->CNT;
		if (strlen($u)>48) $u=substr($u,0,48).'...';
		echo("<li>($c) <a href=\"$uu\">$u</a></li>");
		$lim++;
		if ($lim>9) break;
	}
	echo "</ol>";

// now set up the stuff for a list of links
	$somany=20; // how many records per page
	// we have to limit so many records per page so we need paging
	$start_row=$_GET['kpg_ob_start_row'];
	if ($start_row==null||$start_row>=$cnt||$start_row<=0) $start_row=0;
	$next_row=$start_row+$somany;
	if ($next_row>$cnt) $next_row=$cnt;
	$nnext_row=$next_row+$somany;
	if ($nnext_row>$cnt) $nnext_row=$cnt; // count of next records
	$prev_row=$start_row-$somany;
	if ($prev_row<0) $prev_row=0;
	$nprev_row=$prev_row+$somany;
	if ($nprev_row>$cnt) $nprev_row=$cnt;
?>

<h3>Outbound Clicks</h3>
Showing <?php echo $next_row-$start_row; ?> out of <?php echo $cnt; ?> records
<table style="background-color:#CCCCCC;" cellspacing="3">
<tr style="background-color:#CCFFFF">
<td align="center">Click Date</td>
<td align="center">Page with Clicked Link</td>
<td align="center">Target Page</td>
<td align="center">IP Address</td>
<td align="center">Link Text</td>
</tr>
<?php

	$sql="select * from $table_name order by time desc LIMIT $start_row, $somany";
	$rows=$wpdb->get_results($sql); 
  
  foreach($rows as $row) {
	$d=mysql2date('m/d/Y H:i:s', $row->time, $translate = true);
	$u=$row->url;
	$ip=$row->userip;
	if ($ip==null) $ip='&nbsp;';
	$ref=$row->referer;
	if ($ref==null) $ref='&nbsp;';
	$uu=$u;
	if (strlen($u)>48) {
		$u=substr($u,0,48).'...';
	}
	$p=$row->page;
	$pp=$p;
	if (strlen($p)>48) $p=substr($p,0,48).'...';
  
?> 
<tr style="background-color:#FFFFFF">
<td style="padding:4px;" align="center" bgcolor="#FFFFFF"><?php echo $d; ?></td>
<td style="padding:4px;" align="center" bgcolor="#FFFFFF"><a href="<?php echo $pp; ?>"  title="<?php echo $pp; ?>"><?php echo $p; ?></a></td>
<td style="padding:4px;" align="center" bgcolor="#FFFFFF" ><a href="<?php echo $uu; ?>"  title="<?php echo $uu; ?>"><?php echo $u; ?></a></td>
<td style="padding:4px;" align="center" bgcolor="#FFFFFF" ><?php echo $ip; ?>
</td>
<td style="padding:4px;" align="center" bgcolor="#FFFFFF" ><?php echo $ref; ?>
</td>
</tr>
  <?php } ?>
</table>
<?php 
   if ($prev_row<$nprev_row&&$prev_row<$start_row) {
	echo "<a href=\"?page=outbound-click-tracker/outbound-click-tracker-options.php&kpg_ob_start_row=$prev_row\">Previous Records $prev_row to $nprev_row</a><br/>";
   }
   if ($nnext_row>$next_row&&$next_row>$star_row) {
	echo "<a href=\"?page=outbound-click-tracker/outbound-click-tracker-options.php&kpg_ob_start_row=$next_row\">Next Records $next_row to $nnext_row</a><br/>";
   }
?>
  <?php } ?>
 <hr/>
You can access the outbound click data by rss: <a href="<?php echo get_bloginfo('url'); ?>?oblt_rss=5"><?php echo get_bloginfo('url'); ?>?oblt_rss=5</a>
 <hr/> 
  <h3>Other Plugins:</h3>
  <p>1) <a href="http://wordpress.org/extend/plugins/permalink-finder/">Permalink Finder Plugin</a> is a useful when moving from another blog platform or static site to Wordpress. It detects 404 file not found errors and redirects them to the best matching post. It can automatically correct mistakes or changes in permalinks and fix them. </p>
  <p>2) <a href="http://wordpress.org/extend/plugins/open-in-new-window-plugin/">Open in New Window Plugin</a>. This inserts JavaScript onto each page to for links external to the site to open in a new window.</p>
  <h3>Donations:</h3>
  <p>If you find this plugin useful, please visit my websites and, if appropriate, add a link to one on your blog: <br/>
<a href="http://www.cthreepo.com/">Resources for Science Fiction Writers</a><br/>
<a href="http://www.freenameastar.com/">Name a real star for free</a><br/>
<a href="http://www.jt30.com/">Amplified Blues Harmonica</a><br/>
or visit the <a href="https://online.nwf.org/site/Donation2?df_id=6620&6620.donation=form1">National Wildlife Federation</a>.<br/>

</p>
<p>Version 1.4 April 5, 2010 </p>
</div>

<p>
  <?php } ?>
</p>
<p>&nbsp;</p>

</p>
