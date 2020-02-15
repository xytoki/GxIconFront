<?php
/* 
 * Gxicon Manager
 * gxicon.e123.pw
 * By:Weng 
 */
ini_set("display_errors","On");
error_reporting(E_ALL ^ E_NOTICE);
define("SQL_HOST","");
define("SQL_PORT","");
define("SQL_USER","");
define("SQL_NAME","");
define("SQL_PASS","");
define("UPYUN_H","");
define("UPYUN_U","");
define("UPYUN_P","");
define("UPYUN_W","//gxicon.static.homeii.info");
define("GH_TOKEN","");
define("MC_SERVER","127.0.0.1");
define("MC_PORT",11211);
include("jwt.php");
date_default_timezone_set('PRC'); ini_set('date.timezone','Asia/Shanghai');
define ('_Host', ($_SERVER["HTTP_X_REAL_SHEME"]!="https" &&( empty($_SERVER["HTTPS"]) || $_SERVER['HTTPS'] == 'off') ? 'http://' : 'https://') . $_SERVER['HTTP_HOST']);		// 主机网址
define ('_Http', _Host . str_ireplace('//','/',str_ireplace('/index.php', '', $_SERVER['SCRIPT_NAME']) . '/'));
session_name("GxIconAuth");
header("Access-Control-Allow-Origin:".(isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:"*"));
header("Access-Control-Allow-Credentials:true"); 
header("Access-Control-Allow-Headers: Origin, No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type");
if(isset($_SERVER['HTTP_X_EASY_AUTH']))session_id($_SERVER['HTTP_X_EASY_AUTH']);
if(isset($_GET['HTTP_X_EASY_AUTH']))session_id($_GET['HTTP_X_EASY_AUTH']);
session_start();
use \Firebase\JWT\JWT;
class GxIconApi{
    function __construct(){
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')die("code=0");
        $this->db = new mysqli(SQL_HOST,SQL_USER,SQL_PASS,SQL_NAME,SQL_PORT);
        if(mysqli_connect_error()){
            $err=mysqli_connect_error();
            header("HTTP/1.1 500 Database Error");
            $this->_json(array("code"=>500,"msg"=>$err));
        }
        $this->db->set_charset("utf8");
        $r=explode("&",$_SERVER['QUERY_STRING']);
        $this->path=$r[0];
        $r=explode("/",$r[0]); 
        $pfx="";
        $r3=explode("?",$r[1]);
        if(count($r3)>1){
            $r[1]=$r3[0];
            $r4=explode("=",$r3[1]);
            $_GET[$r4[0]]=$r4[1];
        }
        if($r[0]=="")array_shift($r);
        if($r[0]=="nano"){$pfx="nano_";array_shift($r);}
        $this->method=$r[0];
        if(method_exists($this,$pfx."api_".$r[0]."_".$r[1])){
            $rr=$pfx."api_".$r[0]."_".$r[1];
            $this->route=array_slice($r,2);
            $this->$rr(implode("/",$this->route));
        }elseif(method_exists($this,$pfx."api_".$r[0])){
            $rr=$pfx."api_".$r[0];
            $this->route=array_slice($r,1);
            $this->$rr(implode("/",$this->route));
        }else{
            $this->api_index();
        }
    }
    function api_user_auth(){
        session_write_close();
        $headers = array("X-Easy-Auth: ".session_id());
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://passport.e123.pw/api/remote/auth/".session_id());
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT,1);
        $data = curl_exec($ch);
        curl_close($ch);
        $d=json_decode($data,true);
        if($d['code']==200){
            $this->_json(array("msg"=>"done"),200);
            if(!isset($_SESSION['user']))$_SESSION['user']=$d['user'];
        }else{
            $this->_json(array("msg"=>"NotLogin"),401);
        }
    }
    function api_icon_upload(){
        if(isset($_FILES['file'])){
            $f = $_FILES['file'];
            $canupload=false;
            $ff = explode('.',$f['name']);
            $ext=$ff[count($ff)-1];
            if($f['type']=="image/webp"&&$ext="webp")$canupload=true;
            if($f['type']=="image/jpeg"&&$ext="jpg")$canupload=true;
            if($f['type']=="image/png"&&$ext="png")$canupload=true;
            if(!$canupload)$this->_json(array("msg"=>"Not an image","cn"=>"不是图片文件"),400);
            $info=getimagesize($f['tmp_name']);
            if($info!=false){
                if(abs(($info[0]/$info[1])-1)>0.2)$this->_json(array("msg"=>"Not an image","cn"=>"请上传正方形图片"),400);
                $filename=md5_file($f['tmp_name']).".".$ext;
                $filePath = $f['tmp_name'];
                $fileSize = filesize($filePath);
                $uri = "/".UPYUN_H."/iconstore/".$filename;
                $date = gmdate('D, d M Y H:i:s \G\M\T');
                $sign = md5("PUT&{$uri}&{$date}&{$fileSize}&".md5(UPYUN_P));
                $ch = curl_init('http://v0.api.upyun.com' . $uri);
                $headers = array(
                    "Expect:",
                    "Date: ".$date, // header 中需要使用生成签名的时间
                    "Authorization: UpYun ".UPYUN_U.":".$sign
                );
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_PUT, true);
                $fh = fopen($filePath, 'rb');
                curl_setopt($ch, CURLOPT_INFILE, $fh);
                curl_setopt($ch, CURLOPT_INFILESIZE, $fileSize);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($ch);
                if(curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200) {
                    $this->_json(array(
                        "url"=>UPYUN_W."/iconstore/".$filename,
                        "filesize"=>$filesize,
                        "imgsize"=>$info
                    ),0);
                } else {
                    $errorMessage = sprintf("UPYUN API ERROR:%s", $result);
                    echo $errorMessage;
                }
                curl_close($ch);
                var_dump($filename);
            }else{
                $this->_json(array("msg"=>"Not an image","cn"=>"请上传图片文件"),400);
            }
        }else{
            $this->_json(array("msg"=>"Invaild File","cn"=>"请选择文件"),400);
        }
    }
    function api_icon_search(){
        $sql="SELECT * FROM `gxicon_icons`";
        if(isset($_GET['package'])){
            $p=addslashes($_GET['package']);
            $sql.=" where `package` = '$p' and `delete`=0";
        }else{
            $sql.=" where `delete`=0";
        }
        $sq=$sql;
            $page=1;
            if(isset($_GET['page']))$page=(int) $_GET['page'];
            $b=50;
            $a=($page-1)*50;
            $sql.=" order by `date` desc limit $a,$b ";
        $res=$this->db->query($sql);
        $r=$res->fetch_all(MYSQLI_ASSOC);
        $res->close();
        
        $sql=str_replace("SELECT * FROM `gxicon_icons`","SELECT count(*) as count FROM `gxicon_icons`",$sq);
        if(isset($_GET['packege'])){
            $p=addslashes($_GET['package']);
            $sql.=" where `package` = '$p'";
        }
        $res=$this->db->query($sql);
        $c=$res->fetch_all(MYSQLI_ASSOC);
        $res->close();
        $this->_json(array("data"=>$r,"total"=>$c[0]['count']),0);
    }
    function api_icon_byshare($id){
        if(!is_numeric($id))$this->_json(array("msg"=>"Invaild ID"),400);
        $sql="SELECT * FROM `gxicon_icons` where `share_id` = '$id' and `delete`=0";
        $res=$this->db->query($sql);
        $r=$res->fetch_all(MYSQLI_ASSOC);
        $res->close();
        $sql="SELECT * FROM `gxicon_share` where `delete`=0 and `id` = '$id'";
        $res=$this->db->query($sql);
        $r2=$res->fetch_all(MYSQLI_ASSOC);
        $res->close();
        $sql="UPDATE `gxicon_share` SET `clickCount` = `clickCount`+1 WHERE `id` = '$id';";
        $this->db->query($sql);
        $this->_json(array("data"=>$r,"share"=>$r2),0);
    }
    function api_icon_byauthor($id){
        if(!is_numeric($id))$this->_json(array("msg"=>"Invaild ID"),400);
        $sql="SELECT * FROM `gxicon_icons` where `delete`=0 and `author` = 'e:$id'";
        $res=$this->db->query($sql);
        $r=$res->fetch_all(MYSQLI_ASSOC);
        $res->close();
        $this->_json(array("data"=>$r),0);
    }
    function api_icon_add($id){
        if(!is_numeric($id))$this->_json(array("msg"=>"Invaild ID"),400);
        $c=explode("/",$_POST['code']);
        $sql="INSERT INTO `gxicon_icons`";
        $sql.=$this->_sql(array(
            "img"=>$_POST['url'],
            "code"=>$_POST['code'],
            "package"=>$c[0],
            "author"=>$_POST['author'],
            "date"=>date('Y-m-d H:i:s'),
            "delete"=>0,
            "share_id"=>$id
        ));
        if ($this->db->query($sql) === TRUE) { }else {  
            $this->_json(array("msg"=>"Database error","err"=>$this->db->error,"sql"=>$sql),500);
        }
        $this->_json(array("code"=>0,"id"=>$this->db->insert_id));
    }
    function api_icon_delete($id){
        if(!is_numeric($id))$this->_json(array("msg"=>"Invaild ID"),400);
        if(!isset($_SESSION['user']))$this->_json(array("msg"=>"NotLogin"),401);
        $sql="UPDATE `gxicon_icons` SET `delete` = '1' WHERE `id` = '".$id."' and `author` = 'e:".$_SESSION['user']['id']."';";
        if ($this->db->query($sql) === TRUE) { 
            if($this->db->affected_rows==0)$this->_json(array("msg"=>"Permission denied"),403);
            $this->_json(array("code"=>0));
        }else {  
            $this->_json(array("msg"=>"Database error","err"=>$this->db->error,"sql"=>$sql),500);
        }
    }
    function api_icon_export(){
        $d=date("Y-m-d",time()-24*60*60);
        $sql="SELECT `id` FROM `gxicon_share` WHERE `delete` = '0' AND `date` > '". $d ." 12:00:00' ";
        $res=$this->db->query($sql);
        $r=$res->fetch_all(MYSQLI_ASSOC);
        $res->close();
        $icons=array();
        foreach ($r as $i){
            $id=$i['id'];
            $sql="SELECT `id`,`package`,`img` FROM `gxicon_icons` WHERE `share_id` = '$id' and `delete`=0";
            $res=$this->db->query($sql);
            $r2=$res->fetch_all(MYSQLI_ASSOC);
            $res->close();
            foreach ($r2 as $a){
                $icons[]=(array($a['id'],$a['package'],$a['img']));
            }
        }
        $this->_json(array("startfrom"=>$d,"icons"=>$icons),0);
    }
    function api_icon_exportall(){
        $sql="SELECT `id` FROM `gxicon_share` WHERE `delete` = '0'";
        $res=$this->db->query($sql);
        $r=$res->fetch_all(MYSQLI_ASSOC);
        $res->close();
        $icons=array();
        foreach ($r as $i){
            $id=$i['id'];
            $sql="SELECT `id`,`package`,`img` FROM `gxicon_icons` WHERE `share_id` = '$id' and `delete`=0";
            $res=$this->db->query($sql);
            $r2=$res->fetch_all(MYSQLI_ASSOC);
            $res->close();
            foreach ($r2 as $a){
                $icons[]=(array($a['id'],$a['package'],$a['img']));
            }
        }
        $this->_json(array("startfrom"=>$d,"icons"=>$icons),0);
    }
    function api_share_prepare(){
        $sql="INSERT INTO `gxicon_share`";
        $sql.=$this->_sql(array(
            "author"=>$_POST['author'],
        ));
        if ($this->db->query($sql) === TRUE) { }else {  
            $this->_json(array("msg"=>"Database error","err"=>$this->db->error,"sql"=>$sql),500);
        }
        $this->_json(array("code"=>0,"sid"=>$this->db->insert_id));
    }
    function api_share_add($id){
        if(!is_numeric($id))$this->_json(array("msg"=>"Invaild ID"),400);
        $sql="UPDATE `gxicon_share` SET ";
        $sql.=$this->_sql(array(
            "desc"=>$_POST['desc'],
            "delete"=>0,
            "date"=>date('Y-m-d H:i:s'),
            "author"=>$_POST['author'],
            "icon_ids"=>"+".$_POST['ids']."+",
            "tags"=>"+".$_POST['tags']."+"
        ),"update");
        $sql.="WHERE `id`='".addslashes($id)."'";
        if ($this->db->query($sql) === TRUE) { }else {  
            $this->_json(array("msg"=>"Database error","err"=>$this->db->error,"sql"=>$sql),500);
        }
        $this->_json(array("code"=>0,"sid"=>$this->db->insert_id));
    }
    function api_share_byid($id){
        if(!is_numeric($id))$this->_json(array("msg"=>"Invaild ID"),400);
        $sql="SELECT * FROM `gxicon_share` where `delete`=0 and `id` = '$id'";
        $res=$this->db->query($sql);
        $r=$res->fetch_all(MYSQLI_ASSOC);
        $res->close();
        $this->_json(array("data"=>$r),0);
    }
    function api_share_byicon($id){
        if(!is_numeric($id))$this->_json(array("msg"=>"Invaild ID"),400);
        $sql="SELECT * FROM `gxicon_share` WHERE `delete`=0 and `icon_ids` LIKE '%+$id+%'";
        $res=$this->db->query($sql);
        $r=$res->fetch_all(MYSQLI_ASSOC);
        $res->close();
        $this->_json(array("data"=>$r),0);
    }
    function api_share_byauthor($id){
        if(!is_numeric($id))$this->_json(array("msg"=>"Invaild ID"),400);
        $sql="SELECT * FROM `gxicon_share` where `delete`=0 and `author` = 'e:$id'";
        $res=$this->db->query($sql);
        $r=$res->fetch_all(MYSQLI_ASSOC);
        $res->close();
        $this->_json(array("data"=>$r),0);
    }
    function api_code_search($c){
        $c=addslashes(urldecode($c));
        if($c=="")$this->_json(array("data"=>array()),0);
        $ispkg=1;
        $sql="SELECT label,label_en AS labelEn,pkg,sum2, launcher,COUNT(*) AS sum  FROM req WHERE pkg LIKE '$c%' GROUP BY pkg ORDER BY sum,sum2 DESC LIMIT 15";
        $sql_n="SELECT sum2,label, label_en AS labelEn, pkg, launcher, COUNT(*) AS sum FROM req WHERE label LIKE '%$c%' OR label_en LIKE '%$c%' GROUP BY label, label_en, pkg, launcher LIMIT 128";
        if(!preg_match("/^[a-zA-Z\\d_]+\\.[a-zA-Z\\d_\\.]+$/",$c)){
            $sql=$sql_n;
            $ispkg=0;
        }
        $res=$this->db->query($sql);
        $r=$res->fetch_all(MYSQLI_ASSOC);
        $res->close();
        $rx=array();
        foreach($r as &$rr)$rr["slot"]=$rr['label']."/".$rr['pkg'];
        $this->_json(array("data"=>$r),0);
    }
    function api_build_submit(){
        $j=array();
        $j['message']=$_POST['message'];
        $j['content']=base64_encode($_POST['content']);
        $j['committer']=array("name"=>"共享图标包自助打包系统","email"=>"gxicon@e123.pw");
        $base = "https://api.github.com";
        $url="/repos/xytoki/GxIconDIY/contents/_autoMake.json";
        $headers = array(
            "Accept: application/vnd.github.v3+json",
            "User-Agent: GxIconDIY"
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, "xytoki:".GH_TOKEN);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //设置请求头
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取的信息以文件流的形式返回
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);//2019.3.31 github api 301
        $resx = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        if($res === FALSE ){
             $resx=curl_error($ch);
        }
        curl_close($ch);
        $resx=json_decode($resx,true);
        $j['sha']=$resx['sha'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, "xytoki:".GH_TOKEN);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //设置请求头
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取的信息以文件流的形式返回
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);//2019.3.31 github api 301
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($j));
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        if($res === FALSE ){
             $res=curl_error($ch);
        }
        $json=json_decode($res,true);
        if($json === FALSE ){
             $json=$res;
        }
        curl_close($ch);
        $app=json_decode($_POST['content'],true);
        $ico=isset($app['ico'])?$app['ico']:"http://gxicon.static.homeii.info/2017/08/23/22/20170823102618_9862d989faf315beaf69a4edb44aeb08.png";
        $sq="INSERT INTO `gxicon_apk` (`pkg`, `vcode`, `vname`, `name`, `icon`, `url`, `status`) VALUES ('".$app["pkg"]."', '".$app["vcode"]."', '".$app["vname"]."', '".$app["app"]."', '$ico', '', 'pending');";
        $this->db->query($sq);
        $this->_json($json);
    }
    function api_build_custom(){
        $app=json_decode($_POST['content'],true);
        $ico=isset($app['ico'])?$app['ico']:"http://gxicon.static.homeii.info/2017/08/23/22/20170823102618_9862d989faf315beaf69a4edb44aeb08.png";
        $sq="INSERT INTO `gxicon_apk` (`pkg`, `vcode`, `vname`, `name`, `icon`, `url`, `status`) VALUES ('".$app["pkg"]."', '".$app["vcode"]."', '".$app["vname"]."', '".$app["app"]."', '$ico', '', 'pending');";
        //$this->db->query($sq);
        $key = isset($_POST['key'])?$_POST['key']:"gxicon_toki_key";
        $token = [
            "exp" => time()+600,
            "data"=>$app
        ];
        $jwt = JWT::encode($token, $key);
        $server=isset($_POST['server'])?$_POST['server']:"http://172.105.240.200:5656";
        $this->_json([
            "code"=>0,
            "token"=>$jwt,
            "server"=>$server."/api/build",
            "terminal"=>$server."/terminal.html?id="
        ]);
    }
    function api_build_done($p){
        $p=addslashes($p);
        $u=addslashes(str_replace(",","",$_GET['url']));
        $vc=addslashes($_GET['vcode']);
        $vn=addslashes($_GET['vname']);
        $sql="UPDATE `gxicon_apk` SET `url` = '$u', `status` = 'done',`time` = now() WHERE `pkg` = '$p' and `vcode`='$vc' and `vname` = '$vn';";
        if ($this->db->query($sql) === TRUE) { }else {  
            $this->_json(array("msg"=>"Database error","err"=>$this->db->error,"sql"=>$sql),500);
        }
        $this->_json(array("code"=>0,"sid"=>$this->db->insert_id));
    }
    function api_build_box($passwd){
        if($passwd!="gxicon123")die();
        $sql="SELECT `id` FROM `gxicon_share` WHERE `delete` = '0'";
        $res=$this->db->query($sql);
        $r=$res->fetch_all(MYSQLI_ASSOC);
        $res->close();
        $icons=array();
        foreach ($r as $i){
            $id=$i['id'];
            $sql="SELECT `id`,`package`,`img` FROM `gxicon_icons` WHERE `share_id` = '$id' and `delete`=0";
            $res=$this->db->query($sql);
            $r2=$res->fetch_all(MYSQLI_ASSOC);
            $res->close();
            foreach ($r2 as $a){
                $icons[]=(array($a['id'],$a['package'],$a['img']));
            }
        }
        $_POST['message']=date('Y-m-d H:i:s')."主图标打包";
        $_POST['content']=$this->_prettyJson(array(
            "pkg"=>"com.e123.gxicon",
            "app"=>"共享图标包",
            "vname"=>date("Y.m.d.H"),
            "vcode"=>date("ymdH"),
            "author"=>"[@xytoki](https://xylog.cn)",
            "color_primary"=>"#008b99",
            "color_primary_dark"=>"#008b99",
            "color_accent"=>"#008b99",
            "ignore_appfilter"=>true,
            "icons"=>$icons
        ));
        $this->api_build_submit();
    }
    function api_apk_down($p){
        $p=addslashes($p);
        $v=isset($_GET['v'])?addslashes($_GET['v']):"latest";
        if($v=="latest"){
            $sql="SELECT `url` FROM `gxicon_apk` where `pkg`='$p' ORDER BY `time` DESC LIMIT 1";
        }else{
            $sql="SELECT `url` FROM `gxicon_apk` where `pkg`='$p' and `vname`='$v' ORDER BY `time` DESC LIMIT 1";
        }
        $res=$this->db->query($sql);
        $r=$res->fetch_all(MYSQLI_ASSOC);
        header("Location: ".$r[0]['url']);
    }
    function api_apk_info($p){
        $p=addslashes($p);
        $v=isset($_GET['v'])?addslashes($_GET['v']):"latest";
        if($v=="latest"){
            $sql="SELECT * FROM `gxicon_apk` where `pkg`='$p' and `status`='done' ORDER BY `time` DESC LIMIT 1";
        }else{
            $sql="SELECT * FROM `gxicon_apk` where `pkg`='$p' and `vname`='$v' and `status`='done' ORDER BY `time` DESC LIMIT 1";
        }
        $res=$this->db->query($sql);
        $r=$res->fetch_all(MYSQLI_ASSOC);
        $this->_json(array("data"=>$r[0]),0);
    }
    function api_index(){
        header("HTTP/1.1 404 Not Found");
        $this->_json(array("code"=>404,"msg"=>"API `".$this->method."` NOT FOUND"));
    }
    
    function nano_api_code($c){
        if(strlen($c)<=1)$this->_json(array("msg"=>"Invaild Request"),400);
        $c=str_replace(",",".",$c);
        $c=addslashes($c);
        $ispkg=0;
        $sql_pkg="SELECT sum2,label, label_en AS labelEn, pkg, launcher, COUNT(*) AS sum FROM req WHERE pkg = '?' GROUP BY label, label_en, pkg, launcher";
        $sql="SELECT sum2,label, label_en AS labelEn, pkg, launcher, COUNT(*) AS sum FROM req WHERE label LIKE '%?%' OR label_en LIKE '%?%' GROUP BY label, label_en, pkg, launcher LIMIT 128";
        if(preg_match("/^[a-zA-Z\\d_]+\\.[a-zA-Z\\d_\\.]+$/",$c)){
            $sql=$sql_pkg;
            $ispkg=1;
        }
        $sql=str_replace("?",$c,$sql);
        $res=$this->db->query($sql);
        $r=$res->fetch_all(MYSQLI_ASSOC);
        foreach($r as &$a)$a['sum']+=$a['sum2'];
        $res->close();
        if(count($r)==0&&$ispkg=1){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://nano.by-syk.com/code/".$c);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT,1);
            $data = curl_exec($ch);
            curl_close($ch);
            $data=json_decode($data,true);
            $r=$data['result'];
            if($r!=null){
                $sq="INSERT IGNORE INTO req (`label`, `label_en`, `pkg`, `launcher`,`sum2`) values ";
                foreach($r as $one){
                    $sq.="('".$one['label']."','".$one['labelEn']."','".$one['pkg']."','".$one['launcher']."','".$one['sum']."'),";
                    //$sq.=$this->_sql($one)." ";
                }
                $sq.=",,,";
                $sq=str_replace(",,,,","",$sq);
                $this->db->query($sq);
            }
            $db=true;
            //echo $sq;
        }
        $this->_json(array("msg"=>"success","status"=>0,"db"=>$db,"result"=>$r),0);
    }
    function nano_api_sum(){
        $sql='SELECT COUNT(*) AS sum FROM req UNION all SELECT COUNT(*) AS sum FROM (SELECT pkg FROM req GROUP BY pkg, launcher) AS pkgs UNION all SELECT COUNT(*) AS sum FROM (SELECT icon_pack FROM req GROUP BY icon_pack HAVING COUNT(icon_pack) > 20) AS iconPacks';
        $res=$this->db->query($sql);
        $r=$res->fetch_all();
        $this->_json(array("result"=>array("reqTimes"=>$r[0][0],"apps"=>$r[1][0],"iconPacks"=>$r[2][0])),0,0);
    }
    function nano_api_stats_month(){
        $sql1='SELECT ips.label, pkgs.* FROM (SELECT icon_pack AS pkg, COUNT(*) AS reqs FROM req WHERE time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY icon_pack HAVING reqs > 32 ORDER BY reqs DESC) AS pkgs LEFT JOIN icon_pack AS ips ON pkgs.pkg = ips.pkg';
        $sql2='SELECT pkg, COUNT(*) AS users FROM (SELECT icon_pack AS pkg FROM req WHERE time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY icon_pack, device_id) AS t0 GROUP BY pkg HAVING users > 4 ORDER BY users DESC';
        $res=$this->db->query($sql1);
        $r1=$res->fetch_all(MYSQLI_ASSOC);
        $res=$this->db->query($sql2);
        $r2=$res->fetch_all(MYSQLI_ASSOC);
        foreach($r1 as &$i1){
            foreach($r2 as $i2){
                if($i2['pkg']==$i1['pkg']){
                    $i1['users']=$i2['users'];break;
                }
            }
        }
        $this->_json(array("result"=>$r1,"msg"=>"success"),0,0);
    }
    function nano_api_trend_week($ipack){
        $ipack=addslashes($ipack);
    }
    function api_travis_log(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://s3.amazonaws.com/archive.travis-ci.org/".$_POST['url']);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,15);
        $data = curl_exec($ch);
        $err=curl_error($ch);
        curl_close($ch);
        echo $data;
    }
    
    function _json($data,$code=0,$status=null){
        if($code!=0){
            $msg="API Error";
            if(isset($data['msg']))$msg=$data['msg'];
            header("HTTP/1.1 ".$code." ".$msg);
        }
        if(!isset($data['code']))$data['code']=$code;
        if(!isset($data['status'])&&$status!==null)$data['status']=$status;
        header('Content-type: application/json');
    	die($this->_prettyJson($data));
    }
    function _prettyJson($data){
        $json=json_encode($data);
        $result = ''; 
    	$pos = 0; 
    	$strLen = strlen($json); 
    	$indentStr = '    '; 
    	$newLine = "\n"; 
    	$prevChar = ''; 
    	$outOfQuotes = true; 
    	for ($i=0; $i<=$strLen; $i++) { 
    		$char = substr($json, $i, 1); 
    		if ($char == '"' && $prevChar != '\\') { 
    			$outOfQuotes = !$outOfQuotes; 
    		} else if(($char == '}' || $char == ']') && $outOfQuotes) { 
    			$result .= $newLine; 
    			$pos --; 
    			for ($j=0; $j<$pos; $j++) { 
    			$result .= $indentStr; 
    		} 
    	} 
    	$result .= $char; 
    	if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) { 
    		$result .= $newLine; 
    		if ($char == '{' || $char == '[') { 
    			$pos ++; 
    		} 
    		for ($j = 0; $j < $pos; $j++) { 
    			$result .= $indentStr; 
    		} 
    	} 
    		$prevChar = $char; 
    	} 
        return $result;
    }
    function _sql($array, $type='insert', $exclude = array()){
     
    $sql = '';
    if(count($array) > 0){
      foreach ($exclude as $exkey) {
        unset($array[$exkey]);//剔除不要的key
      }
 
      if('insert' == $type){
        $keys = array_keys($array);
        $values = array_values($array);
        $col = implode("`, `", $keys);
        $val = implode("', '", $values);
        $sql = "(`$col`) values('$val')";
      }else if('update' == $type){
        $tempsql = '';
        $temparr = array();
        foreach ($array as $key => $value) {
          $tempsql = "`$key` = '$value'";
          $temparr[] = $tempsql;
        }
 
        $sql = implode(",", $temparr);
      }
    }
    return $sql;
  }
}
new GxIconApi();
