<?php
/**
 *   ORIGAMI APP initialize
 *   -------------------------------------------
 *   lib/init.php
 *   
 *   Copyright (c) 2013 hokuken
 *   http://hokuken.com/
 *   
 *   created  : 13/01/17
 *   modified :
 *   
 *   Description
 *   
 *   Usage :
 *   
 */

define('APP_NAME', 'haik');
define('S_VERSION', '1.4.7');//PukiWiki version
define('APP_VERSION', '0.15.0-RC');

// URLs
define('APP_OFFICIAL_SITE', 'http://toiee.jp/haik/');
define('APP_MANUAL_SITE', APP_OFFICIAL_SITE . 'help/');
define('APP_MEMBER_SITE', 'https://ensmall.net/p/haik/');

/////////////////////////////////////////////////
// Init server variables

foreach (array('SCRIPT_NAME', 'SERVER_ADMIN', 'SERVER_NAME',
        'SERVER_PORT', 'SERVER_SOFTWARE') as $key) {
        define($key, isset($_SERVER[$key]) ? $_SERVER[$key] : '');
        if(isset(${$key})) unset(${$key});
        if(isset($_SERVER[$key])) unset($_SERVER[$key]);
        if(isset($HTTP_SERVER_VARS[$key])) unset($HTTP_SERVER_VARS[$key]);
}

/////////////////////////////////////////////////
// Init grobal variables

$foot_explain = array();	// Footnotes
$related      = array();	// Related pages
$head_tags    = array();	// HTML tags in <head></head>

//set GENERATOR
$head_tags[] = '<meta name="GENERATOR" content="'. APP_NAME . ' ' . APP_VERSION .'">' . "\n";

/////////////////////////////////////////////////
// Time settings

define('LOCALZONE', date('Z'));
define('UTIME', time() - LOCALZONE);
define('MUTIME', getmicrotime());

/////////////////////////////////////////////////
// Require INI_FILE

define('INI_FILE',  CONFIG_DIR . 'pukiwiki.ini.php');
$die = '';
if (! file_exists(INI_FILE) || ! is_readable(INI_FILE)) {
	$die .= 'File is not found. (INI_FILE)' . "\n";
} else {
	require(INI_FILE);
}
if ($die) die_message(nl2br("\n\n" . $die));


/////////////////////////////////////////////////
// ! エラーチェック
$app_err = array(
	'files' => array(),
	'dirs'  => array(),
	'pages' => array(),
	'php'   => array(),
);

// ディレクトリチェック
foreach (array('DATA_DIR', 'DIFF_DIR', 'BACKUP_DIR', 'CACHE_DIR', 'META_DIR', 'CONFIG_DIR', 'SKIN_DIR') as $dir)
{
	if ( ! is_writable(constant($dir)))
	{
		$app_err['dirs'][] = constant($dir);
	}
}

// ファイルチェック
foreach (array($app_ini_path) as $file)
{
	if ( ! is_writable($file))
	{
		$app_err['files'][] = $file;
	}
}


/////////////////////////////////////////////////
// INI_FILE: 言語設定

define('SOURCE_ENCODING', 'UTF-8');
define('CONTENT_CHARSET', 'UTF-8');

// mbstring の設定がおかしい場合
if (ini_get('mbstring.encoding_translation')
	&& ini_get('mbstring.http_input') != 'pass'
	&& ini_get('mbstring.http_input') != 'auto'
	&& ( strtoupper( ini_get('mbstring.internal_encoding') ) != SOURCE_ENCODING )
)
{

	$app_err['php']['mbstring.encoding_translation'] = TRUE;
}

if ( ! defined('MB_LANGUAGE'))
{
	switch (LANG)
	{
		case 'ja':
			$language = 'Japanese';
		case 'en':
			$language = 'English';
		default:
			$language = 'uni';
	}
	define('MB_LANGUAGE', $language);
	mb_language(MB_LANGUAGE);
	unset($language);
}

mb_internal_encoding(SOURCE_ENCODING);
ini_set('mbstring.http_input', 'pass');
mb_http_output('pass');
mb_detect_order('auto');

