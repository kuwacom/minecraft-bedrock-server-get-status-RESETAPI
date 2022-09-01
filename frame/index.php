<?php
$ip = $_GET["ip"];
$port = $_GET["port"];
$version = $_GET["version"];
$protocol = $_GET["protocol"];
$players = $_GET["players"];
$gamemode = $_GET["gamemode"];
$address = $_GET["address"];
$ipv4 = $_GET["ipv4"];
$ipv6 = $_GET["ipv6"];
$ping = $_GET["ping"];

// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL);

$data = b"\x01\x00\x00\x00\x00L\x00\x00\x00\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x124Vx\x00\x00\x00\x00\x00\x00\x00\x00";

$sndtimeo = 1; // 接続タイムアウト (本当は送信タイムアウト)
$rcvtimeo = 1; // 受信タイムアウト
$error = 'None';
// ソケット作成
$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP );
// オプション設定
socket_set_option( $sock, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>$sndtimeo,"usec"=>0) );
socket_set_option( $sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>$rcvtimeo,"usec"=>0) );

// 接続
socket_connect($sock, $ip, $port);
$error_code = socket_last_error($sock);
// エラーコード一覧 https://www.php.net/manual/ja/function.socket-last-error.php#95160
if($error_code == 0){
	$error = 'None';
}elseif($error_code == 110){
	$error = 'timeout';
}elseif($error_code == 11001){
	$error = 'nothost';
}else{
	$error = socket_strerror($error_code);
}

if($error == "None"){
	// 送受信
	$start_time = microtime(true);
	socket_write($sock, $data);
	$end_time = microtime(true);
	$status = socket_read( $sock, 10240 );
	$rec_time = microtime(true);

	$connect_time = ($end_time - $start_time) * 1000;
	// echo round($connect_time, 2).'<br>';
	$rec_time = ($rec_time - $end_time) * 1000;
	// echo round($rec_time, 2).'<br>';
	$total_time = $connect_time + $rec_time;

	$error_code = socket_last_error($sock);
	// エラーコード一覧 https://www.php.net/manual/ja/function.socket-last-error.php#95160
	if($error_code == 0){
		$error = 'None';
	}elseif($error_code == 110){
		$error = 'timeout';
	}else{
		$error = socket_strerror($error_code);
		// echo $error;
	}
}

