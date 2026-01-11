<?php
if(!defined('ABSPATH')){echo 'Look your sister';exit;}

function boxmoe_markdown_to_html($text){
    $text = str_replace(["\r\n","\r"],"\n",$text);
    $blocks = [];
    // 代码块解析，确保与主题自带语法兼容
    $text = preg_replace_callback('/```(\w+)?\s*([\s\S]*?)```/m', function($m) use (&$blocks){
        $key = '__MD_CODE_'.count($blocks).'__';
        $language = $m[1] ? $m[1] : '';
        $code = $m[2];
        $lang_class = $language ? ' lang-'.esc_attr($language) : '';
        // 确保生成的HTML结构与主题样式兼容，包含必要的<code>标签
        $blocks[$key] = '<pre class="prettyprint linenums'.esc_attr($lang_class).'"><code'.esc_attr($lang_class).'>'.esc_html($code).'</code></pre>';
        return $key;
    }, $text);
    // 标题解析，确保与主题自带语法兼容
    $text = preg_replace('/^\s*######\s*(.+)$/m','<h6>$1</h6>',$text);
    $text = preg_replace('/^\s*#####\s*(.+)$/m','<h5>$1</h5>',$text);
    $text = preg_replace('/^\s*####\s*(.+)$/m','<h4>$1</h4>',$text);
    $text = preg_replace('/^\s*###\s*(.+)$/m','<h3>$1</h3>',$text);
    $text = preg_replace('/^\s*##\s*(.+)$/m','<h2>$1</h2>',$text);
    $text = preg_replace('/^\s*#\s*(.+)$/m','<h1>$1</h1>',$text);
    $text = preg_replace('/^\s*>\s?(.+)$/m','<blockquote><p>$1</p></blockquote>',$text);
    // 支持三种任务清单状态：未完成[- [ ]]、进行中[- [>]]、已完成[- [x]]
    $text = preg_replace_callback('/(^|\n)(?:-\s*\[( |x|>)\]\s+.+)(?:\n(?:-\s*\[( |x|>)\]\s+.+))*/', function($m){
        $items = preg_split('/\n/', trim($m[0]));
        $lis = '';
        global $post;
        $is_author = false;
        if(is_user_logged_in() && $post){
            $current_user_id = get_current_user_id();
            $is_author = ($current_user_id == $post->post_author);
            
            // 检查用户是否是被授权的编辑者
            if (!$is_author) {
                $editors = get_post_meta($post->ID, '_boxmoe_post_editors', true);
                $editors = is_array($editors) ? $editors : array();
                $is_author = in_array($current_user_id, $editors);
            }
        }
        $list_class = $is_author ? 'md-task-list-interactive' : 'md-task-list-static';
        foreach($items as $it){
            if(preg_match('/^-\s*\[( |x|>)\]\s+(.+)/', $it, $mm)){
                $status_char = $mm[1];
                // 根据状态字符设置emoji和状态值
                switch($status_char){
                    case 'x':
                        $emoji = '✅';
                        $task_status = 'completed';
                        break;
                    case '>':
                        $emoji = '🔄';
                        $task_status = 'in-progress';
                        break;
                    default:
                        $emoji = '❌';
                        $task_status = 'pending';
                        break;
                }
                $item_class = $is_author ? 'md-task-item-interactive' : 'md-task-item-static';
                $task_content = esc_attr($mm[2]);
                $lis .= '<li class="md-task-item ' . $item_class . '" data-task-status="' . $task_status . '" data-task-content="' . $task_content . '" data-is-author="' . ($is_author ? 'true' : 'false') . '">';
                $lis .= '<span class="md-task-emoji">' . $emoji . '</span>';
                $lis .= '<span class="md-task-text">' . $mm[2] . '</span>';
                $lis .= '</li>';
            }
        }
        return '<ul class="md-task-list ' . $list_class . '">' . $lis . '</ul>';
    }, $text);
    

    $text = preg_replace_callback('/(^|\n)(?:-\s+.+(?:\n|$))+/', function($m){
        $items = preg_split('/\n/', trim($m[0]));
        $lis = '';
        foreach($items as $it){
            if(preg_match('/^-\s+(.+)/',$it,$mm)){$lis .= '<li>'.$mm[1].'</li>';}
        }
        return '<ul>'.$lis.'</ul>';
    }, $text);
    $text = preg_replace_callback('/(^|\n)(?:\d+\.\s+.+(?:\n|$))+/', function($m){
        $items = preg_split('/\n/', trim($m[0]));
        $lis = '';
        foreach($items as $it){
            if(preg_match('/^\d+\.\s+(.+)/',$it,$mm)){$lis .= '<li>'.$mm[1].'</li>';}
        }
        return '<ol>'.$lis.'</ol>';
    }, $text);
    // 解析卡片内容，将其替换为临时占位符
    $card_placeholders = [];
    $text = preg_replace_callback('/名称：\s*(.+?)\s*\n头像链接：\s*(.+?)\s*\n描述：\s*(.+?)\s*\n链接：\s*(.+?)\s*\n勋章：\s*(.+?)\s*(\n|$)/s', function($m) use (&$card_placeholders){
        $name = $m[1];
        $avatar = $m[2];
        $desc = $m[3];
        $link = $m[4];
        $badge = $m[5];
        
        $card_html = '<a href="'.$link.'" target="_blank" class="md-card-link-wrap">
            <div class="md-card">
                <div class="md-card-avatar">
                    <img src="'.$avatar.'" alt="'.$name.'" />
                    <div class="md-card-badge">'.$badge.'</div>
                </div>
                <div class="md-card-content">
                    <h3 class="md-card-title">'.$name.'</h3>
                    <p class="md-card-desc">'.$desc.'</p>
                </div>
            </div>
        </a>';
        
        $placeholder = '__MD_CARD_'.count($card_placeholders).'__';
        $card_placeholders[$placeholder] = $card_html;
        return $placeholder;
    }, $text);
    
    // 处理其他Markdown元素，包括链接转换
    // 🔤 文本格式：粗体
    $text = preg_replace('/\*\*(.+?)\*\*/s','<strong>$1</strong>',$text);
    // 🔤 文本格式：斜体
    $text = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s','<em>$1</em>',$text);
    // 📋 文本格式：行内代码
    $text = preg_replace('/`([^`]+)`/s','<code>$1</code>',$text);
    // 📷 图片（支持自定义尺寸语法：![alt](url =widthxheight) 或 ![alt](url =widthxheightxalignment)）
    $text = preg_replace('/!\[([^\]]*)\]\(([^\)]+)\s*=\s*(\d+)x(\d+)(x(\w+))?\)/i','<img src="$2" alt="$1" width="$3" height="$4" $5$6 />',$text);
    // 处理基础图片语法（无尺寸）
    $text = preg_replace('/!\[([^\]]*)\]\(([^\)]+)\)/','<img src="$2" alt="$1" />',$text);
    // 🔗 链接
    $text = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/','<a href="$2"'.(is_admin()?'':' target="_blank"').'>$1</a>',$text);
    // 📊 表格支持
    $text = preg_replace_callback('/(^|\n)(?:[|].+[|](?:\n|$))+(?:[|].+[|](?:\n|$))+/', function($m){
        $lines = preg_split('/\n/', trim($m[0]));
        $thead = true;
        $html = '<table class="md-table"><thead>';
        foreach($lines as $line){
            if(preg_match('/^[|](.*)[|]$/', $line, $mm)){
                $cells = array_map('trim', explode('|', $mm[1]));
                if($thead){
                    $html .= '<tr>';
                    foreach($cells as $cell){
                        $html .= '<th>'.$cell.'</th>';
                    }
                    $html .= '</tr></thead><tbody>';
                    $thead = false;
                } else {
                    $html .= '<tr>';
                    foreach($cells as $cell){
                        $html .= '<td>'.$cell.'</td>';
                    }
                    $html .= '</tr>';
                }
            }
        }
        $html .= '</tbody></table>';
        return $html;
    }, $text);
    // 📏 水平分割线
    $text = preg_replace('/^---$/m','<hr class="md-hr" />',$text);
    $text = preg_replace('/^___$/m','<hr class="md-hr" />',$text);
    $text = preg_replace('/^\*\*\*$/m','<hr class="md-hr" />',$text);
    
    // 📦 折叠语法支持
    // 支持:::details 标题
    // 内容
    // ::: 格式
    $text = preg_replace_callback('/:::details\s+(.+)\s*([\s\S]*?):::/m', function($m){
        $title = $m[1];
        $content = trim($m[2]);
        // 对内容进行递归处理，确保内部Markdown语法也能被正确解析
        $content_html = boxmoe_markdown_to_html($content);
        return '<details class="shiroki-collapse"><summary class="shiroki-collapse-title">' . $title . '</summary><div class="shiroki-collapse-content">' . $content_html . '</div></details>';
    }, $text);
    
    // 支持<details>和<summary>HTML标签
    $text = preg_replace_callback('/<details>\s*<summary>(.+?)<\/summary>\s*([\s\S]*?)<\/details>/i', function($m){
        $title = $m[1];
        $content = trim($m[2]);
        // 对内容进行递归处理，确保内部Markdown语法也能被正确解析
        $content_html = boxmoe_markdown_to_html($content);
        return '<details class="shiroki-collapse"><summary class="shiroki-collapse-title">' . $title . '</summary><div class="shiroki-collapse-content">' . $content_html . '</div></details>';
    }, $text);
    
    // 先处理段落，添加<p>标签
    $parts = preg_split('/\n\n+/', trim($text));
    foreach($parts as &$p){
        // 🎯 检查是否是代码块占位符，如果是则不添加<p>标签
        if(!preg_match('/^\s*<(h\d|ul|ol|pre|blockquote|img|a|table|audio|video)/i',$p) && 
           !preg_match('/^__MD_CODE_\d+__$/', $p)){
            $p = '<p>'.$p.'</p>';
        }
    }
    $text = implode("\n", $parts);
    
    // 将卡片占位符替换回完整的HTML
    foreach($card_placeholders as $placeholder => $card_html){
        $text = str_replace($placeholder, $card_html, $text);
    }
    
    // 修复：将包裹在<p>标签中的卡片HTML提取出来，移除<p>标签
    $text = preg_replace('/<p>\s*(<a href=".+?" target="_blank" class="md-card-link-wrap">.+?<\/a>)\s*<\/p>/s', '$1', $text);
    
    // 处理代码块占位符
    $html = $text;
    foreach($blocks as $k=>$v){
        $html = str_replace($k,$v,$html);
    }
    
    // 🎯 修复：移除包裹在代码块HTML外的<p>标签
    $html = preg_replace('/<p>\s*(<pre class="prettyprint linenums.*?<\/pre>)\s*<\/p>/s', '$1', $html);
    
    return $html;
}

