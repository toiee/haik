<?php
// $Id: recent.inc.php,v 1.23 2006/03/05 14:59:29 henoheno Exp $
// Copyright (C)
//   2002-2006 PukiWiki Developers Team
//   2002      Y.MASUI http://masui.net/pukiwiki/ masui@masui.net
// License: GPL version 2
//
// Recent plugin -- Show RecentChanges list
//   * Usually used at 'MenuBar' page
//   * Also used at special-page, without no #recnet at 'MenuBar'

// Default number of 'Show latest N changes'
define('PLUGIN_RECENT_DEFAULT_LINES', 10);

// Limit number of executions
define('PLUGIN_RECENT_EXEC_LIMIT', 2); // N times per one output

// Place of the cache of 'RecentChanges'
define('PLUGIN_RECENT_CACHE', CACHE_DIR . 'recent.dat');

function plugin_recent_action()
{
    global $vars, $date_format, $show_passage, $layout_pages;
    
    $qt = get_qt();
    
    if (ss_admin_check())
    {
        if (exist_plugin('app_config'))
        {
            $qt->setv('template_name', 'content');
            do_plugin_init('app_config');
        }
        
        $recent_pages = array();
        if (file_exists(CACHE_DIR.ORGM_UPDATE_CACHE))
        {
            $lines = file_head(CACHE_DIR.ORGM_UPDATE_CACHE, 60);
            foreach ($lines as $line)
            {
                list($time, $pagename) = explode("\t", $line);
                $pagename = trim($pagename);
                if ($pagetitle = get_page_title($pagename))
                {
                    $recent_pages[] = array(
                        'name'  => $pagename,
                        'title' => array_key_exists($pagename, $layout_pages) ? $layout_pages[$pagename] : $pagetitle,
                        'date'  => get_date($date_format, $time)
                    );
                }
            }
        }

        $body  = '<div class="page-header">更新履歴</div>';
        $body .= '<div id="orgm_recent_list" class="container">';
        $body .= '    <ul class="nav nav-list">';
        $temp_date = '';
        foreach($recent_pages as $p)
        {
            if ($temp_date != $p['date'])
            {
                $body .= '<li class="nav-header">'.h($p['date']).'</li>';
                $temp_date = $p['date'];
            }
            $body .= '<li>';
            $title = '';
            if ($p['title'] != $p['name'])
            {
                $title = '　<small class="muted">'.h($p['title']).'</small>';
            }
            $body .= '<a href="'.h(get_page_url($p['name'])).'"><strong>　'.h($p['name']).'</strong>'.$title.'</a></li>';
            $body .= '</li>';
        }
        $body .= '    </ul>';
        $body .= '</div>';

        $qt->setv('menu', '');
        
        return array("msg"=> '', "body"=> $body);
    }
    
}


function plugin_recent_convert()
{
    global $vars, $date_format, $show_passage;
    static $exec_count = 1;
    $qm = get_qm();
    $qt = get_qt();

    $recent_lines = PLUGIN_RECENT_DEFAULT_LINES;
    if (func_num_args() > 1) {

        $args = func_get_args();
        array_pop($args);

        if (! is_numeric($args[0]) || isset($args[1])) {
            return $qm->m['plg_recent']['err_usage'] . '<br />';
        } else {
            $recent_lines = $args[0];
        }
    }
    
    //---- キャッシュのための処理を登録 -----
    if($qt->create_cache) {
        $args = func_get_args();
        return $qt->get_dynamic_plugin_mark(__FUNCTION__, $args);
    }
    //------------------------------------


    // Show only N times
    if ($exec_count > PLUGIN_RECENT_EXEC_LIMIT) {
        return $qm->m['plg_recent']['err_limit'] . '<br />' . "\n";
    } else {
        ++$exec_count;
    }

    if (! file_exists(PLUGIN_RECENT_CACHE))
        return $qm->m['plg_recent']['err_file_notfound'] . '<br />';

    // Get latest N changes
    $lines = file_head(PLUGIN_RECENT_CACHE, $recent_lines);

    if ($lines == FALSE) return $qm->m['plg_recent']['err_file_cannotopen']. '<br />' . "\n";

    $script = get_script_uri();
    $date = $items = '';
    foreach ($lines as $line) {
        list($time, $page) = explode("\t", rtrim($line));

        $_date = get_date($date_format, $time);
        if ($date != $_date) {
            // End of the day
            if ($date != '') $items .= '</ul>' . "\n";

            // New day
            $date = $_date;
            $items .= '<strong>' . $date . '</strong>' . "\n" .
                '<ul class="recent_list">' . "\n";
        }

        $s_page = h($page);
        
        //customized by hokuken.com
        $s_page  = get_page_title($page);
        
        if($page == $vars['page']) {
            // No need to link to the page you just read, or notify where you just read
            $items .= ' <li>' . $s_page . '</li>' . "\n";
        } else {
            $r_page = rawurlencode($page);
            $passage = $show_passage ? ' ' . get_passage($time) : '';
            $items .= ' <li><a href="' . get_page_url($page) . '"' . 
                ' title="' . $s_page . $passage . '">' . $s_page . '</a></li>' . "\n";
        }
    }
    // End of the day
    if ($date != '') $items .= '</ul>' . "\n";

    return sprintf($qm->m['plg_recent']['frame'], count($lines), $items);
}

/* End of file recent.inc.php */
/* Location: /app/haik-contents/plugin/recent.inc.php */