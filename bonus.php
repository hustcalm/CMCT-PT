<?php
require_once("include/bittorrent.php");
dbconn();
loggedinorreturn();
require_once(get_langfile_path("",true));

if ($_GET['id'])
	stderr("Party is over!", "This trick doesn't work anymore. You need to click the button!");

$type = $_POST['type'];
if (!$type) $type = 'torrent';
if (!in_array($type, array('torrent', 'post')))
	stderr("Error", "Invalid type.");

$userid = 0 + $CURUSER["id"];
$torrentid = $postid = 0 + $_POST["id"];
$bonus = 0 + $_POST["bonus"];
if ($bonus <= 0)
	stderr("Error", "Bonus must be larger than 0.");
if ($bonus > $CURUSER["seedbonus"])
	stderr("Error", "You don't have enough bonus.");

if ($type == 'torrent')
{
	$tsql = sql_query("SELECT owner FROM torrents where id=$torrentid");
	$arr = mysql_fetch_array($tsql);
	if (!$arr)
		stderr("Error", "Invalid torrent id!");
	$owner = $arr['owner'];
	if ($userid == $owner)
		stderr("Error", "Why do you need to give bonus to yourself?");
	$tsql = sql_query("SELECT COUNT(*) FROM bonuses where torrentid=$torrentid and userid=$userid");
	$trows = mysql_fetch_array($tsql);
	$t_ab = $trows[0];
	if ($t_ab != 0)
		stderr("Error", "You've already given your bonus, thank you.");
	if (isset($userid) && isset($torrentid))
	{
		$res = sql_query("INSERT INTO bonuses (torrentid, userid, bonus) VALUES ($torrentid, $userid, $bonus)");
		KPS("-",$bonus,$CURUSER['id']); // User lost bonus
		KPS("+",$bonus,$owner); // Torrent uploader got bonus
	}
}
if ($type == 'post')
{
	$tsql = sql_query("SELECT posts.userid, posts.topicid, topics.subject FROM posts LEFT JOIN topics ON posts.topicid = topics.id WHERE posts.id=$postid");
	$arr = mysql_fetch_array($tsql);
	if (!$arr)
		stderr("Error", "Invalid post id!");
	$owner = $arr['userid'];
	$topicid = $arr['topicid'];
	$topic = $arr['subject'];
	if ($userid == $owner)
		stderr("Error", "Why do you need to give bonus to yourself?");
	$tsql = sql_query("SELECT COUNT(*) FROM bonuses where postid=$postid and userid=$userid");
	$trows = mysql_fetch_array($tsql);
	$t_ab = $trows[0];
	if ($t_ab != 0)
		stderr("Error", "You've already given your bonus, thank you.");	
	if (isset($userid) && isset($postid))
	{
		$res = sql_query("INSERT INTO bonuses (postid, userid, bonus) VALUES ($postid, $userid, $bonus)");
		KPS("-",$bonus,$CURUSER['id']); // User lost bonus
		KPS("+",$bonus,$owner); // Post author got bonus
		$lang = get_user_lang($owner);
		$subject = $lang_bonus_target[$lang]['msg_post_bonus'];
		$msg = sprintf($lang_bonus_target[$lang]['msg_post_bonus_detail'], $topicid, $postid, $postid, $topic, $CURUSER['username'], $bonus);
		pm_user($subject, $msg, $owner);
	}
}
?>