function boxmoe_md_the_content($content){
    // 只在前端显示时转换为HTML，后台编辑器中保持原始Markdown语法
    if(get_boxmoe('boxmoe_md_editor_switch') && !is_admin()){
        return boxmoe_markdown_to_html($content);
    }
    return $content;
}
// 调整执行Markdown转换优先级
add_filter('the_content', 'boxmoe_md_the_content', 2);

// 修复后台编辑器中的HTML实体问题
function boxmoe_fix_md_editor_content($content){
    if(get_boxmoe('boxmoe_md_editor_switch') && is_admin()){
        // 将HTML实体转换为原始字符，确保后台编辑器中显示正确的Markdown语法
        $content = str_replace('&gt;', '>', $content);
        $content = str_replace('&lt;', '<', $content);
        $content = str_replace('&quot;', '"', $content);
        $content = str_replace('&#039;', "'", $content);
    }
    return $content;
}
add_filter('content_edit_pre', 'boxmoe_fix_md_editor_content');
add_filter('the_editor_content', 'boxmoe_fix_md_editor_content');

if(get_boxmoe('boxmoe_md_editor_switch')){
    add_filter('use_block_editor_for_post', '__return_false');
    add_filter('user_can_richedit', '__return_false');
    add_action('admin_enqueue_scripts', function($hook){
        if($hook==='post.php' || $hook==='post-new.php'){
            wp_enqueue_style('boxmoe-md-editor', get_template_directory_uri().'/assets/css/markdown-editor.css', [], THEME_VERSION);
            wp_enqueue_script('boxmoe-md-editor', get_template_directory_uri().'/assets/js/markdown-editor.js', ['jquery'], THEME_VERSION, true);
            wp_localize_script('boxmoe-md-editor','BoxmoeMdEditor',[
                'enabled'=>true,
                'ajaxUrl'=>admin_url('admin-ajax.php'),
                'nonce'=>wp_create_nonce('boxmoe_md')
            ]);
        }
    });
    add_action('wp_ajax_boxmoe_md_preview', function(){
        if(!current_user_can('edit_posts')){wp_send_json_error(['message'=>'forbidden']);}
        if(!isset($_POST['nonce'])||!wp_verify_nonce($_POST['nonce'],'boxmoe_md')){wp_send_json_error(['message'=>'bad_nonce']);}
        $md = isset($_POST['markdown']) ? (string) wp_unslash($_POST['markdown']) : '';
        $html = boxmoe_markdown_to_html($md);
        $html = do_shortcode($html);
        wp_send_json_success(['html'=>$html]);
    });
}

