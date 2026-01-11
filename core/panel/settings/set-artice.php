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
    'name' => __('文章设置', 'ui_boxmoe_com'),
    'icon' => 'dashicons-admin-post',
    'type' => 'heading');

    $options[] = array(
        'name' => __('文章新窗口打开开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_article_new_window_switch',
        'type' => "checkbox",
        'std' => true,
        'desc' => __('若开启则文章新窗口打开', 'ui_boxmoe_com'),
        );
    $options[] = array(
        'name' => __('开启所有文章形式支持', 'ui_boxmoe_com'),
        'id' => 'boxmoe_article_support_switch',
        'type' => "checkbox",
        'std' => false,
        'desc' => __('若开启则开启所有文章形式支持', 'ui_boxmoe_com'),
        );
    $options[] = array(
        'name' => __('首页文章显示数量', 'ui_boxmoe_com'),
        'id' => 'boxmoe_home_posts_per_page',
        'type' => "text",
        'std' => 3,
        'class' => 'mini',
        'desc' => __('设置首页显示的文章数量，默认3篇', 'ui_boxmoe_com'),
        );
    $options[] = array(
        'name' => __('首页文章仅显示分类', 'ui_boxmoe_com'),
        'id' => 'boxmoe_home_article_categories',
        'type' => "text",
        'std' => '',
        'desc' => __('设置后，首页只显示指定的分类内的所有文章，请输入分类ID，多个分类用<span style="display:inline-block;background-color:#ff4d4f;color:#fff;font-size:12px;padding:2px 6px;border-radius:10px;margin:0 2px;text-align:center;vertical-align:middle;line-height:1;">，</span><span style="display:inline-block;background-color:#ff4d4f;color:#fff;font-size:12px;padding:2px 6px;border-radius:10px;margin:0 2px;text-align:center;vertical-align:middle;line-height:1;">、</span><span style="display:inline-block;background-color:#ff4d4f;color:#fff;font-size:12px;padding:2px 6px;border-radius:10px;margin:0 2px;text-align:center;vertical-align:middle;line-height:1;">,</span>三个标点符号做区分，不输入则显示所有分类', 'ui_boxmoe_com'),
        );
    $options[] = array(
        'group' => 'start',
        'group_title' => '缩略图尺寸自定义设定',
        'name' => __('缩略图尺寸自定义开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_article_thumbnail_size_switch',
        'type' => "checkbox",
        'std' => false,
        );
    $options[] = array(
        'name' => __('缩略图宽度', 'ui_boxmoe_com'),
        'id' => 'boxmoe_article_thumbnail_width',
        'type' => "text",
        'std' => '300',
        'class' => 'mini',
        );
    $options[] = array(
        'group' => 'end',
        'name' => __('缩略图高度', 'ui_boxmoe_com'),
        'id' => 'boxmoe_article_thumbnail_height',
        'type' => "text",
        'std' => '200',
        'class' => 'mini',
        );
    $options[] = array(
        'group' => 'start',
        'group_title' => '文章缩略图随机API',
        'name' => __('文章缩略图随机API', 'ui_boxmoe_com'),
        'id' => 'boxmoe_article_thumbnail_random_api',
        'type' => "checkbox",
        'std' => false,
        'desc' => __('文章缩略图随机API仅在文章没有设置缩略图时生效', 'ui_boxmoe_com'),
        );  
    $options[] = array(
        'group' => 'end',
        'name' => __('文章缩略图随机API URL', 'ui_boxmoe_com'),
        'id' => 'boxmoe_article_thumbnail_random_api_url',
        'type' => "text",
        'class' => '',
        'std' => 'https://mu.baimu.live/img/acg/',
        'desc' => __('文章缩略图随机API URL', 'ui_boxmoe_com'),
        );  
        $options[] = array(
            'type' => 'info',
            'group' => 'start',
            'group_title' => '文章页左下角看板娘设置',
        );
        $options[] = array(
            'name' => __('文章卡片看板娘图片', 'ui_boxmoe_com'),
            'id' => 'boxmoe_article_card_kanban_image',
            'type' => 'text',
            'std' => get_template_directory_uri() . '/assets/images/post-list.png',
            'desc' => __('输入文章页左下角看板娘图片链接，支持JPG、PNG、GIF格式，留空则不显示', 'ui_boxmoe_com') . '<br><span style="color: #17a2b8;">【固定尺寸162x75px，会被挤压变形】</span>',
        );
        $options[] = array(
            'name' => __('文章卡片看板娘显示范围', 'ui_boxmoe_com'),
            'id' => 'boxmoe_article_card_kanban_scope',
            'std' => 'all',
            'type' => "radio",
            'options' => array(
                'home' => __('仅在首页生效', 'ui_boxmoe_com'),
                'all' => __('全站生效', 'ui_boxmoe_com'),
            ),
            'desc' => __('设置看板娘图片的显示范围', 'ui_boxmoe_com'),
        );
        $options[] = array(
            'type' => 'info',
            'group' => 'end',
        );
    $options[] = array(
	'name' => __('文章列表分页模式', 'ui_boxmoe_com'),
	'id' => 'boxmoe_article_paging_type',
	'std' => "multi",
	'type' => "radio",
	'options' => array(
		'next' => __('上一页 和 下一页', 'ui_boxmoe_com'),
		'multi' => __('页码  1 2 3 ', 'ui_boxmoe_com'),
        // 🥰 由 白木gl.baimu.live 新增的分页模式功能
        'infinite' => __('无限加载', 'ui_boxmoe_com'),
	));
    $options[] = array(
        'name' => __('密码保护文章摘要文案', 'ui_boxmoe_com'),
        'id' => 'boxmoe_article_password_excerpt_text',
        'type' => 'text',
        'std' => '无法提供摘要。这是一篇受保护的文章。',
        'desc' => __('用于受密码保护的文章在列表中的摘要说明', 'ui_boxmoe_com'),
    );
    $options[] = array(    
        'group' => 'start',
        'group_title' => '文章打赏&点赞设置',
        'name' => __('点赞开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_like_switch',
        'type' => "checkbox",
        'std' => false,
        );        
    $options[] = array(
        'name' => __('文章打赏开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_reward_switch',
        'type' => "checkbox",
        'std' => false,
        'desc' => __('若开启则显示打赏按钮', 'ui_boxmoe_com'),
        );

    $options[] = array(
        'name' => __('打赏二维码-微信', 'ui_boxmoe_com'),
        'id' => 'boxmoe_reward_qrcode_weixin',
        'type' => "text",
        'std' => '',
        'desc' => __('打赏二维码-微信二维码地址', 'ui_boxmoe_com'),
        );
    $options[] = array(
        'group' => 'end',
        'name' => __('打赏二维码-支付宝', 'ui_boxmoe_com'),
        'id' => 'boxmoe_reward_qrcode_alipay',
        'type' => "text",
        'std' => '',
        'desc' => __('打赏二维码-支付宝二维码地址', 'ui_boxmoe_com'),
        );
    // 复制带版权功能 - - YI KAN博客提供功能代码
    $options[] = array(
        'name' => __('复制带版权开关', 'ui_boxmoe_com'),
        'id' => 'boxmoe_copy_copyright_switch',
        'type' => "checkbox",
        'std' => false,
        'desc' => __('若开启则在用户复制内容时自动添加版权信息', 'ui_boxmoe_com'),
        );
    
    