/////////////////////////////////////////////////
// INI_FILE: Require LANG_FILE

$lang_ini_file = CONFIG_DIR . 'lang.ini.php';

if (! file_exists($lang_ini_file) || ! is_readable($lang_ini_file))
{
	die_message(__('言語設定ファイル lang.ini.php が見つからない、あるいは読み取り権限がありません。'));
}

define('PKWK_ENCODING_HINT', isset($languages[LANG]['hint']) ? $languages[LANG]['hint'] : '');
unset($lang_ini_file);



/////////////////////////////////////////////////
// LANG_FILE: Init severn days of the week

$weeklabels = array('日','月','火','水','木','金','土');

/////////////////////////////////////////////////
// INI_FILE: Init $script

$default_script = $script;
if ( !isset($script) || $script == '') {
	$script = get_script_uri(); // Init automanually
} else {
	get_script_uri($script); // Init matically
}

//ssl接続用の$scriptを生成
$default_script_ssl = isset($script_ssl) ? $script_ssl : '';
if( ! isset($script_ssl) || $script_ssl == '' ){
	$script_ssl = preg_replace('/^http:/', 'https:', $script);
}

//is_httpsメソッド用など
$init_scripts = array('normal'=>$script, 'ssl'=>$script_ssl);


/////////////////////////////////////////////////
// INI_FILE: $agents:  UserAgentの識別

$ua = 'HTTP_USER_AGENT';
$user_agent = $matches = array();

