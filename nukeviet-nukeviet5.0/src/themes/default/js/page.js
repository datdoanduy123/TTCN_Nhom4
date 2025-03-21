/**
 * NukeViet Content Management System
 * @version 5.x
 * @author VINADES.,JSC <contact@vinades.vn>
 * @copyright (C) 2009-2025 VINADES.,JSC. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://github.com/nukeviet The NukeViet CMS GitHub project
 */

$(window).on('load resize', function() {
    var postHtml = $('#page-bodyhtml'),
        postHtmlW, w, h;
    if (postHtml.length) {
        var postHtmlW = postHtml.innerWidth();
        $.each($('img', postHtml), function() {
            if (typeof $(this).data('width') == "undefined") {
                w = $(this).innerWidth();
                h = $(this).innerHeight();
                $(this).data('width', w);
                $(this).data('height', h);
            } else {
                w = $(this).data('width');
                h = $(this).data('height');
            }

            if (w > postHtmlW) {
                $(this).prop('width', postHtmlW);
                $(this).prop('height', h * postHtmlW / w);
            }
        })
    }
});

$(function() {
    $('body').on('click', '[data-toggle=nv_del_content]', function(e) {
        e.preventDefault();
        if (confirm(nv_is_del_confirm[0])) {
            $.post($(this).data('adminurl') + 'index.php?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=del&nocache=' + new Date().getTime(), 'id=' + $(this).data('id') + '&checkss=' + $(this).data('ss'), function(res) {
                var r_split = res.split('_');
                if (r_split[0] == 'OK') {
                    window.location.href = strHref;
                } else {
                    alert(nv_is_del_confirm[2]);
                }
            })
        }
    })
})
