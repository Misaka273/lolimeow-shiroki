<?php
/**
 * @link https://gl.baimu.live
 * @package 白木🥰
 * @description 音乐播放器设置面板
 * @author 初叶🍂 <www.chuyel.top>
 */

//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){exit;}

$options[] = array(
    'name' => '全站音乐设置',
    'id' => 'music_settings',
    'icon' => 'dashicons-playlist-audio',
    'type' => 'heading');

// 🎵 基础设置分组
$options[] = array(
    'group' => 'start',
    'group_title' => '基础设置',
    'name' => '启用全站底部音乐播放器',
    'id' => 'music_on',
    'type' => 'checkbox',
    'std' => false,
    'desc' => '开启后将在网站底部显示音乐播放器');

$options[] = array(
    'group' => 'end');

// 🎵 音乐源设置分组
$options[] = array(
    'group' => 'start',
    'group_title' => '音乐源设置',
    'name' => '选择音乐运营商',
    'id' => 'music_server',
    'std' => 'netease',
    'type' => 'radio',
    'options' => array(
        'netease' => '网易云音乐',
        'tencent' => 'QQ音乐',
        'kugou' => '酷狗音乐',
        'xiami' => '虾米音乐',
        'baidu' => '百度音乐'
    ));

$options[] = array(
    'name' => '歌单ID',
    'id' => 'music_id',
    'std' => '6814606449',
    'type' => 'text',
    'desc' => '输入音乐平台的歌单ID，建议不要使用超过100首歌曲的歌单');

$options[] = array(
    'name' => '自定义API接口',
    'id' => 'music_api',
    'std' => '',
    'type' => 'text',
    'desc' => '留空使用默认API，填写自定义API地址将强制使用该接口作为音乐源，优先级最高');

$options[] = array(
    'name' => 'API源选择',
    'id' => 'music_api_source',
    'std' => 'api_injahow',
    'type' => 'select',
    'options' => array(
        'api_injahow' => 'api.injahow.cn (推荐)',
        'api_imeto' => 'api.i-meto.com (默认)',
        'api_ihuan' => 'meting-api.ihuan.me',
        'api_github' => 'api-meting.github.io',
        'api_chuyel' => 'musicapi.chuyel.top (初叶🍂仅QQ音乐)'
    ),
    'desc' => '选择音乐API源，如果自定义API不为空则优先使用自定义API');

$options[] = array(
    'group' => 'end');

// 🎵 播放设置分组
$options[] = array(
    'group' => 'start',
    'group_title' => '播放设置',
    'name' => '歌单列表播放顺序',
    'id' => 'music_order',
    'std' => 'list',
    'type' => 'radio',
    'options' => array(
        'list' => '顺序播放',
        'random' => '随机播放'
    ));

$options[] = array(
    'group' => 'end');



