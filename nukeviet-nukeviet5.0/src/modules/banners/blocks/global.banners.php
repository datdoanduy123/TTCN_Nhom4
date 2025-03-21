<?php

/**
 * NukeViet Content Management System
 * @version 5.x
 * @author VINADES.,JSC <contact@vinades.vn>
 * @copyright (C) 2009-2025 VINADES.,JSC. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://github.com/nukeviet The NukeViet CMS GitHub project
 */

if (!defined('NV_MAINFILE')) {
    exit('Stop!!!');
}

if (!nv_function_exists('nv_block_data_config_banners')) {
    /**
     * nv_block_data_config_banners()
     *
     * @param string $module
     * @param array  $data_block
     * @return string
     */
    function nv_block_data_config_banners($module, $data_block)
    {
        global $db, $language_array, $nv_Lang;

        $html = "<select name=\"config_idplanbanner\" class=\"form-select\">\n";
        $html .= '<option value="">' . $nv_Lang->getModule('idplanbanner') . "</option>\n";
        $query = 'SELECT * FROM ' . NV_BANNERS_GLOBALTABLE . "_plans WHERE (blang='" . NV_LANG_DATA . "' OR blang='') ORDER BY title ASC";
        $result = $db->query($query);

        while ($row_bpn = $result->fetch()) {
            $value = $row_bpn['title'] . ' (';
            $value .= ((!empty($row_bpn['blang']) and isset($language_array[$row_bpn['blang']])) ? $language_array[$row_bpn['blang']]['name'] : $nv_Lang->getModule('blang_all')) . ', ';
            $value .= $row_bpn['form'] . ', ';
            $value .= $row_bpn['width'] . 'x' . $row_bpn['height'] . 'px';
            $value .= ')';
            $sel = ($data_block['idplanbanner'] == $row_bpn['id']) ? ' selected' : '';

            $html .= '<option value="' . $row_bpn['id'] . '" ' . $sel . '>' . $value . "</option>\n";
        }

        $html .= "</select>\n";

        return '<div class="row mb-3"><label class="col-sm-3 col-form-label text-sm-end text-truncate fw-medium">' . $nv_Lang->getModule('idplanbanner') . ':</label><div class="col-sm-5">' . $html . '</div></div>';
    }

    /**
     * nv_block_data_config_banners_submit()
     *
     * @param string $module
     * @return array
     */
    function nv_block_data_config_banners_submit($module)
    {
        global $nv_Request, $nv_Lang;

        $return = [];
        $return['error'] = [];
        $return['config'] = [];
        $return['config']['idplanbanner'] = $nv_Request->get_int('config_idplanbanner', 'post', 0);

        if (empty($return['config']['idplanbanner'])) {
            $return['error'][] = $nv_Lang->getModule('idplanbanner');
        }

        return $return;
    }

    /**
     * nv_block_global_banners()
     *
     * @param array $block_config
     * @return string|void
     */
    function nv_block_global_banners($block_config)
    {
        global $global_config, $client_info;

        if ($global_config['idsite']) {
            $xmlfile = NV_ROOTDIR . '/' . NV_DATADIR . '/site_' . $global_config['idsite'] . '_bpl_' . $block_config['idplanbanner'] . '.xml';
        } else {
            $xmlfile = NV_ROOTDIR . '/' . NV_DATADIR . '/bpl_' . $block_config['idplanbanner'] . '.xml';
        }

        if (!file_exists($xmlfile)) {
            return '';
        }

        $xml = simplexml_load_file($xmlfile);

        if ($xml === false) {
            return '';
        }

        $width_banners = (int) ($xml->width);
        $height_banners = (int) ($xml->height);
        $array_banners = $xml->banners->banners_item;

        $array_banners_content = [];

        foreach ($array_banners as $banners) {
            $banners = (array) $banners;
            if ($banners['publ_time'] < NV_CURRENTTIME and ($banners['exp_time'] == 0 or $banners['exp_time'] > NV_CURRENTTIME)) {
                $banners['file_height'] = empty($banners['file_height']) ? 0 : round($banners['file_height'] * $width_banners / $banners['file_width']);
                $banners['file_width'] = $width_banners;
                if (!empty($banners['imageforswf']) and !empty($client_info['is_mobile'])) {
                    $banners['file_name'] = $banners['imageforswf'];
                    $banners['file_ext'] = nv_getextension($banners['file_name']);
                }
                $banners['file_alt'] = (!empty($banners['file_alt'])) ? $banners['file_alt'] : $banners['title'];
                $banners['file_image'] = NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . NV_BANNER_DIR . '/' . $banners['file_name'];
                $banners['link'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=banners&amp;' . NV_OP_VARIABLE . '=click&amp;id=' . $banners['id'] . '&amp;s=' . md5($banners['id'] . NV_CHECK_SESSION);
                if (!empty($banners['bannerhtml'])) {
                    $banners['bannerhtml'] = html_entity_decode($banners['bannerhtml'], ENT_COMPAT | ENT_HTML401, strtoupper($global_config['site_charset']));
                }
                $array_banners_content[] = $banners;
            }
        }

        if (!empty($array_banners_content)) {
            if ($xml->form == 'random') {
                shuffle($array_banners_content);
            } elseif ($xml->form == 'random_one') {
                $array_banners_content = [$array_banners_content[array_rand($array_banners_content)]];
            }
            unset($xml, $array_banners);

            $block_theme = get_tpl_dir([$global_config['module_theme'], $global_config['site_theme']], 'default', '/modules/banners/global.banners.tpl');
            $xtpl = new XTemplate('global.banners.tpl', NV_ROOTDIR . '/themes/' . $block_theme . '/modules/banners');

            foreach ($array_banners_content as $banners) {
                $xtpl->assign('DATA', $banners);

                if ($banners['file_name'] != 'no_image') {
                    if (!empty($banners['file_click'])) {
                        $xtpl->parse('main.loop.type_image_link');
                    } else {
                        $xtpl->parse('main.loop.type_image');
                    }
                }

                if (!empty($banners['bannerhtml'])) {
                    $xtpl->parse('main.loop.bannerhtml');
                }

                $xtpl->parse('main.loop');
            }
            $xtpl->parse('main');

            return $xtpl->text('main');
        }
    }
}

if (defined('NV_SYSTEM')) {
    $content = nv_block_global_banners($block_config);
}
