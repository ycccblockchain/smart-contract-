<?php
error_reporting(0);
set_time_limit(600);

define('THINKSNS_INSTALL', true);
define('THINKSNS_ROOT', str_replace('\\', '/', substr(dirname(__FILE__), 0, -7)));

//session
ini_set('session.cookie_httponly', 1);
//session
if (strtolower(ini_get('session.save_handler')) == 'files') {
    $session_dir = THINKSNS_ROOT.'/data/session/';
    if (!is_dir($session_dir)) {
        mkdir($session_dir, 0777, true);
    }
    session_save_path($session_dir);
}
session_start();

$_TSVERSION = '4';

include 'install_function.php';
include 'install_lang.php';

$timestamp = time();
$ip = getip();
$installfile = 'ThinkSNS.sql';
$installdbname = 'thinksns_4_r2';
$thinksns_config_file = 'config.inc.php';
$_SESSION['thinksns_install'] = $timestamp;

header('Content-Type: text/html; charset=utf-8');

if (file_exists(THINKSNS_ROOT.'/data/install.lock')) {
    echo $i_message['install_lock'];
    exit;

} elseif (!is_readable($installfile)) {
    echo $i_message['install_dbFile_error'];
    exit;
}

$quit = false;
$msg = $alert = $link = $sql = $allownext = '';

