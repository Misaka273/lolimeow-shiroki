/**
 * Custom scripts needed for the colorpicker, image button selectors,
 * and navigation tabs.
 */

jQuery(document).ready(function($) {

	// Loads the color pickers
	$('.of-color').wpColorPicker();

	// Image Options
	$('.of-radio-img-img').click(function(){
		$(this).parent().parent().find('.of-radio-img-img').removeClass('of-radio-img-selected');
		$(this).addClass('of-radio-img-selected');
	});

	$('.of-radio-img-label').hide();
	$('.of-radio-img-img').show();
	$('.of-radio-img-radio').hide();

	// Loads tabbed sections if they exist
	if ( $('.nav-tab-wrapper').length > 0 ) {
		options_framework_tabs();
	}

	function options_framework_tabs() {

		var $group = $('.group'),
			$navtabs = $('.nav-tab-wrapper li a'),
			active_tab = '';

		// Hides all the .group sections to start
		$group.hide();
		$('.nav-tab-wrapper li').removeClass('active');

		// Find if a selected tab is saved in localStorage
		if ( typeof(localStorage) != 'undefined' ) {
			active_tab = localStorage.getItem('active_tab');
		}

		// If active tab is saved and exists, load it's .group
		if ( active_tab != '' && $(active_tab).length ) {
			$(active_tab).fadeIn();
			$(active_tab + '-tab').parent('li').addClass('active');
		} else {
			$('.group:first').fadeIn();
			$('.nav-tab-wrapper li:first').addClass('active');
		}

		// Bind tabs clicks
		$navtabs.click(function(e) {

			e.preventDefault();

			$('.nav-tab-wrapper li').removeClass('active');

			$(this).parent('li').addClass('active');
			$(this).blur();

			if (typeof(localStorage) != 'undefined' ) {
				localStorage.setItem('active_tab', $(this).attr('href') );
			}

			var selected = $(this).attr('href');

			$group.hide();
			$(selected).fadeIn();

		});
	}

	var $search = $('#of-search-input');
	if ($search.length) {
		function normalize(s){return (s||'').toLowerCase();}
		function fuzzyMatch(q, text){
			q = normalize(q);
			text = normalize(text);
			if (!q) return true;
			if (text.indexOf(q) !== -1) return true;
			var i=0,j=0;
			while (i<q.length && j<text.length){
				if (q.charCodeAt(i) === text.charCodeAt(j)) i++;
				j++;
			}
			return i===q.length;
		}
		function restoreTabs(){
			var $groups = $('.group');
			var href = $('.nav-tab-wrapper li.active a').attr('href') || $('.nav-tab-wrapper li a').first().attr('href');
			$groups.hide();
			if (href) $(href).fadeIn();
			$('.group .section').show();
		}
		function applySearch(){
			var q = $search.val().trim();
			var $groups = $('.group');
			if (!q){
				restoreTabs();
				return;
			}
			$groups.show();
			$('.nav-tab-wrapper li').removeClass('active');
			$('.group .section').each(function(){
				var $s = $(this);
				var name = $s.find('h4.heading').text();
				var gidText = $s.closest('.group').find('.boxmoe_tab_header').first().text();
				var hay = (name||'') + ' ' + (gidText||'');
				if (fuzzyMatch(q, hay)){
					$s.show();
				} else {
					$s.hide();
				}
			});
			$groups.each(function(){
				var $g = $(this);
				if ($g.find('.section:visible').length === 0){
					$g.hide();
				} else {
					$g.show();
				}
			});
		}
		$search.on('input', applySearch);
	}

	// Custom Board List Logic
	$(document).on('click', '.custom-board-add', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var namePrefix = $btn.data('name');
		var $wrap = $btn.closest('.custom-board-list-wrap');
		var $items = $wrap.find('.custom-board-items');
		
		// Create a media frame
		var frame = wp.media({
			title: '选择看板图片',
			button: { text: '添加到列表' },
			multiple: false
		});
		
		frame.on('select', function() {
			var attachment = frame.state().get('selection').first().toJSON();
			var timestamp = new Date().getTime();
			var itemUrlName = namePrefix + '[' + timestamp + '][url]';
			var itemNameName = namePrefix + '[' + timestamp + '][name]';
			
			var html = '<div class="custom-board-item" style="width:150px;border:1px solid #ddd;padding:10px;border-radius:5px;background:#fff;text-align:center;">';
			html += '<div class="custom-board-preview" style="margin-bottom:10px;height:150px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f5f5f5;">';
			html += '<img src="' + attachment.url + '" style="max-width:100%;max-height:100%;object-fit:contain;">';
			html += '</div>';
			html += '<input type="hidden" name="' + itemUrlName + '" value="' + attachment.url + '" class="custom-board-url">';
			html += '<div class="custom-board-input-group">';
			html += '<input type="text" name="' + itemNameName + '" value="" class="custom-board-name" placeholder=" ">';
			html += '<span class="custom-board-floating-label" data-normal="请输入名称" data-active="名称"></span>';
			html += '</div>';
			html += '<div class="actions" style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:5px;">';
			html += '<button type="button" class="button button-secondary custom-board-enable" data-url="' + attachment.url + '" style="width:100%;margin-bottom:5px;">启动</button>';
			html += '<button type="button" class="button custom-board-replace" data-update="选择图片" data-choose="选择看板图片" style="flex:1;">替换</button>';
			html += '<button type="button" class="button custom-board-delete" style="color:#b32d2e;border-color:#b32d2e;flex:1;">删除</button>';
			html += '</div></div>';
			
			$items.append(html);
		});
		
		frame.open();
	});
	
	$(document).on('click', '.custom-board-replace', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var $item = $btn.closest('.custom-board-item');
		var $input = $item.find('.custom-board-url');
		var $img = $item.find('img');
		var $enableBtn = $item.find('.custom-board-enable');
		
		var frame = wp.media({
			title: $btn.data('choose'),
			button: { text: $btn.data('update') },
			multiple: false
		});
		
		frame.on('select', function() {
			var attachment = frame.state().get('selection').first().toJSON();
			$input.val(attachment.url);
			$img.attr('src', attachment.url);
			$enableBtn.data('url', attachment.url);
			
			// If this item was enabled, update the main radio selection too
			if($enableBtn.hasClass('button-primary')) {
				updateMainRadio(attachment.url);
			}
		});
		
		frame.open();
	});
	
	$(document).on('click', '.custom-board-delete', function(e) {
		e.preventDefault();
		if(confirm('确定要删除吗？')) {
			var $item = $(this).closest('.custom-board-item');
			// If this item was enabled, maybe we should select default?
			// For now just remove it.
			$item.remove();
		}
	});
	
	// Add board by direct URL
	$(document).on('click', '.custom-board-add-by-url', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var namePrefix = $btn.data('name');
		var $wrap = $btn.closest('.custom-board-list-wrap');
		var $items = $wrap.find('.custom-board-items');
		var $urlInput = $('#custom-board-direct-url');
		var url = $urlInput.val().trim();
		
		if (!url) {
			alert('请输入图片链接');
			return;
		}
		
		// Simple URL validation
		var urlPattern = /^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)$/;
		if (!urlPattern.test(url)) {
			alert('请输入有效的图片链接');
			return;
		}
		
		var timestamp = new Date().getTime();
		var itemUrlName = namePrefix + '[' + timestamp + '][url]';
		var itemNameName = namePrefix + '[' + timestamp + '][name]';
		
		var html = '<div class="custom-board-item" style="width:150px;border:1px solid #ddd;padding:10px;border-radius:5px;background:#fff;text-align:center;">';
		html += '<div class="custom-board-preview" style="margin-bottom:10px;height:150px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f5f5f5;">';
		html += '<img src="' + url + '" style="max-width:100%;max-height:100%;object-fit:contain;">';
		html += '</div>';
		html += '<input type="hidden" name="' + itemUrlName + '" value="' + url + '" class="custom-board-url">';
		html += '<div class="custom-board-input-group">';
		html += '<input type="text" name="' + itemNameName + '" value="" class="custom-board-name" placeholder=" ">';
		html += '<span class="custom-board-floating-label" data-normal="请输入名称" data-active="名称"></span>';
		html += '</div>';
		html += '<div class="actions" style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:5px;">';
		html += '<button type="button" class="button button-secondary custom-board-enable" data-url="' + url + '" style="width:100%;margin-bottom:5px;">启动</button>';
		html += '<button type="button" class="button custom-board-replace" data-update="选择图片" data-choose="选择看板图片" style="flex:1;">替换</button>';
		html += '<button type="button" class="button custom-board-delete" style="color:#b32d2e;border-color:#b32d2e;flex:1;">删除</button>';
		html += '</div></div>';
		
		$items.append(html);
		$urlInput.val(''); // Clear input
	});
	
	// Allow Enter key to add by URL
	$(document).on('keypress', '#custom-board-direct-url', function(e) {
		if (e.which == 13) { // Enter key
			e.preventDefault();
			$('.custom-board-add-by-url').click();
		}
	});

	// Enable button logic
	$(document).on('click', '.custom-board-enable', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var url = $btn.data('url');
		
		if ($btn.hasClass('disabled')) return; // Already enabled
		
		// Reset all enable buttons in the list
		$('.custom-board-enable').removeClass('button-primary disabled').addClass('button-secondary').text('启动');
		
		// Set this one to enabled
		$btn.removeClass('button-secondary').addClass('button-primary disabled').text('已启动');
		
		// Update the main radio input
		updateMainRadio(url);
	});
	
	function updateMainRadio(url) {
		// Find radio with this value
		var $radio = $('input[name$="[boxmoe_lolijump_img]"][value="' + url + '"]');
		
		if ($radio.length > 0) {
			$radio.prop('checked', true);
		} else {
			// If radio doesn't exist (new item), create a hidden one or check if we can add it to the radio group container
			// The radio group container usually has class 'controls' or similar in OF.
			// Let's look for the radio group container by finding one of the radios
			var $anyRadio = $('input[name$="[boxmoe_lolijump_img]"]').first();
			if ($anyRadio.length > 0) {
				// Uncheck all radios
				$('input[name$="[boxmoe_lolijump_img]"]').prop('checked', false);
				
				// Create a hidden radio and append it to the form so it submits
				// We need to make sure we don't duplicate
				var radioName = $anyRadio.attr('name');
				var $existingHidden = $('input.custom-board-hidden-radio[value="' + url + '"]');
				
				if($existingHidden.length === 0) {
					var $hiddenRadio = $('<input type="radio" class="custom-board-hidden-radio" style="display:none;" checked="checked">');
					$hiddenRadio.attr('name', radioName);
					$hiddenRadio.val(url);
					$anyRadio.parent().append($hiddenRadio);
				} else {
					$existingHidden.prop('checked', true);
				}
			}
		}
	}
	
	// Listen for changes on the main radio group to update buttons
	$(document).on('change', 'input[name$="[boxmoe_lolijump_img]"]', function() {
		var val = $(this).val();
		
		// Reset all buttons
		$('.custom-board-enable').removeClass('button-primary disabled').addClass('button-secondary').text('启动');
		
		// Find button with this url
		var $btn = $('.custom-board-enable[data-url="' + val + '"]');
		if ($btn.length > 0) {
			$btn.removeClass('button-secondary').addClass('button-primary disabled').text('已启动');
		}
	});

	// 🚀 重构主题后台重置按钮功能，确保重置操作正确执行
	// 🔧 全局重置函数 - 使用独立处理文件
	window.shirokiResetAllSettings = function() {
		if (!confirm('警告：点击确定，之前所有设置修改都将丢失！')) {
			return false;
		}
		
		// 获取当前主题目录URI
		var themeUri = $('#direct-reset-button').data('theme-uri') || '/wp-content/themes/lolimeow-shiroki';
		
		// 🔧 显示加载提示窗口
		var loadingHtml = '<div id="shiroki-reset-loading" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);z-index:999999;display:flex;align-items:center;justify-content:center;">' +
			'<div style="background:#fff;padding:30px;border-radius:5px;text-align:center;max-width:400px;">' +
			'<h3>正在重置设置...</h3>' +
			'<p>请稍候，这可能需要几秒钟时间。</p>' +
			'<div style="margin:20px 0;">' +
			'<div style="width:100%;height:10px;background:#f1f1f1;border-radius:5px;overflow:hidden;">' +
			'<div id="shiroki-reset-progress" style="width:0%;height:100%;background:#2271b1;transition:width 0.3s;"></div>' +
			'</div>' +
			'</div>' +
			'<p id="shiroki-reset-status">正在初始化...</p>' +
			'</div>' +
			'</div>';
		$('body').append(loadingHtml);
		
		// 🔧 模拟进度更新
		var progress = 0;
		var progressInterval = setInterval(function() {
			progress += Math.random() * 15;
			if (progress > 90) progress = 90;
			$('#shiroki-reset-progress').css('width', progress + '%');
			
			if (progress < 30) {
				$('#shiroki-reset-status').text('正在初始化...');
			} else if (progress < 60) {
				$('#shiroki-reset-status').text('正在获取默认设置...');
			} else {
				$('#shiroki-reset-status').text('正在更新数据库...');
			}
		}, 500);
		
		// 创建表单并提交到独立的重置处理文件
		var $form = $('<form>', {
			'action': themeUri + '/direct-reset.php',
			'method': 'POST',
			'target': '_self'
		});
		
		// 添加重置标志
		$('<input>', {
			'type': 'hidden',
			'name': 'reset',
			'value': '1'
		}).appendTo($form);
		
		// 🔧 重置进度条
		setTimeout(function() {
			clearInterval(progressInterval);
			$('#shiroki-reset-progress').css('width', '100%');
			$('#shiroki-reset-status').text('正在完成...');
			
			// 提交表单
			setTimeout(function() {
				$form.appendTo('body').submit();
			}, 500);
		}, 2000);
	};
	
	// 🔧 检测重置成功参数，显示成功消息
	$(document).ready(function() {
		// 🔧 检查URL中是否包含reset参数，而不是仅检查页面
		var urlParams = new URLSearchParams(window.location.search);
		var isResetSuccess = urlParams.get('reset') === 'success';
		
		// 只有在真正重置成功后才显示消息
		if (isResetSuccess) {
			// 移除URL参数，避免刷新时重复显示
			var newUrl = window.location.pathname + window.location.search.replace(/[?&]reset=success/, '');
			window.history.replaceState({}, document.title, newUrl);
			
			// 显示成功消息
			$('#message').remove();
			var successHtml = '<div id="message" class="updated fade"><p><strong>✅ 已恢复默认选项!</strong></p></div>';
			$('.wrap h2').after(successHtml);
			
			// 5秒后自动隐藏消息
			setTimeout(function() {
				$('#message').fadeOut();
			}, 5000);
		}
	});
	
	// 🔧 重置功能 - 使用预定义默认值，避免动态获取
	window.superFastResetFunction = function() {
		if (!confirm('警告：点击确定，之前所有设置修改都将丢失！')) {
			return false;
		}
		
		// 获取当前主题目录URI
		var themeUri = $('#super-fast-reset-button').data('theme-uri') || '/wp-content/themes/lolimeow-shiroki';
		
		// 🔧 加载提示
		var loadingHtml = '<div id="shiroki-reset-loading" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);z-index:999999;display:flex;align-items:center;justify-content:center;">' +
			'<div style="background:#fff;padding:30px;border-radius:5px;text-align:center;max-width:400px;">' +
			'<h3>🚀 重置中...</h3>' +
			'<p>🌏即将重置主题设置~✍🏻</p>' +
			'<div style="margin:20px 0;">' +
			'<div style="width:100%;height:10px;background:#f1f1f1;border-radius:5px;overflow:hidden;">' +
			'<div id="shiroki-reset-progress" style="width:0%;height:100%;background:#2271b1;transition:width 0.3s;"></div>' +
			'</div>' +
			'</div>' +
			'<p id="shiroki-reset-status">正在初始化...</p>' +
			'</div>' +
			'</div>';
		$('body').append(loadingHtml);
		
		// 🔧 重置初始化进展
		var progress = 0;
		var progressInterval = setInterval(function() {
			progress += 20;
			if (progress > 90) progress = 90;
			$('#shiroki-reset-progress').css('width', progress + '%');
			
			if (progress < 30) {
				$('#shiroki-reset-status').text('正在初始化...');
			} else if (progress < 60) {
				$('#shiroki-reset-status').text('正在应用预定义默认值...');
			} else {
				$('#shiroki-reset-status').text('正在更新数据库...');
			}
		}, 200);
		
		// 创建表单并提交到重置处理文件
		var $form = $('<form>', {
			'action': themeUri + '/super-fast-reset.php',
			'method': 'POST',
			'target': '_self'
		});
		
		// 添加重置标志
		$('<input>', {
			'type': 'hidden',
			'name': 'reset',
			'value': '1'
		}).appendTo($form);
		
		// 重置进展
		setTimeout(function() {
			clearInterval(progressInterval);
			$('#shiroki-reset-progress').css('width', '100%');
			$('#shiroki-reset-status').text('正在完成...');
			
			// 提交表单
			setTimeout(function() {
				$form.appendTo('body').submit();
			}, 300);
		}, 1000);
	};

});
