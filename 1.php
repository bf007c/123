<?php
/**
* Socket版本
* 使用方法：
* $post_string = "app=socket&version=beta";
* request_by_socket('chajia8.com', '/restServer.php', $post_string);
*/
function request_by_socket($remote_server,$remote_path,$referer,$post_string,$session,$port = 33200,$timeout = 30) {
$socket = fsockopen($remote_server, $port, $errno, $errstr, $timeout);
if (!$socket) die("$errstr($errno)");
fwrite($socket, "POST $remote_path HTTP/1.1\r\n");
fwrite($socket, "HOST: $remote_server\r\n");
fwrite($socket, "Accept-Encoding: deflate, gzip\r\n");
if ($session<>''){
fwrite($socket, "Cookie: JSESSIONID=$session \r\n");
}
fwrite($socket, "Connection: Keep-Alive\r\n");
fwrite($socket, "Origin: http://10.254.193.84:33200\r\n");
fwrite($socket, "User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/534.0 (KHTML, like Gecko)\r\n"); 
fwrite($socket, "Content-type: application/x-www-form-urlencoded\r\n");
fwrite($socket, "Accept: */*\r\n");
fwrite($socket, "Referer: $referer \r\n");
fwrite($socket, "Content-length: " . strlen($post_string) . "\r\n");
fwrite($socket, "\r\n"); 
fwrite($socket, $post_string."\r\n");
fwrite($socket, "\r\n"); 
fwrite($socket, "\r\n");

$header = "";
while ($str = trim(fgets($socket, 4096))) {
$header .= $str;
}

$data = "";
while (!feof($socket)) {
$data .= fgets($socket, 4096);
}

$data .= $header;
return $data;
}

/**
* des-ecb加密
* @param string $data 要被加密的数据
* @param string $key 加密密钥
*/
function des_ecb_encrypt($data, $key){
$data1 = $data;
if (strlen($data1) % 8){
$len = strlen($data1) % 8;
$data1 = str_pad($data1,$len, $len);
}
else{
$data1 = str_pad($data1,8, 8);
}
return openssl_encrypt ($data1, 'des-ecb', $key, 1);
}
//请事先在本文件目录内新建两个txt文件，记录session和token。
$session = file_get_contents("sid.txt");
$token = file_get_contents("token.txt");
chanbill:
$post_referer = "http://10.254.193.84:33200/EPG/jsp/ValidAuthenticationHWCU.jsp";
$post_string = "";
$html = request_by_socket('10.254.193.84:33200', '/EPG/jsp/getchannellistHWCU.jsp?channellisttype=0&UserToken='.$token,$post_referer, $post_string,$session);


preg_match("/SessionTimeOutJump/",$html,$invalid);

if (count($invalid)==1){

$post_string = "isfirst=0";
$post_referer = "http://10.254.193.84:33200/EPG/jsp/AuthenticationURL?Action=Login&UserID=371452251041198890&SampleId=20&v6edsaddress=10.7.227.141";
$html = request_by_socket('10.254.193.84:33200', '/EPG/jsp/authLoginHWCU.jsp?UserID=371452251041198890&SampleId=20',$post_referer, $post_string,'');

preg_match("/JSESSIONID=([\s\S]*?);/",$html,$sid);
preg_match("/EncryptToken=([\s\S]*?);"/",$html,$enc);
$myfile = fopen("sid.txt", "w") or die("Unable to open file!");
$txt = $sid[1];
fwrite($myfile, $txt);
fclose($myfile);
$tt = "99999[        DISCUZ_CODE_0        ]quot;.$enc[1]."$371452251041198890$000004000008894000000021265B223C$10.24.168.34$00:21:26:5B:22:3C\$\$CTC";
$post_referer = "http://10.254.193.84:33200/EPG/jsp/authLoginHWCU.jsp?UserID=371452251041198890&SampleId=20";
$post_string = "UserID=371452251041198890&UserPwd=&Lang=1&SupportHD=1&NetUserID=&Authenticator=".bin2hex(des_ecb_encrypt($tt,"00000000"))."&STBType=EC6108V9E_pub_hnylt&STBVersion=V100R003C88LHAL23B014&conntype=2&STBID=000004000008894000000021265B223C&templateName=defaultnew1&areaId=31701&userToken=".$enc[1]."&userGroupId=1&productPackageId=HDPackage&mac=00:21:26:5B:22:3C&UserField=2&SoftwareVersion=V100R003C88LHAL23B014&IsSmartStb=0&desktopId=&SampleId=20&isfirst=0";
$html = request_by_socket('10.254.193.84:33200', '/EPG/jsp/ValidAuthenticationHWCU.jsp',$post_referer, $post_string,$sid[1]);
preg_match("/UserToken\',\'([\s\S]*?)\'/",$html,$tkn);
$myfile1 = fopen("token.txt", "w") or die("Unable to open file!");
$token = $tkn[1];
fwrite($myfile1, $token);
fclose($myfile1);
$session = $sid[1];
goto chanbill;//如果session过期重新抓取频道
}

$html = preg_replace('/\r\n.{1,5}\r\n/', '',$html);
//echo "<textarea style="font-size:15px; font-family: Helvetica, sans-serif;" rows=9 cols=100>".$html."</textarea>";

preg_match_all("/ChannelName="([\s\S]*?)"/",$html,$chname);
preg_match_all("/ChannelURL="igmp\:\/\/([\s\S]*?)"/",$html,$churl);

$prot = "http://192.1680.1:4444/udp/";

echo "#EXTM3U\n";
for($n=0;$n<count($chname[1]);$n++){
echo "#EXTINF:-1,".$chname[1][$n]."\n";
echo $prot.$churl[1][$n]."\n";
}

?>
