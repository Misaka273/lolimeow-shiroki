<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */

//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

$options[] = array(
    'name' => __('关于主题', 'ui_boxmoe_com'),
    'icon' => 'dashicons-info',
    'type' => 'heading');

$options[] = array(
    'name' => __('开源协议', 'ui_boxmoe_com'), 
    'id' => 'banquan',
    'desc' => __('
     <p>1.主题依托于开源协议 GPL V3.0，如果不接受本协议，请立即删除</p>
     <p>2.请遵循开源协议，保留主题底部版权信息，如果不接受本协议，请立即删除；</p>		
    ', 'ui_boxmoe_com'),
    'type' => 'info');
$options[] = array(
    'name' => __('使用协议/注意事项', 'ui_boxmoe_com'), 
    'id' => 'shiyong',
    'desc' => __('
     <p>1.主题仅供博客爱好者合法建站交流！禁止使用于违法用途！如如主题使用者不能遵守此规定，请立即删除；</p>
     <p>2.严禁利用本主题严重侵犯他人隐私权，如主题使用者不能遵守此规定，请立即删除；</p>
     <p>3.使用主题请遵守网站服务器当地相关法律和站长当地相关法律，如不能遵守请立即删除；</p>
     <p>4.主题不支持任何作为非法违规用途站点！如不能遵守请立即删除；</p>
     <p>5.主题开源无任何加密文件，对于因用户使用本主题而造成自身或他人隐私泄露，等任何不良后果，均由用户自行承担，主题作者不负任何责任；</p>
     <p>6.本主题共享下载，如果用户自行下载使用，即表明用户自愿并接受本协议所有条款。 如果用户不接受本协议，请立即删除；</p>
    ', 'ui_boxmoe_com'),
    'type' => 'info');
    $options[] = array(
        'name' => __('🕊️当前二创主题介绍「纸鸢版⌋🚀', 'ui_boxmoe_com'), 
        'id' => 'shiyong',
        'desc' => __('
         <p>🎉 对后台UI界面进行美化</p>
         <p>⚒️ 修复部分功能的BUG问题</p>
         <p>🥰 新增若干功能</p>
         <p>🙉 基于Lolimeow原版主题开发，保留原版所有功能，会根据白木自身需求进行更新，欢迎反馈建议</p>
         <p>👀 有些BUG和正在开发中的功能，会被白木记录到 <a href="https://gl.baimu.live/864" target="_blank" rel="external nofollow" class="url">「萌盒子Lolimeow」主题二创开发笔记，及待办事项</a> 中</p>
         <p>😏 如果真的有要提出的建议，可以加入主题群，向 <a href="http://qm.qq.com/cgi-bin/qm/qr?_wv=1027&k=YLb_jw14jGMh1q8cMwga9UZcWp6JDPsS&authKey=x8YpdYVOU%2BIyiJ8uSJ2gT9UJ%2B%2BByQjnaHTTaTjMAu9YIERV20NnM%2F7tfBB%2B39peo&noverify=0&group_code=24847519" target="_blank" rel="external nofollow" class="url">@白木</a> 提出~</p>
        ', 'ui_boxmoe_com'),
        'type' => 'info');
$options[] = array(
    'name' => __('主题信息', 'ui_boxmoe_com'), 
    'id' => 'banquan',
    'desc' => __('
     <p>当前二创版本：'.$THEME_VERSION.'</p>
     <p>查看二创主题：<a href="https://gl.baimu.live/864" target="_blank" rel="external nofollow" class="url">笔记</a></p>
     <p>原版最新版本：<span id="vbox"></span></p>
     <p>查看原版主题：<a href="https://www.boxmoe.com/468.html" target="_blank" rel="external nofollow" class="url">更新日志</a></p>		
     <p>主题QQ群：<a href="http://qm.qq.com/cgi-bin/qm/qr?_wv=1027&k=YLb_jw14jGMh1q8cMwga9UZcWp6JDPsS&authKey=x8YpdYVOU%2BIyiJ8uSJ2gT9UJ%2B%2BByQjnaHTTaTjMAu9YIERV20NnM%2F7tfBB%2B39peo&noverify=0&group_code=24847519" target="_blank" rel="external nofollow" class="url">24847519</a></p>
     <p>TG群组：<a href="https://t.me/hezimeng" target="_blank" rel="external nofollow" class="url">https://t.me/hezimeng</a></p>
    ', 'ui_boxmoe_com'),
    'type' => 'info');