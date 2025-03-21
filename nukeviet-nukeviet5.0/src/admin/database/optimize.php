<?php

/**
 * NukeViet Content Management System
 * @version 5.x
 * @author VINADES.,JSC <contact@vinades.vn>
 * @copyright (C) 2009-2025 VINADES.,JSC. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://github.com/nukeviet The NukeViet CMS GitHub project
 */

if (!defined('NV_IS_FILE_DATABASE')) {
    exit('Stop!!!');
}

$tables = $nv_Request->get_title('tables', 'post');

if (empty($tables)) {
    $tables = [];
} else {
    $tables = explode(',', $tables);
}
$checkss = $nv_Request->get_title('checkss', 'post', '');
if ($checkss !== NV_CHECK_SESSION) {
    nv_htmlOutput('Wrong session!!!');
}

nv_insert_logs(NV_LANG_DATA, $module_name, $nv_Lang->getModule('optimize'), '', $admin_info['userid']);

$totalfree = 0;
$tabs = [];

$result = $db->query("SHOW TABLE STATUS LIKE '" . $db_config['prefix'] . "\_%'");
while ($item = $result->fetch()) {
    if (empty($tables) or (!empty($tables) and in_array($item['name'], $tables, true))) {
        $totalfree += $item['data_free'];
        $tabs[] = substr($item['name'], strlen($db_config['prefix']) + 1);
        $db->query('OPTIMIZE TABLE ' . $item['name']);
    }
}
$result->closeCursor();

$totalfree = !empty($totalfree) ? nv_convertfromBytes($totalfree) : 0;

$content = $nv_Lang->getModule('optimize_result', implode(', ', $tabs), $totalfree);

include NV_ROOTDIR . '/includes/header.php';
echo $content;
include NV_ROOTDIR . '/includes/footer.php';