$user_agent['agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
if(isset(${$ua})) unset(${$ua});
if(isset($_SERVER[$ua])) unset($_SERVER[$ua]);
if(isset($HTTP_SERVER_VARS[$ua])) unset($HTTP_SERVER_VARS[$ua]);
unset($ua);

foreach ($agents as $agent) {
	if (preg_match($agent['pattern'], $user_agent['agent'], $matches)) {
		$user_agent['profile'] = isset($agent['profile']) ? $agent['profile'] : '';
		$user_agent['name']    = isset($matches[1]) ? $matches[1] : '';	// device or browser name
		$user_agent['vers']    = isset($matches[2]) ? $matches[2] : ''; // 's version
		break;
	}
}
unset($agents, $matches);

// Profile-related init and setting
define('UA_PROFILE', isset($user_agent['profile']) ? $user_agent['profile'] : '');

define('UA_INI_FILE', CONFIG_DIR . UA_PROFILE . '.ini.php');
if (! file_exists(UA_INI_FILE) || ! is_readable(UA_INI_FILE)) {
	die_message('UA_INI_FILE for "' . UA_PROFILE . '" not found.');
} else {
	require(UA_INI_FILE); // Also manually
}

define('UA_NAME', isset($user_agent['name']) ? $user_agent['name'] : '');
define('UA_VERS', isset($user_agent['vers']) ? $user_agent['vers'] : '');
unset($user_agent);	// Unset after reading UA_INI_FILE



/////////////////////////////////////////////////
// 必須のページが存在しなければ、空のファイルを作成する

if ( ! $app_start)
{
	foreach(array($defaultpage, $whatsnew, $interwiki) as $page){
		if (! is_page($page)) touch(get_filename($page));
	}
}

/////////////////////////////////////////////////
// 外部からくる変数のチェック

// Prohibit $_GET attack
foreach (array('msg', 'pass') as $key) {
	if (isset($_GET[$key])) die_message('Sorry, already reserved: ' . $key . '=');
}

// Expire risk
unset($HTTP_GET_VARS, $HTTP_POST_VARS);	//, 'SERVER', 'ENV', 'SESSION', ...
unset($_REQUEST);	// Considered harmful

// Remove null character etc.
$_GET    = input_filter($_GET);
$_POST   = input_filter($_POST);
$_COOKIE = input_filter($_COOKIE);

// 文字コード変換 ($_POST)
// <form> で送信された文字 (ブラウザがエンコードしたデータ) のコードを変換
// POST method は常に form 経由なので、必ず変換する
//
if (isset($_POST['encode_hint']) && $_POST['encode_hint'] != '') {
	// do_plugin_xxx() の中で、<form> に encode_hint を仕込んでいるので、
	// encode_hint を用いてコード検出する。
	// 全体を見てコード検出すると、機種依存文字や、妙なバイナリ
	// コードが混入した場合に、コード検出に失敗する恐れがある。
	$encode = mb_detect_encoding($_POST['encode_hint']);
	mb_convert_variables(SOURCE_ENCODING, $encode, $_POST);

} else if (isset($_POST['charset']) && $_POST['charset'] != '') {
	// TrackBack Ping で指定されていることがある
	// うまくいかない場合は自動検出に切り替え
	if (mb_convert_variables(SOURCE_ENCODING,
	    $_POST['charset'], $_POST) !== $_POST['charset']) {
		mb_convert_variables(SOURCE_ENCODING, 'auto', $_POST);
	}

} else if (! empty($_POST)) {
	// 全部まとめて、自動検出／変換
	mb_convert_variables(SOURCE_ENCODING, 'auto', $_POST);
}

// 文字コード変換 ($_GET)
// GET method は form からの場合と、<a href="http://script/?key=value> の場合がある
// <a href...> の場合は、サーバーが rawurlencode しているので、コード変換は不要
if (isset($_GET['encode_hint']) && $_GET['encode_hint'] != '')
{
	// form 経由の場合は、ブラウザがエンコードしているので、コード検出・変換が必要。
	// encode_hint が含まれているはずなので、それを見て、コード検出した後、変換する。
	// 理由は、post と同様
	$encode = mb_detect_encoding($_GET['encode_hint']);
	mb_convert_variables(SOURCE_ENCODING, $encode, $_GET);
}


/////////////////////////////////////////////////
// QUERY_STRINGを取得

// cmdもpluginも指定されていない場合は、QUERY_STRINGを
// ページ名かInterWikiNameであるとみなす
$arg = '';
if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']) {
	$arg = & $_SERVER['QUERY_STRING'];
} else if (isset($_SERVER['argv']) && ! empty($_SERVER['argv'])) {
	$arg = & $_SERVER['argv'][0];
}
if (PKWK_QUERY_STRING_MAX && strlen($arg) > PKWK_QUERY_STRING_MAX) {
	// Something nasty attack?
	pkwk_common_headers();
	sleep(1);	// Fake processing, and/or process other threads
	echo('Query string too long');
	exit;
}
$arg = input_filter($arg); // \0 除去

// unset QUERY_STRINGs
foreach (array('QUERY_STRING', 'argv', 'argc') as $key) {
	if(isset(${$key})) unset(${$key});
	if(isset($_SERVER[$key])) unset($_SERVER[$key]);
	if(isset($HTTP_SERVER_VARS[$key])) unset($HTTP_SERVER_VARS[$key]);
}
// $_SERVER['REQUEST_URI'] is used at func.php NOW
if(isset($REQUEST_URI)) unset($REQUEST_URI);
if(isset($HTTP_SERVER_VARS['REQUEST_URI'])) unset($HTTP_SERVER_VARS['REQUEST_URI']);

$arg = mb_convert_encoding($arg, SOURCE_ENCODING, 'auto');

/////////////////////////////////////////////////
// QUERY_STRINGを分解してコード変換し、$_GET に上書き

// URI を urlencode せずに入力した場合に対処する
parse_str($arg, $output);
$_GET = array_merge($_GET, $output);

/////////////////////////////////////////////////
// GET, POST, COOKIE

$get    = & $_GET;
$post   = & $_POST;
$cookie = & $_COOKIE;

// GET + POST = $vars
if (empty($_POST)) {
	$vars = & $_GET;  // Major pattern: Read-only access via GET
} else if (empty($_GET)) {
	$vars = & $_POST; // Minor pattern: Write access via POST etc.
} else {
	$vars = array_merge_deep($_GET, $_POST); // Considered reliable than $_REQUEST
}

