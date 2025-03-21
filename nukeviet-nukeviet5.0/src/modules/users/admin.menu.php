<?php

/**
 * NukeViet Content Management System
 * @version 5.x
 * @author VINADES.,JSC <contact@vinades.vn>
 * @copyright (C) 2009-2025 VINADES.,JSC. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://github.com/nukeviet The NukeViet CMS GitHub project
 */

if (!defined('NV_ADMIN')) {
    exit('Stop!!!');
}

global $nv_Cache;

// Xác định cấu hình
$_mod_table = ($module_data == 'users') ? NV_USERS_GLOBALTABLE : $db_config['prefix'] . '_' . $module_data;
$sql = 'SELECT config, content FROM ' . $_mod_table . "_config WHERE config IN('access_admin', 'active_editinfo_censor')";
$_config_module = $nv_Cache->db($sql, 'config', $module_name);

$access_admin = $_config_module['access_admin']['content'];
$access_admin = unserialize($access_admin);
$active_editinfo_censor = $_config_module['active_editinfo_censor']['content'];

$allow_func = [
    'main',
    'getuserid'
];
$level = $admin_info['level'];

// Quyền thêm tài khoản
if (!empty($access_admin['access_addus'][$level])) {
    $submenu['user_add'] = $nv_Lang->getModule('user_add');
    $allow_func[] = 'user_add';
}

// Quyền kích hoạt tài khoản
if (!empty($access_admin['access_waiting'][$level])) {
    $submenu['user_waiting'] = $nv_Lang->getModule('member_wating');
    $allow_func[] = 'user_waiting';
    $allow_func[] = 'user_waiting_remail';
    $allow_func[] = 'setactive';
    $allow_func[] = 'setofficial';
}

// Quyền kiểm duyệt thông tin sửa
if (!empty($access_admin['access_editcensor'][$level])) {
    if ($active_editinfo_censor) {
        $submenu['editcensor'] = $nv_Lang->getModule('editcensor');
    }
    $allow_func[] = 'editcensor';
}

// Quyền sửa tài khoản
if (!empty($access_admin['access_editus'][$level])) {
    $allow_func[] = 'edit';
    $allow_func[] = 'edit_2step';
    $allow_func[] = 'edit_oauth';
}

// Quyền xóa tài khoản
if (!empty($access_admin['access_delus'][$level])) {
    $allow_func[] = 'del';
}

// Quyền quản lý nhóm
$access['checked_passus'] = !empty($access_admin['access_passus'][$level]) ? ' checked="checked" ' : '';
if (!empty($access_admin['access_groups'][$level])) {
    $submenu['groups'] = $nv_Lang->getGlobal('mod_groups');
    $allow_func[] = 'groups';
}

if ($module_data == 'users' and isset($admin_mods['authors'])) {
    $submenu['authors'] = $nv_Lang->getGlobal('mod_authors');
    $allow_func[] = 'authors';
}

if (defined('NV_IS_SPADMIN')) {
    if (!defined('NV_IS_USER_FORUM') and empty($global_config['idsite']) or $global_config['users_special']) {
        $submenu['question'] = $nv_Lang->getModule('question');
        $submenu['siteterms'] = $nv_Lang->getModule('siteterms');
        $submenu['fields'] = $nv_Lang->getModule('fields');
        $allow_func[] = 'question';
        $allow_func[] = 'siteterms';
        $allow_func[] = 'fields';
    }
    $submenu['config'] = $nv_Lang->getModule('config');
    $allow_func[] = 'config';
}