// 📝 更新任务状态的AJAX处理函数
add_action('wp_ajax_update_task_status', 'boxmoe_update_task_status');
add_action('wp_ajax_nopriv_update_task_status', 'boxmoe_update_task_status_nopriv');

// 前端任务清单AJAX初始化
add_action('wp_enqueue_scripts', function(){
    // 只在单页文章和页面中加载任务清单脚本
    if(is_singular()){
        // 使用不同的对象名，避免覆盖ajax_object
        wp_localize_script('boxmoe-script', 'task_ajax_object', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('boxmoe_task_status')
        ));
    }
});

function boxmoe_update_task_status(){
    // 检查nonce
    if(!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'boxmoe_task_status')){
        wp_send_json_error(['message'=>'无效的nonce']);
    }
    
    if(!isset($_POST['post_id']) || !isset($_POST['task_content']) || !isset($_POST['current_status'])){
        wp_send_json_error(['message'=>'缺少必要参数']);
    }
    
    $post_id = intval($_POST['post_id']);
    $task_content = wp_unslash($_POST['task_content']);
    $current_status = $_POST['current_status'];
    
    // 获取当前用户ID
    $current_user_id = get_current_user_id();
    
    // 验证用户权限
    $post = get_post($post_id);
    if(!$post){
        wp_send_json_error(['message'=>'文章不存在']);
    }
    
    // 初始化编辑者数组，避免作用域问题
    $editors = array();
    
    // 检查用户是否有修改权限
    $is_allowed = false;
    
    // 检查WordPress内置权限（管理员、编辑、作者等）
    if(current_user_can('edit_post', $post_id)){
        $is_allowed = true;
    } else {
        // 检查用户是否是文章作者
        if($current_user_id === $post->post_author){
            $is_allowed = true;
        } else {
            // 检查用户是否是被授权的编辑者
            $editors = get_post_meta($post_id, '_boxmoe_post_editors', true);
            
            // 添加详细调试日志
            error_log('原始编辑者数据: ' . print_r($editors, true));
            
            $editors = is_array($editors) ? $editors : array();
            
            // 确保$current_user_id是整数
            $current_user_id = intval($current_user_id);
            
            // 将编辑者列表转换为整数数组
            $editors = array_map('intval', $editors);
            
            // 添加调试日志
            error_log('处理后的编辑者列表: ' . implode(', ', $editors));
            error_log('当前用户ID (整数): ' . $current_user_id);
            error_log('in_array返回值: ' . (in_array($current_user_id, $editors) ? 'true' : 'false'));
            
            $is_allowed = in_array($current_user_id, $editors);
        }
    }
    
    // 添加调试日志
    error_log('任务状态更新权限检查:');
    error_log('当前用户ID: ' . $current_user_id);
    error_log('文章作者ID: ' . $post->post_author);
    error_log('文章编辑者列表: ' . implode(', ', $editors));
    error_log('是否有权限: ' . ($is_allowed ? '是' : '否'));
    error_log('内置权限检查: ' . (current_user_can('edit_post', $post_id) ? '是' : '否'));
    
    // 只有有权限的用户才能修改任务状态
    if(!$is_allowed){
        wp_send_json_error(['message'=>'没有权限修改此任务']);
    }
    
    // 获取当前文章内容
    $post = get_post($post_id);
    if(!$post){
        wp_send_json_error(['message'=>'文章不存在']);
    }
    
    $content = $post->post_content;
    
    // 根据当前状态计算下一个状态
    // 状态循环：in-progress → pending → completed → in-progress
    // 对应语法：- [>] → - [ ] → - [x] → - [>]
    switch($current_status){
        case 'in-progress':
            $next_status = 'pending';
            $status_char = ' ';
            break;
        case 'pending':
            $next_status = 'completed';
            $status_char = 'x';
            break;
        case 'completed':
            $next_status = 'in-progress';
            $status_char = '>';
            break;
        default:
            $next_status = 'in-progress';
            $status_char = '>';
            break;
    }
    
    // 记录调试信息
    error_log('更新任务状态: post_id='.$post_id.', task_content='.$task_content.', current_status='.$current_status.', next_status='.$next_status.', status_char='.$status_char);
    error_log('原始文章内容前100字符: '.substr($content, 0, 100));
    
    // 记录完整的文章内容用于调试
    error_log('完整文章内容: '.str_replace('\n', '\\n', $content));
    
    // 当所有任务内容完全相同时，我们需要使用更智能的匹配策略
    // 首先将文章内容按行分割（使用双引号确保换行符被正确解释）
    $lines = explode("\n", $content);
    $updated = false;
    
    // 遍历每一行，查找需要更新的任务行
    for($i = 0; $i < count($lines); $i++){
        $line = $lines[$i];
        
        // 检查是否是任务行
        if(preg_match('/^-\s*\[( |x|>|&gt;)\]\s+(.*)$/', $line, $matches)){
            $current_status_char = $matches[1];
            $line_content = $matches[2];
            
            // 精确匹配任务内容（去除HTML实体影响）
            $clean_line_content = trim(str_replace('&gt;', '>', str_replace('&lt;', '<', $line_content)));
            $clean_task_content = trim($task_content);
            
            // 记录当前行的匹配信息
            error_log('检查行 ' . ($i+1) . ': "' . $line . '"');
            error_log('  当前状态字符: "' . $current_status_char . '"');
            error_log('  行内容(clean): "' . $clean_line_content . '"');
            error_log('  目标内容(clean): "' . $clean_task_content . '"');
            error_log('  内容匹配: ' . ($clean_line_content === $clean_task_content ? '是' : '否'));
            error_log('  当前状态: "' . $current_status . '"');
            
            // 匹配条件🔽
            // 任务内容完全匹配
            // 当前状态字符与请求的当前状态匹配
            $status_matched = false;
            if($current_status == 'pending' && $current_status_char == ' ') {
                $status_matched = true;
            } elseif($current_status == 'completed' && $current_status_char == 'x') {
                $status_matched = true;
            } elseif($current_status == 'in-progress' && ($current_status_char == '>' || $current_status_char == '&gt;')) {
                $status_matched = true;
            }
            
            error_log('  状态匹配: ' . ($status_matched ? '是' : '否'));
            
            if($clean_line_content === $clean_task_content && $status_matched){
                // 找到匹配的任务行，更新状态
                error_log('  找到匹配行，更新状态');
                
                // 替换该行的状态字符
                $new_line = preg_replace('/^(-\s*)\[( |x|>|&gt;)\]/', '$1['.$status_char.']', $line);
                $lines[$i] = $new_line;
                $updated = true;
                break; // 只更新第一个匹配的行，避免更新所有相同内容的行
            }
        }
    }
    
    // 如果找到并更新了任务行，重新组合文章内容（使用双引号确保换行符被正确解释）
    if($updated){
        $updated_content = implode("\n", $lines);
        error_log('找到并更新了匹配的任务行');
    } else {
        // 如果没有找到匹配的任务行，保持原内容不变
        $updated_content = $content;
        error_log('没有找到匹配的任务行');
    }
    
    // 确保所有HTML实体都被转换为原始字符
    $updated_content = str_replace('&gt;', '>', $updated_content);
    $updated_content = str_replace('&lt;', '<', $updated_content);
    
    // 添加调试日志，查看最终更新后的内容
    error_log('最终更新后的内容片段: ' . substr($updated_content, 0, 200));
    $updated_content = str_replace('&quot;', '"', $updated_content);
    $updated_content = str_replace('&#039;', "'", $updated_content);
    
    // 记录替换结果
    error_log('替换结果: '.($updated_content === $content ? '未找到匹配的任务' : '成功更新任务状态'));
    
    // 更新文章
    error_log('调用wp_update_post前: post_id='.$post_id.', updated_content前100字符: '.substr($updated_content, 0, 100));
    
    $result = wp_update_post([
        'ID' => $post_id,
        'post_content' => $updated_content
    ]);
    
    error_log('wp_update_post结果: '.($result === 0 ? '没有更新' : ($result === false ? '更新失败' : '更新成功，post_id='.$result)));
    
    if(is_wp_error($result)){
        error_log('wp_update_post错误: '. $result->get_error_message());
        wp_send_json_error(['message'=>'更新任务状态失败: '. $result->get_error_message()]);
    }
    
    if($result === 0){
        // 没有更新，可能是因为内容没有变化
        error_log('wp_update_post没有更新，可能是因为内容没有变化');
        wp_send_json_success(['message'=>'任务状态没有变化']);
    }
    
    if($result === false){
        // 更新失败
        error_log('wp_update_post更新失败，原因未知');
        wp_send_json_error(['message'=>'更新任务状态失败']);
    }
    
    // 更新成功，返回新状态
    error_log('任务状态更新成功，返回的post_id='.$result);
    wp_send_json_success([
        'message'=>'更新任务状态成功',
        'new_status' => $next_status
    ]);
}

function boxmoe_update_task_status_nopriv(){
    wp_send_json_error(['message'=>'请先登录']);
}