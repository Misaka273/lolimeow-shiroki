<?php
/**
 * @link https://gl.baimu.live/
 * @package 白木-灵阈研都-纸鸢社
 * @description GEO / AI 搜索优化：结构化数据（Organization、WebSite、Article）、面包屑、/llms.txt
 */

//安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){
    echo'Look your sister';
    exit;
}

$options[] = array(
    'name' => __('GEO / AI 搜索优化', 'ui_boxmoe_com'),
    'icon' => 'dashicons-search',
    'type' => 'heading');

    //-----------------------------------------------------------
    $options[] = array(
        'group' => 'start',
        'group_title' => '结构化数据（Schema.org）',
        'name' => __('输出结构化数据', 'ui_boxmoe_com'),
        'id' => 'boxmoe_geo_structured_switch',
        'desc' => __('（默认开启）在页面头部输出 JSON-LD：首页是 Organization + WebSite，文章页是 Article，页面是 WebPage，帮助搜索引擎和其他系统理解站点身份与内容，不保证收录或引用。', 'ui_boxmoe_com'),
        'type' => 'checkbox',
        'std' => true,
        );

    $options[] = array(
        'name' => __('输出面包屑结构化数据', 'ui_boxmoe_com'),
        'id' => 'boxmoe_geo_breadcrumb_switch',
        'desc' => __('（默认开启）文章与页面输出 BreadcrumbList，帮助 AI 理解页面层级。', 'ui_boxmoe_com'),
        'type' => 'checkbox',
        'std' => true,
        );

    $options[] = array(
        'name' => __('输出 FAQ 结构化数据', 'ui_boxmoe_com'),
        'id' => 'boxmoe_geo_faq_switch',
        'desc' => __('（默认关闭）把可公开正文中「三级标题 + 紧随段落」识别为问答并输出 FAQPage，仅用于表达真实问答内容。正文没有问答内容时请保持关闭；此标记不保证搜索结果中的特殊展示。', 'ui_boxmoe_com'),
        'type' => 'checkbox',
        'std' => false,
        );

    $options[] = array(
        'group' => 'end',
        'name' => __('输出 geo:* 扩展标签', 'ui_boxmoe_com'),
        'id' => 'boxmoe_geo_meta_switch',
        'desc' => __('（默认关闭）输出 geo:title、geo:description 等自定义 meta 标签。这些不是行业标准，可选项。', 'ui_boxmoe_com'),
        'type' => 'checkbox',
        'std' => false,
        );

    //-----------------------------------------------------------
    $options[] = array(
        'group' => 'start',
        'group_title' => '站点身份（Organization）',
        'name' => __('站点类型', 'ui_boxmoe_com'),
        'id' => 'boxmoe_org_type',
        'desc' => __('个人站点可选 Person，企业选 Organization / Corporation。默认 Organization。', 'ui_boxmoe_com'),
        'type' => 'select',
        'std' => 'Organization',
        'options' => array(
            'Organization' => __('站点（默认）', 'ui_boxmoe_com'),
            'Corporation' => __('公司 / 企业', 'ui_boxmoe_com'),
            'Person' => __('个人', 'ui_boxmoe_com'),
            'NGO' => __('非营利站点', 'ui_boxmoe_com'),
            'EducationalOrganization' => __('教育机构', 'ui_boxmoe_com'),
            'GovernmentOrganization' => __('政府机构', 'ui_boxmoe_com'),
            'LocalBusiness' => __('本地商家', 'ui_boxmoe_com'),
        ));

    $options[] = array(
        'name' => __('站点名称', 'ui_boxmoe_com'),
        'id' => 'boxmoe_org_name',
        'desc' => __('留空则使用站点标题。建议填真实全称，例如「灵阈研都」。', 'ui_boxmoe_com'),
        'type' => 'text',
        'std' => '',
        );

    $options[] = array(
        'name' => __('别名', 'ui_boxmoe_com'),
        'id' => 'boxmoe_org_alt_names',
        'desc' => __('多个别名用英文逗号分隔，也支持换行，例如：Neuro Eden,风铃白木', 'ui_boxmoe_com'),
        'type' => 'text',
        'std' => '',
        );

    $options[] = array(
        'name' => __('LOGO 地址', 'ui_boxmoe_com'),
        'id' => 'boxmoe_org_logo',
        'desc' => __('留空则自动使用主题【LOGO设置】里的图片。建议正方形、不小于 112×112。', 'ui_boxmoe_com'),
        'type' => 'text',
        'std' => '',
        );

    $options[] = array(
        'name' => __('站点简介', 'ui_boxmoe_com'),
        'id' => 'boxmoe_org_description',
        'desc' => __('留空则使用站点副标题。建议简洁说明「这是什么站、覆盖什么主题、给谁看」，帮助系统理解站点定位，不保证 AI 引用。', 'ui_boxmoe_com'),
        'type' => 'textarea',
        'settings' => array('rows' => 3),
        'std' => '',
        );

    $options[] = array(
        'name' => __('同一主体主页', 'ui_boxmoe_com'),
        'id' => 'boxmoe_org_sameas',
        'desc' => __('一行一个完整网址，例如 gitee、GitHub、微博、公众号、百科页。用于告诉 AI 这些账号都属于同一个主体。', 'ui_boxmoe_com'),
        'type' => 'textarea',
        'settings' => array('rows' => 4),
        'std' => '',
        );

    $options[] = array(
        'name' => __('擅长领域', 'ui_boxmoe_com'),
        'id' => 'boxmoe_org_knows_about',
        'desc' => __('一行一个，例如：LoliMeow 主题、WordPress 建站、游戏日报。用于补充站点涉及的主题，应与实际公开内容一致。', 'ui_boxmoe_com'),
        'type' => 'textarea',
        'settings' => array('rows' => 4),
        'std' => '',
        );

    $options[] = array(
        'group' => 'end',
        'name' => __('联系邮箱', 'ui_boxmoe_com'),
        'id' => 'boxmoe_org_email',
        'desc' => __('可选。填入后会在结构化数据里公开，仅建议填公开的业务邮箱。', 'ui_boxmoe_com'),
        'type' => 'text',
        'std' => '',
        );

    //-----------------------------------------------------------
    $options[] = array(
        'group' => 'start',
        'group_title' => 'llms.txt',
        'name' => __('开启 /llms.txt', 'ui_boxmoe_com'),
        'id' => 'boxmoe_llms_switch',
        'desc' => __('（默认开启）在 站点地址/llms.txt 输出可公开内容的站点导览，随文章更新生成；外部缓存可能延迟最多一小时。关闭后该地址返回 404。此文件为可选辅助，不保证收录或引用。', 'ui_boxmoe_com'),
        'type' => 'checkbox',
        'std' => true,
        );

    $options[] = array(
        'name' => __('导览简介', 'ui_boxmoe_com'),
        'id' => 'boxmoe_llms_intro',
        'desc' => __('留空则使用站点副标题。建议一句话说清站点主题与受众，会作为 llms.txt 开头的引用块（> 开头那一行）。', 'ui_boxmoe_com'),
        'type' => 'textarea',
        'settings' => array('rows' => 3),
        'std' => '',
        );

    $options[] = array(
        'name' => __('栏目数量', 'ui_boxmoe_com'),
        'id' => 'boxmoe_llms_cats',
        'desc' => __('按文章数取前 N 个分类，默认 20。', 'ui_boxmoe_com'),
        'type' => 'text',
        'class' => 'mini',
        'std' => '20',
        );

    $options[] = array(
        'name' => __('文章数量', 'ui_boxmoe_com'),
        'id' => 'boxmoe_llms_posts',
        'desc' => __('列出最近更新的 N 篇文章，默认 15。', 'ui_boxmoe_com'),
        'type' => 'text',
        'class' => 'mini',
        'std' => '15',
        );

    $options[] = array(
        'name' => __('页面数量', 'ui_boxmoe_com'),
        'id' => 'boxmoe_llms_pages',
        'desc' => __('列出 N 个独立页面，默认 8。', 'ui_boxmoe_com'),
        'type' => 'text',
        'class' => 'mini',
        'std' => '8',
        );

    $options[] = array(
        'name' => __('声明允许引用', 'ui_boxmoe_com'),
        'id' => 'boxmoe_llms_allow',
        'desc' => __('（默认开启）在文件末尾写明允许 AI 抓取引用并给出原文链接。', 'ui_boxmoe_com'),
        'type' => 'checkbox',
        'std' => true,
        );

    $options[] = array(
        'group' => 'end',
        'name' => __('补充说明', 'ui_boxmoe_com'),
        'id' => 'boxmoe_llms_extra',
        'desc' => __('一行一条，会追加到 llms.txt 末尾。可写更新频率、联系方式、付费说明等。', 'ui_boxmoe_com'),
        'type' => 'textarea',
        'settings' => array('rows' => 4),
        'std' => '',
        );
