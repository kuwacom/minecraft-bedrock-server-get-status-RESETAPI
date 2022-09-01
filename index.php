<?php

$ip = $_GET["ip"];
$port = $_GET["port"];
$getStatus = $_GET["getStatus"];

if($getStatus == "true"){
  echo '{"status":200}';
  return;
}
if(!isset($ip) or !isset($port)){
  echo '{"status":"error","detail":"badiporport"}';
  return;
}

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
	// if($server_ip == "192.168.1.8"){
	// 	$server_ip = "kuwa.cf";
	// }
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


if($error == "None"){
	echo<<<HTML
	{status:"202",data:{"ip":"$server_ip","v4port":"$server_ipv4_port","v6port":"$server_ipv6_port","ping":"$total_time","motd":"$server_name","motd2":"$server_name_2","version":"$server_version","protocolVersion":"$server_protocol","maxPlayers":"$server_max_players","connectingPlayers":"$server_connecting_players","gamemode","$server_gamemode"}}
	HTML;	
}elseif($error == "timeout"){
	echo '{"status":"timeout"}';
}elseif($error == "nothost"){
	echo '{"status":"nothost"}';
}else{
	echo `{"status":"error","detail":"$error"}`;
}
?>