$PHP_SELF = addslashes(htmlspecialchars($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']));
set_magic_quotes_runtime(0);
if (!get_magic_quotes_gpc()) {
    addS($_POST);
    addS($_GET);
}
@extract($_POST);
@extract($_GET);
?>
<html>
<head>
<title><?php echo $i_message['install_title']; ?></title>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<link href="images/style.css" rel="stylesheet" type="text/css" />
<body>
<div id='content'>
<div id='pageheader'>
	<div id="logo">
		<img src="images/thinksns.gif" border="0" alt="ThinkSNS" />
		<span class="h1"><?php echo $i_message['install_wizard']; ?></span>
	</div>
</div>
<div id='innercontent'>
<?php
if (!$v) {
    ?>

<h2><?php echo $i_message['install_license_title']; ?></h2>
<p>
<textarea class="textarea" readonly cols="50">
<?php echo $i_message['install_license']; ?>
</textarea>
</p>
<form action="install.php?v=2" method="post">
<p class="center"><input type="submit" style="width:200px;" class="submit" name="next" value="Agreement" /></p>
</form>
<?php
} elseif ($v == '2') {
        if ($agree == 'no') {
            echo '<script>alert('.$i_message['install_disagree_license'].');history.go(-1)</script>';
        }
        $dirarray = array(
        'data',
        'storage',
        'install',
        'config',
    );
        $writeable = array();
        foreach ($dirarray as $key => $dir) {
            if (writable($dir)) {
                $writeable[$key] = $dir.result(1, 0);
            } else {
                $writeable[$key] = $dir.result(0, 0);
                $quit = true;
            }
        } ?>
<h2><?php echo $i_message['install_env']; ?></h2>
<div class="shade">
<h5><?php echo $i_message['php_os']; ?>&nbsp;&nbsp;<span class="p"><?php echo PHP_OS;
        result(1, 1); ?></span></h5>

<h5><?php echo $i_message['php_version']; ?>&nbsp;&nbsp;<span class="p"><?php
echo PHP_VERSION;
        if (version_compare(PHP_VERSION, '5.3.12', '<')) {
            result(0, 1);
            $quit = true;
        } else {
            result(1, 1);
        } ?></span></h5>

<h5><?php echo $i_message['php_memory']; ?>&nbsp;&nbsp;<span class="p"><?php
echo $i_message['support'],'/',@ini_get('memory_limit');
        if ((int) @ini_get('memory_limit') < (int) '32M') {
            result(0, 1);
            $quit = true;
        } else {
            result(1, 1);
        } ?></span></h5>

<h5><?php echo $i_message['php_session']; ?>&nbsp;&nbsp;<span class="p"><?php
$session_path = @ini_get('session.save_path');
        if (!isset($_SESSION['thinksns_install'])) {
            echo '<span class="red">'.$i_message['php_session_error'].': '.$session_path.'</span>';
            result(0, 1);
            $quit = true;
        } else {
            echo $i_message['support'];
            result(1, 1);
        } ?></span></h5>

<h5><?php echo $i_message['file_upload']; ?>&nbsp;&nbsp;<spam class="p"><?php
if (@ini_get('file_uploads')) {
            echo $i_message['support'],'/',@ini_get('upload_max_filesize');
        } else {
            echo '<span class="red">'.$i_message['unsupport'].'</span>';
        }
        result(1, 1); ?></spam></h5>

<h5><?php echo $i_message['mysql']; ?>&nbsp;&nbsp;<span class="p"><?php
if (function_exists('mysql_connect')) {
            echo $i_message['support'];
            result(1, 1);
        } else {
            echo '<span class="red">'.$i_message['mysql_unsupport'].'</span>';
            result(0, 1);
            $quit = true;
        } ?></span></h5>

<h5><?php echo $i_message['php_extention']; ?></h5>
<p>&nbsp;&nbsp;
<?php
if (extension_loaded('mysql')) {
            echo 'mysql:'.$i_message['support'];
            result(1, 1);
        } else {
            echo '<span class="red">'.$i_message['php_extention_unload_mysql'].'</span>';
            result(0, 1);
            $quit = true;
        } ?></p>
<p>&nbsp;&nbsp;
<?php
if (extension_loaded('gd')) {
            echo 'gd:'.$i_message['support'];
            result(1, 1);
        } else {
            echo '<span class="red">'.$i_message['php_extention_unload_gd'].'</span>';
            result(0, 1);
            $quit = true;
        } ?></p>
<p>&nbsp;&nbsp;
<?php
if (extension_loaded('curl')) {
            echo 'curl:'.$i_message['support'];
            result(1, 1);
        } else {
            echo '<span class="red">'.$i_message['php_extention_unload_curl'].'</span>';
            result(0, 1);
            $quit = true;
        } ?></p>
<p>&nbsp;&nbsp;
<?php
if (extension_loaded('mbstring')) {
            echo 'mbstring:'.$i_message['support'];
            result(1, 1);
        } else {
            echo '<span class="red">'.$i_message['php_extention_unload_mbstring'].'</span>';
            result(0, 1);
            $quit = true;
        } ?></p>

</div>
<h2><?php echo $i_message['dirmod']; ?></h2>
<div class="shade">
<?php
foreach ($writeable as $value) {
            echo '<p>'.$value.'</p>';
        } ?>

</div>
<p class="center">
	<form method="get" action='install.php?v=3' style="text-align: center;">
	<input type="hidden" name="v" value="3">
	<input style="width:200px;" type="submit" class="submit" value="<?php echo $i_message['install_next']; ?>" <?php if ($quit) {
            echo 'disabled="disabled"';
        } ?>>
	</form>
</p>
<?php
    } elseif ($v == '3') {
        ?>
<!-- <h2><?php echo $i_message['install_setting']; ?></h2> -->
<form method="post" action="install.php?v=4" id="install" onSubmit="return check(this);">
	<h2><?php echo $i_message['install_mysql']; ?></h2>
<div class="shade">

<h5><?php echo $i_message['install_mysql_host']; ?></h5>

<p><input type="text" name="db_host" value="localhost" size="40" class='input' placeholder="<?php echo $i_message['install_mysql_host_intro']; ?>" /></p>

<h5><?php echo $i_message['install_mysql_username']; ?></h5>
<p><input type="text" name="db_username" value="root" size="40" class='input' /></p>

<h5><?php echo $i_message['install_mysql_password']; ?></h5>
<p><input type="password" name="db_password" value="" size="40" class='input' /></p>

<h5><?php echo $i_message['install_mysql_name']; ?></h5>
<p><input type="text" name="db_name" value="<?php echo $installdbname; ?>" size="40" class='input' />
</p>

<h5><?php echo $i_message['install_mysql_prefix']; ?></h5>

<p><input type="text" name="db_prefix" value="ts_" size="40" class='input' placeholder="<?php echo $i_message['install_mysql_prefix_intro']; ?>" /></p>

<h5><?php echo $i_message['site_url']; ?></h5>
<p><?php echo $i_message['site_url_intro']; ?></p>
<p><input type="text" name="site_url" value="<?php echo 'http://'.$_SERVER['HTTP_HOST'].rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/'); ?>" size="40" class='input' /></p>

</div>

<h2><?php echo $i_message['founder']; ?></h2>
<div class="shade">

<h5><?php echo $i_message['auto_increment']; ?></h5>
<p><input type="text" name="first_user_id" value="1" size="40" class='input' /></p>

<h5><?php echo $i_message['install_founder_email']; ?></h5>
<p><input type="text" name="email" value="admin@admin.com" size="40" class='input' /></p>

<h5><?php echo $i_message['install_founder_password']; ?></h5>
<p><input type="password" name="password" value="" size="40" class='input' /></p>

<h5><?php echo $i_message['install_founder_rpassword']; ?></h5>
<p><input type="password" name="rpassword" value="" size="40" class='input' /></p>


</div>
<div class="">
	<input type="submit" class="submit" name="next" value="<?php echo $i_message['install_next']; ?>" style="width:200px;margin-left: 23px;">
</form>
</div>
<script type="text/javascript" language="javascript">
function check(obj)
{
	if (!obj.db_host.value)
	{
		alert('<?php echo $i_message['install_mysql_host_empty']; ?>');
		obj.db_host.focus();
		return false;
	}
	else if (!obj.db_username.value)
	{
		alert('<?php echo $i_message['install_mysql_username_empty']; ?>');
		obj.db_username.focus();
		return false;
	}
	else if (!obj.db_name.value)
	{
		alert('<?php echo $i_message['install_mysql_name_empty']; ?>');
		obj.db_name.focus();
		return false;
	}
	else if (obj.password.value.length < 6)
	{
		alert('<?php echo $i_message['install_founder_password_length']; ?>');
		obj.password.focus();
		return false;
	}
	else if (obj.password.value != obj.rpassword.value)
	{
		alert('<?php echo $i_message['install_founder_rpassword_error']; ?>');
		obj.rpassword.focus();
		return false;
	}
	else if (!obj.email.value)
	{
		alert('<?php echo $i_message['install_founder_email_empty']; ?>');
		obj.email.focus();
		return false;
	}
	return true;
}
</script>
<?php
    } elseif ($v == '4') {
        if (empty($db_host) || empty($db_username) || empty($db_name) || empty($db_prefix)) {
            $msg .= '<p>'.$i_message['mysql_invalid_configure'].'<p>';
            $quit = true;
        } elseif (!@mysql_connect($db_host, $db_username, $db_password)) {
            $msg .= '<p>'.mysql_error().'</p>';
            $quit = true;
        }
        if (strstr($db_prefix, '.')) {
            $msg .= '<p>'.$i_message['mysql_invalid_prefix'].'</p>';
            $quit = true;
        }

        if (strlen($password) < 6) {
            $msg .= '<p>'.$i_message['founder_invalid_password'].'</p>';
            $quit = true;
        } elseif ($password != $rpassword) {
            $msg .= '<p>'.$i_message['founder_invalid_rpassword'].'</p>';
            $quit = true;
        } elseif (!preg_match('/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,3}$/i', $email)) {
            $msg .= '<p>'.$i_message['founder_invalid_email'].'</p>';
            $quit = true;
        } else {
            $forbiddencharacter = array('\\', '&', ' ', "'", '"', '/', '*', ',', '<', '>', "\r", "\t", "\n", '#', '$', '(', ')', '%', '@', '+', '?', ';', '^');
            foreach ($forbiddencharacter as $value) {
                if (strpos($username, $value) !== false) {
                    $msg .= '<p>'.$i_message['	'].'</p>';
                    $quit = true;
                    break;
                }
            }
        }

        if ($quit) {
            $allownext = 'disabled="disabled"'; ?>
		<?php
        echo $msg;
        } else {
            $config_file_content = array();
            $config_file_content['db_host'] = $db_host;
            $config_file_content['db_name'] = $db_name;
            $config_file_content['db_username'] = $db_username;
            $config_file_content['db_password'] = $db_password;
            $config_file_content['db_prefix'] = $db_prefix;
            $config_file_content['db_pconnect'] = 0;
            $config_file_content['db_charset'] = 'utf8';
            $config_file_content['dbType'] = 'MySQL';

            $default_manager_account = array();
            $default_manager_account['email'] = $email;
            $default_manager_account['password'] = md5(md5($password).'11111');

            $_SESSION['config_file_content'] = $config_file_content;
            $_SESSION['default_manager_account'] = $default_manager_account;
            $_SESSION['first_user_id'] = $first_user_id;
            $_SESSION['site_url'] = $site_url; ?>
	<div class="botBorder">
		<p><?php echo $i_message['install_founder_name'], ': ', $email?></p>
		<p><?php echo $i_message['install_founder_password'], ': ', $password; ?></p>
	</div>
<?php
        } ?>
	<div class="botBorder">