if($error == 'None'){
	$status = strstr($status, 'MCPE');
	$status = explode(';', $status);

	$server_name = $status[1];
	$server_protocol = $status[2];
	$server_version = $status[3];
	$server_connecting_players = $status[4];
	$server_max_players = $status[5];
	$server_name_2 = $status[7];
	$server_gamemode = $status[8];
	$server_ip = gethostbyname($ip);
	if($server_ip == "192.168.1.8"){
		$server_ip = "kuwa.cf";
	}
	if(isset($status[10])){
		if($status[10] == -1 or ''){
			$server_ipv4_port = 'None';
		}else{
			$server_ipv4_port = $status[10];
		}
	}else{
		$server_ipv4_port = 'None';
	}
	if(isset($status[11])){
		if($status[11] == -1 or ''){
			$server_ipv6_port = 'None';
		}else{
			$server_ipv6_port = $status[11];
		}
	}else{
		$server_ipv6_port = 'None';
	}

}else{
	// echo $error;
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<title><?php echo $server_name ?></title>
<link rel="icon" type="image/png" href="../images/sanka1.png">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta property="og:title" content="<?php echo $server_name ?>のステータス">
<meta property="og:type" content="website">
<meta property="og:description" content="サーバーステータス
<?php
if($error == "None"){
	echo "タイトル: $server_name
	サブタイトル: $server_name_2";
	if($version == "true"){
		echo "バージョン: $server_version";
	}
	if($protocol == "true"){
		echo "プロトコルバージョン: $server_protocol";
	}
	if($players == "true"){
		echo "接続数: $server_connecting_players/$server_max_players";
	}
	if($gamemode == "true"){
		echo "ゲームモード: $server_gamemode";
	}
	if($address == "true"){
		echo "サーバーIP: $server_ip";
	}
	if($ipv4 == "true"){
		echo "IPv4ポート: $server_ipv4_port";
	}
	if($ipv6 == "true"){
		echo "IPv6ポート: $server_ipv6_port";
	}
	if($ping == "true"){
		echo "PING: ".round($total_time, 2)."ms";
	}
}else{
	echo "Off Line";
}
?>
">

<meta property="og:url" content="https://kuwa.cf/mcbe-ss/?ip=<?php echo $ip ?>&port=<?php echo $port ?>">
<meta property="og:image" content="https://kuwa.cf/image/logo_2.png">
<meta property="og:site_name" content="kuwa.cf">
<meta property="og:locale" content="ja_JP">

<meta name="twitter:card" content="summary_large_image"><!--デカい画像とかはこれで設定できる-->
<meta property="twitter:title" content="<?php echo $server_name ?>">
<meta name="twitter:description" content="<?php echo $server_name_2 ?>">
<meta name="twitter:site" content="@kuwamain">

<meta name="theme-color" content="#ffa500">
<link rel="stylesheet" href="./style.css" />
<script>
setTimeout(function () {
    location.reload();
}, 4000);
</script>
</head>

<div id="container">

<div id="main">
<!--/#new-->
<section>
<?php
$server_name = str_replace('§l', '<b>', $server_name);
$server_name = str_replace('§a', '<font color="#00FF00">', $server_name);
$server_name = str_replace('§b', '<font color="#00FFFF">', $server_name);
$server_name = str_replace('§c', '<font color="#FF0461">', $server_name);
$server_name = str_replace('§d', '<font color="#FF00FF">', $server_name);
$server_name = str_replace('§e', '<font color="#FFFF00">', $server_name);
$server_name = str_replace('§f', '<font color="white">', $server_name);
$server_name = str_replace('§g', '<font color="#FFD700">', $server_name);
$server_name = str_replace('§r', '</font></b>', $server_name);

$server_name = str_replace('§0', '<font color="#111111">', $server_name);
$server_name = str_replace('§1', '<font color="#0000FF">', $server_name);
$server_name = str_replace('§2', '<font color="#00DD00">', $server_name);
$server_name = str_replace('§3', '<font color="#00CED1">', $server_name);
$server_name = str_replace('§4', '<font color="#FF0000">', $server_name);
$server_name = str_replace('§5', '<font color="#FF00FF">', $server_name);
$server_name = str_replace('§6', '<font color="#FF8C00">', $server_name);
$server_name = str_replace('§7', '<font color="#A9A9A9">', $server_name);
$server_name = str_replace('§8', '<font color="#696969">', $server_name);
$server_name = str_replace('§9', '<font color="#7B68EE">', $server_name);

$server_name_2 = str_replace('§l', '<b>', $server_name_2);
$server_name_2 = str_replace('§a', '<font color="#00FF00">', $server_name_2);
$server_name_2 = str_replace('§b', '<font color="#00FFFF">', $server_name_2);
$server_name_2 = str_replace('§c', '<font color="#FF0461">', $server_name_2);
$server_name_2 = str_replace('§d', '<font color="#FF00FF">', $server_name_2);
$server_name_2 = str_replace('§e', '<font color="#FFFF00">', $server_name_2);
$server_name_2 = str_replace('§f', '<font color="white">', $server_name_2);
$server_name_2 = str_replace('§g', '<font color="#FFD700">', $server_name_2);
$server_name_2 = str_replace('§r', '</font></b>', $server_name_2);

$server_name_2 = str_replace('§0', '<font color="#111111">', $server_name_2);
$server_name_2 = str_replace('§1', '<font color="#0000FF">', $server_name_2);
$server_name_2 = str_replace('§2', '<font color="#00DD00">', $server_name_2);
$server_name_2 = str_replace('§3', '<font color="#00CED1">', $server_name_2);
$server_name_2 = str_replace('§4', '<font color="#FF0000">', $server_name_2);
$server_name_2 = str_replace('§5', '<font color="#FF00FF">', $server_name_2);
$server_name_2 = str_replace('§6', '<font color="#FF8C00">', $server_name_2);
$server_name_2 = str_replace('§7', '<font color="#A9A9A9">', $server_name_2);
$server_name_2 = str_replace('§8', '<font color="#696969">', $server_name_2);
$server_name_2 = str_replace('§9', '<font color="#7B68EE">', $server_name_2);

if($error == 'None'){

	echo "<table class='ta1'>
	<tr><th colspan='2' class='midasi'>
	$server_name
	</b></font><br>
	$server_name_2
	</th></tr>";

	if($version == "true"){
		echo "<tr>
		<th>バージョン</th>
		<td>$server_version</td>
		</tr>";
	}
	if($protocol == "true"){
		echo "<tr>
		<th>プロトコルバージョン</th>
		<td>$server_protocol</td>
		</tr>";
	}
	if($players == "true"){
		echo "<tr>
		<th>接続人数</th>
		<td>$server_connecting_players/$server_max_players</td>
		</tr>";
	}
	if($gamemode == "true"){
		echo "<tr>
		<th>ゲームモード</th>
		<td>$server_gamemode</td>
		</tr>";
	}
	if($address == "true"){
		echo "<tr>
		<th>IPアドレス</th>
		<td>$server_ip</td>
		</tr>";
	}
	if($ipv4 == "true"){
		echo "<tr>
		<th>IPv4ポート</th>
		<td>$server_ipv4_port</td>
		</tr>";
	}
	if($ipv6 == "true"){
		echo "<tr>
		<th>IPv6ポート</th>
		<td>$server_ipv6_port</td>
		</tr>";
	}
	if($ping == "true"){
		echo "<tr>
		<th>PING</th>
		<td>".round($total_time, 2)." ms</td>
		</tr>";
	}
	echo "</table>";
}elseif($error == "timeout"){
	echo '<table class="ta1">
	<tr><th colspan="2" class="error">エラーが発生しました</th></tr>'.
	'<tr>'.
	'<th>詳細</th>'.
	'<td>接続がタイムアウトしました</td>'.
	'</tr>'.
	'</table>';
}elseif($error == "nothost"){
	echo '<table class="ta1">
	<tr><th colspan="2" class="error">エラーが発生しました</th></tr>'.
	'<tr>'.
	'<th>詳細</th>'.
	'<td>無効なIPアドレスです！</td>'.
	'</tr>'.
	'</table>';
}else{
	echo '<table class="ta1">
	<tr><th colspan="2" class="error">エラーが発生しました</th></tr>'.
	'<tr>'.
	'<th>詳細</th>'.
	'<td>'.$error.'</td>'.
	'</tr>'.
	'</table>';
}
?>


<!-- GOOGLEのアドセンス広告のやつ -->
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9930541527927698"
     crossorigin="anonymous">
</script>


</body>
</html>

