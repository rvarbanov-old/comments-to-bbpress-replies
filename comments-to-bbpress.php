<?php
/*
Script for converting comments to replies for bbPress

Next variables should be preset before every run.
*/

// DB connection
$connect = mysql_connect("localhost","user-name","password");
if (!$connect)
  {
  die('Could not connect: ' . mysql_error());
  }

mysql_select_db("database-name", $connect);


/************  Variables  ***************/
// OLD POST ID
$old_post_ID = 858;

// Forum ID for that topic
$forum_ID = 169;

// Topic ID
$topic_ID = 32504;

// Topic name
$topic_name = 'Topic Name';

// Topic slug
$topic_slug = 'topic-slug';

// Counter
$count_N = 0;


/************  MySQL queries  ***************/

// wp_comments here is the table with comments that you want to convert to replies
$query_old = 'SELECT *
  			FROM wp_comments
				WHERE comment_post_ID = ' . $old_post_ID . '
				ORDER BY comment_ID ASC';
				
//echo $query_old;	exit();			
				
$sql_old = mysql_query($query_old);

$num_rows = mysql_num_rows($sql_old);
//echo $num_rows;	exit();		

	
//echo "<pre>"; print_r (mysql_fetch_array($sql_old)); echo "</pre>"; exit();
while($result_old = mysql_fetch_array($sql_old)){
	//echo "<pre>"; print_r ($result_old); echo "</pre>"; //exit();



	$reply_count = '';
	// if $count_N != 0 -> $reply_count = -1, -2, -3 ... else do notthing
	$count_N != 0 ? $reply_count = '-'.$count_N : $reply_count = '';
	//echo ($count_N+1).$reply_count.'<br/>';
	//$count_N++;
	
	$comment_content = $result_old['comment_content'];
	$comment_content = strip_tags( addslashes($comment_content), '<a><img><br><strong>');
	
	// WP_POSTS <div><abbr><acronym><b><blockquote><cite><code><del><em><i><q><strike><sup><sub><u><pre>
	$query_p = 'INSERT INTO wp_posts
				(post_author,
				post_date,
				post_date_gmt,
				post_content,
				post_title,
				post_status,
				comment_status,
				ping_status,
				post_name,
				post_modified,
				post_modified_gmt,
				post_parent,
				guid,
				menu_order,
				post_type,
				comment_count)
				VALUES(1,"'
					.$result_old['comment_date'].'","'
					.$result_old['comment_date_gmt'].'","'
					.$comment_content.'",     
					"Reply To: ' . $topic_name.'", 
					"publish",
					"closed",
					"closed",
					"reply-to-'.$topic_slug.$reply_count.'","'
					.$result_old['comment_date'].'","'
					.$result_old['comment_date_gmt'].'","'
					.$topic_ID.'",
					"http://your-website.com/forums/reply/reply-to-' . $topic_slug . $reply_count.'","'
					.($count_N + 1).'",
					"reply",
					0)';
					
	//echo "<pre>"; echo $query_p; echo "</pre>"; //exit();
	// '<a><img><br><strong><div><abbr><acronym><b><blockquote><cite><code><del><em><i><q><strike><sup><sub><u><pre>'
	
	$insert_p = mysql_query($query_p);
	echo mysql_error();
				
	// wp_postmeta					
					
	$count_N++;
	$last_insert_ID = mysql_insert_id();
	
	//echo $last_insert_ID; exit();
	//$last_insert_ID = 95;





// wp_postmeta INSERTs 6 in total
							//echo 'INSERT INTO wp_postmeta (post_id,meta_key,meta_value)VALUES('.$last_insert_ID.',"_bbp_forum_id",'			.$forum_ID.')'; exit();
$insert_pm_ip			= mysql_query('INSERT INTO wp_postmeta (post_id,meta_key,meta_value)VALUES('.$last_insert_ID.',"_bbp_author_ip","'		.$result_old['comment_author_IP'].'")');
$insert_pm_topic_id	 	= mysql_query('INSERT INTO wp_postmeta (post_id,meta_key,meta_value)VALUES('.$last_insert_ID.',"_bbp_topic_id",'			.$topic_ID.')');
$insert_pm_forum_id		= mysql_query('INSERT INTO wp_postmeta (post_id,meta_key,meta_value)VALUES('.$last_insert_ID.',"_bbp_forum_id",'			.$forum_ID.')');
$insert_pm_website 		= mysql_query('INSERT INTO wp_postmeta (post_id,meta_key,meta_value)VALUES('.$last_insert_ID.',"_bbp_anonymous_website","'.$result_old['comment_author_url'].'")');
$insert_pm_email 		= mysql_query('INSERT INTO wp_postmeta (post_id,meta_key,meta_value)VALUES('.$last_insert_ID.',"_bbp_anonymous_email","'	.$result_old['comment_author_email'].'")');
$insert_pm_name 		= mysql_query('INSERT INTO wp_postmeta (post_id,meta_key,meta_value)VALUES('.$last_insert_ID.',"_bbp_anonymous_name","'	.$result_old['comment_author'].'")');
	
	
	
	
	
// wp_postmeta UPDATEs 5+5
// Topic
$update_pm_topic_bbp_last_reply_id 	  = mysql_query('UPDATE wp_postmeta SET meta_value = '.$last_insert_ID.			  ' WHERE post_id = '.$topic_ID.' AND meta_key = "_bbp_last_reply_id"');
$update_pm_topic_bbp_last_active_id   = mysql_query('UPDATE wp_postmeta SET meta_value = '.$last_insert_ID.			  ' WHERE post_id = '.$topic_ID.' AND meta_key = "_bbp_last_active_id"');
$update_pm_topic_bbp_last_active_time = mysql_query('UPDATE wp_postmeta SET meta_value = "'.$result_old['comment_date'].'" WHERE post_id = '.$topic_ID.' AND meta_key = "_bbp_last_active_time"');
$update_pm_topic_bbp_reply_count 	  = mysql_query('UPDATE wp_postmeta SET meta_value = '.$num_rows.				  ' WHERE post_id = '.$topic_ID.' AND meta_key = "_bbp_reply_count"');
// only for Topic
$update_pm_topic_bbp_bbp_voice_count  = mysql_query('UPDATE wp_postmeta SET meta_value = '.$num_rows.				  ' WHERE post_id = '.$topic_ID.' AND meta_key = "_bbp_voice_count"');
	
	
	
// Forum
$update_pm_forum_bbp_last_reply_id 	  = mysql_query('UPDATE wp_postmeta SET meta_value = '.$last_insert_ID.			  ' WHERE post_id = '.$forum_ID.' AND meta_key = "_bbp_last_reply_id"');
$update_pm_forum_bbp_last_active_id   = mysql_query('UPDATE wp_postmeta SET meta_value = '.$last_insert_ID.			  ' WHERE post_id = '.$forum_ID.' AND meta_key = "_bbp_last_active_id"');
$update_pm_forum_bbp_last_active_time = mysql_query('UPDATE wp_postmeta SET meta_value = "'.$result_old['comment_date'].'" WHERE post_id = '.$forum_ID.' AND meta_key = "_bbp_last_active_time"');
$update_pm_forum_bbp_reply_count 	  = mysql_query('UPDATE wp_postmeta SET meta_value = '.$num_rows.				  ' WHERE post_id = '.$forum_ID.' AND meta_key = "_bbp_reply_count"');
// only for Forum
$update_pm_forum_bbp_bbp_voice_count  = mysql_query('UPDATE wp_postmeta SET meta_value = '.$num_rows.				  ' WHERE post_id = '.$forum_ID.' AND meta_key = "_bbp_total_reply_count"');



echo mysql_error();

						
} // END while
?>

<h1>Converting comments to replies for bbPress finished.</h1>
<h2>for:</h2>
<ul>
	<li>old_post_ID: <?php echo $old_post_ID ?></li>
	<li>comments: <?php echo $num_rows ?></li>
	<li>forum_ID: <?php echo $forum_ID ?></li>
	<li>topic_ID: <?php echo $topic_ID ?></li>
	<li>topic_name: <?php echo $topic_name ?></li>
	<li>topic_slug: <?php echo $topic_slug ?></li>
</ul>