// 入力チェック: 'cmd=' and 'plugin=' can't live together
if (isset($vars['cmd']) && isset($vars['plugin']))
	die('Using both cmd= and plugin= is not allowed');

// 入力チェック: cmd, plugin の文字列は英数字以外ありえない
foreach(array('cmd', 'plugin') as $var) {
	if (isset($vars[$var]) && ! preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $vars[$var]))
		unset($get[$var], $post[$var], $vars[$var]);
}

// 整形: page, strip_bracket()
if (isset($vars['page'])) {
	$get['page'] = $post['page'] = $vars['page']  = strip_bracket($vars['page']);
} else {
	$get['page'] = $post['page'] = $vars['page'] = '';
}

// 整形: msg, 改行を取り除く
if (isset($vars['msg'])) {
	$get['msg'] = $post['msg'] = $vars['msg'] = str_replace("\r", '', $vars['msg']);
}

// plugin で画面を切り替える用
$is_plugin_page = false;

// cmdもpluginも指定されていない場合は、QUERY_STRINGをページ名かInterWikiNameであるとみなす
if (! isset($vars['cmd']) && ! isset($vars['plugin']))
{
	if (isset($vars['go']))
	{
		$t = get_tiny_page($vars['go']);
		header('Location: '.get_page_url($t));
		exit;
	}

	$get['cmd']  = $post['cmd']  = $vars['cmd']  = 'read';
	

	if ($arg == '') $arg = $defaultpage;
	$arg = rawurldecode($arg);
	$arg = strip_bracket($arg);
	$arg = input_filter($arg);
	$arg = strip_adcode($arg);
	$get['page'] = $post['page'] = $vars['page'] = $arg;
}

/////////////////////////////////////////////////
// 初期設定($WikiName,$BracketNameなど)
// $WikiName = '[A-Z][a-z]+(?:[A-Z][a-z]+)+';
// $WikiName = '\b[A-Z][a-z]+(?:[A-Z][a-z]+)+\b';
// $WikiName = '(?<![[:alnum:]])(?:[[:upper:]][[:lower:]]+){2,}(?![[:alnum:]])';
// $WikiName = '(?<!\w)(?:[A-Z][a-z]+){2,}(?!\w)';

// BugTrack/304暫定対処
$WikiName = '(?:[A-Z][a-z]+){2,}(?!\w)';

// $BracketName = ':?[^\s\]#&<>":]+:?';
$BracketName = '(?!\s):?[^\r\n\t\f\[\]<>#&":]+:?(?<!\s)';

// InterWiki
$InterWikiName = '(\[\[)?((?:(?!\s|:|\]\]).)+):(.+)(?(1)\]\])';

// 注釈
$NotePattern = '/\(\(((?:(?>(?:(?!\(\()(?!\)\)(?:[^\)]|$)).)+)|(?R))*)\)\)/ex';

/////////////////////////////////////////////////
// 初期設定(ユーザ定義ルール読み込み)
require(CONFIG_DIR . 'rules.ini.php');

/////////////////////////////////////////////////
// 初期設定(その他のグローバル変数)

// 現在時刻
$now = format_date(UTIME);

// 日時置換ルールを$line_rulesに加える
if ($usedatetime) $line_rules += $datetime_rules;
unset($datetime_rules);

// フェイスマークを$line_rulesに加える
if ($usefacemark) $line_rules += $facemark_rules;
unset($facemark_rules);

// 実体参照パターンおよびシステムで使用するパターンを$line_rulesに加える
//$entity_pattern = '[a-zA-Z0-9]{2,8}';
$entity_pattern = trim(join('', file(CACHE_DIR . 'entities.dat')));

$line_rules = array_merge(array(
	'&amp;(#[0-9]+|#x[0-9a-f]+|' . $entity_pattern . ');' => '&$1;',
	"\r"          => '<br>' . "\n",	/* 行末にチルダは改行 */
), $line_rules);

/* End of file init.php */
/* Location: /haik-contents/lib/init.php */
