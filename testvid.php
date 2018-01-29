<?php
session_start();
ini_set('max_execution_time','6000');
ob_start();
$host = ""; 
$user = "";
$dbpass = "";
$db = "";
$conn = mysql_connect("$host", "$user", "$dbpass") or die ("Unable to connect to database."); 
mysql_select_db("$db", $conn);
mysql_query("SET NAMES 'utf8'");

$df = mysql_query("select id,post_title,post_content from wp_posts");
$count = mysql_num_rows(mysql_query("select * from wp_posts"));
echo "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /></head><body><font color=red>\n<div id=\"ilerleme\"></div>";
$satirsayi = 1;
$kiriksayi = 0;
$filmsayac = 0;
$result = "";
$vkError = 0;
$mailruError = 0;
$gdocsError = 0;
while($row = mysql_fetch_array($df)){
    $filmsayac++;
	$frame = strstr($row['post_content'],"<iframe");
	$fpart = strstr($frame,"http");
	$end = strpos($fpart,"\"");
	$url = substr($fpart,0,$end);
	$end2 = strpos($fpart,"'");
	if($end2 > 0) {
	$url = substr($url,0,$end2) ;
	};
	//echo $url;
	//echo $url ."\n";
	if(strpos($url,"&#038;")>0 || strpos($url,"&amp;")>0) {
		echo $row['post_title'] ."  ". str_replace("&","&amp;",$url) . " html entity encoded link; should be:" . html_entity_decode($url)."\n<br><br><br>";
	}
	
	if(strpos($url,"vk.com")>0){
	$vidpage = file_get_contents($url);
	if($vidpage == false  ) {if($vkError == 0) echo "vk connection error!\n<br/>"; $vkError++;}
	if(strpos($vidpage,"No videos found.")>0  || strpos($vidpage,"This video has been removed from public access.")>0 ){
		 $result .=  $satirsayi. " Vk.com - " . $row['post_title'] . " missing video!".$row['id']."\n<br>"; 
		 $satirsayi++;
		 $kiriksayi++;
		}
	}
	if(strpos($url,"mail.ru")>0){
	$vidpage = file_get_contents($url);
	if($vidpage == false ) { if( $mailruError == 0) echo "mail.ru connection error!!\n<br/>"; $mailruError++;}
	if(strpos($vidpage,'videoSrc = ""')>0 && strpos($vidpage,'isPrivate = false')>0){
		 $result .= $satirsayi ." Mail.ru - " . $row['post_title'] . " missing video!".$row['id']."\n<br>"; 
		 $satirsayi++;
		 $kiriksayi++;
		}
	}
	if(strpos($url,"docs.google.com")>0){
	 	$vidpage = file_get_contents($url);
	if($vidpage == false ) { if( $gdocsError == 0) echo "google docs connection error!!\n<br/>"; $gdocsError++;}
	if(strpos($vidpage,'Google Drive -- Page Not Found')>0){
		 $result .= $satirsayi ." Google Docs - " . $row['post_title'] . " missing video!".$row['id']."\n<br>"; 
		 $satirsayi++;
		 $kiriksayi++;
		}
	}
	
	//echo "<script type=\"text/javascript\">document.getElementbyId('ilerleme').innerHTML('<p>$filmsayac / $count</p>');</script>";
	flush();
	ob_flush();
}
if($kiriksayi == 0) {
	echo "No broken iframes! \n<br/>";

} else {
	echo $result;
}
	echo "Bağlanılamayan vk linki sayısı: $vkError \n<br/>";
	echo "Bağlanılamayan mail.ru linki sayısı: $mailruError \n<br/>";
	echo "Bağlanılamayan google docs linki sayısı: $gdocsError \n<br/>";
echo "</font></body></html>\n";
?>
