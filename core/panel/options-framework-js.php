<?php

add_action( 'optionsframework_custom_scripts', 'optionsframework_custom_scripts' );
function optionsframework_custom_scripts() { ?>
<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('#example_showhidden').click(function() {
  		jQuery('#section-example_text_hidden').fadeToggle(400);
	});
	if (jQuery('#example_showhidden:checked').val() !== undefined) {
		jQuery('#section-example_text_hidden').show();
	}
	jQuery('#lantern').click(function() {
  		jQuery('#section-lanternfont1').fadeToggle(400);
	});
	if (jQuery('#lantern:checked').val() !== undefined) {
		jQuery('#section-lanternfont1').show();
	}
	jQuery('#lantern').click(function() {
  		jQuery('#section-lanternfont2').fadeToggle(400);
	});
	if (jQuery('#lantern:checked').val() !== undefined) {
		jQuery('#section-lanternfont2').show();
	}
	jQuery('#lantern').click(function() {
  		jQuery('#section-lanternfont3').fadeToggle(400);
	});
	if (jQuery('#lantern:checked').val() !== undefined) {
		jQuery('#section-lanternfont3').show();
	}
	jQuery('#lantern').click(function() {
  		jQuery('#section-lanternfont4').fadeToggle(400);
	});
	if (jQuery('#lantern:checked').val() !== undefined) {
		jQuery('#section-lanternfont4').show();
	}
	jQuery('#gravatar_onoff').click(function() {
  		jQuery('#section-gravatar_url').fadeToggle(400);
	});

	if (jQuery('#gravatar_onoff:checked').val() !== undefined) {
		jQuery('#section-gravatar_url').show();
	}	
	jQuery('#lolijump').click(function() {
  		jQuery('#section-lolijumpsister').fadeToggle(400);
	});

	if (jQuery('#lolijump:checked').val() !== undefined) {
		jQuery('#section-lolijumpsister').show();
	}
	jQuery('#lolijump').click(function() {
  		jQuery('#section-lolijumptext').fadeToggle(400);
	});

	if (jQuery('#lolijump:checked').val() !== undefined) {
		jQuery('#section-lolijumptext').show();
	}	
	jQuery('#banner_rand').click(function() {
  		jQuery('#section-banner_rand_n').fadeToggle(400);
	});

	if (jQuery('#banner_rand:checked').val() !== undefined) {
		jQuery('#section-banner_rand_n').show();
	}
	jQuery('#banner_api_on').click(function() {
  		jQuery('#section-banner_api_url').fadeToggle(400);
	});

	if (jQuery('#banner_api_on:checked').val() !== undefined) {
		jQuery('#section-banner_api_url').show();
	}	
	jQuery('#open_author_info').click(function() {
  		jQuery('#section-authorinfo').fadeToggle(400);
	});

	if (jQuery('#open_author_info:checked').val() !== undefined) {
		jQuery('#section-authorinfo').show();
	}	
    jQuery('#baidutuisong').click(function() {
  		jQuery('#section-baidutuisongkey').fadeToggle(400);
	});

	if (jQuery('#baidutuisong:checked').val() !== undefined) {
		jQuery('#section-baidutuisongkey').show();
	}	
	jQuery('#sign_f').click(function() {
  		jQuery('#section-reg_question,#section-sign_zhcn,#section-users_login, #section-users_reg, #section-users_reset,#section-users_page, #section-regto, #section-loginto,#section-czcard_src,#section-user_banner_src ').fadeToggle(400);
	});

	if (jQuery('#sign_f:checked').val() !== undefined) {
		jQuery('#section-reg_question,#section-sign_zhcn, #section-users_login, #section-users_reg,#section-users_reset, #section-users_page, #section-regto, #section-loginto,#section-czcard_src,#section-user_banner_src').show();
	}	
	jQuery('#smtpmail').click(function() {
  		jQuery('#section-fromnames,#section-smtphost,#section-smtpprot, #section-smtpusername, #section-smtppassword').fadeToggle(400);
	});

	if (jQuery('#smtpmail:checked').val() !== undefined) {
		jQuery('#section-fromnames,#section-smtphost,#section-smtpprot, #section-smtpusername, #section-smtppassword').show();
	}		
jQuery('#thumbnail_api').click(function() {
      jQuery('#section-thumbnail_api_url').fadeToggle(400);
  });

	if (jQuery('#thumbnail_api:checked').val() !== undefined) {
		jQuery('#section-thumbnail_api_url').show();
	}
	jQuery('#hitokoto_on').click(function() {
  		jQuery('#section-hitokoto_text').fadeToggle(400);
	});

	if (jQuery('#hitokoto_on:checked').val() !== undefined) {
		jQuery('#section-hitokoto_text').show();
	}	




jQuery(document).on('click','.boxmoe-font-replace',function(){
    var uid = jQuery(this).data('upload-id');
    jQuery('#upload-' + uid).trigger('click');
});
jQuery(document).on('click','.boxmoe-font-delete',function(){
    var base = jQuery(this).data('base');
    var nameId = base + '_name';
    var upId = base + '_woff2';
    var urlId = base + '_woff2_url';
    jQuery('#'+nameId).val('');
    jQuery('#'+upId).val('').removeClass('has-file');
    jQuery('#remove-'+upId).remove();
    jQuery('#upload-'+upId).show();
    jQuery('#'+upId+'-image').empty();
    jQuery('#'+urlId).val('');
});

// Fonts modal logic
jQuery(function($){
  function getOptionNameFromTable(table){
    var name = table.data('option-name');
    return name || 'options-framework-theme';
  }

  var currentIndex = null;
  function openModal(prefill){
    $('#boxmoe-fonts-modal-mask').css('display','flex');
    if(prefill){
      $('#bmf-name').val(prefill.name||'');
      $('#bmf-woff2').val(prefill.woff2||'');
      $('#bmf-url').val(prefill.url||'');
      if(prefill.woff2){
        $('#bmf-woff2-image').html('<img src="'+prefill.woff2+'">');
      } else { $('#bmf-woff2-image').empty(); }
    } else {
      $('#bmf-name,#bmf-woff2,#bmf-url').val('');
      $('#bmf-woff2-image').empty();
    }
    toggleExclusive();
    clearError();
  }
  function closeModal(){ $('#boxmoe-fonts-modal-mask').hide(); }

  function clearError(){ $('#bmf-error').hide().text(''); $('#bmf-name,#bmf-woff2,#bmf-url').removeClass('error'); }
  function showError(msg){ $('#bmf-error').show().text(msg); }
  function toggleExclusive(){
    var hasUpload = $('#bmf-woff2').val().trim().length>0;
    var hasUrl = $('#bmf-url').val().trim().length>0;
    if(hasUpload){ $('#bmf-url').prop('disabled',true).addClass('disabled'); } else { $('#bmf-url').prop('disabled',false).removeClass('disabled'); }
    if(hasUrl){ $('#bmf-woff2').prop('disabled',true).addClass('disabled'); $('#bmf-upload-btn').prop('disabled',true).addClass('disabled'); } else { $('#bmf-woff2').prop('disabled',false).removeClass('disabled'); $('#bmf-upload-btn').prop('disabled',false).removeClass('disabled'); }
  }
  $('#bmf-woff2,#bmf-url').on('input', function(){ toggleExclusive(); clearError(); });

  var currentTable = null;
  $('#boxmoe-fonts-add-btn').on('click', function(){ currentIndex = null; currentTable = $(this).closest('.section').find('.boxmoe-fonts-table'); openModal(); });
  $(document).on('click','.boxmoe-font-edit', function(){
    var row = $(this).closest('.fonts-table-row');
    currentTable = $(this).closest('.boxmoe-fonts-table');
    currentIndex = row.data('index');
    var prefill = {
      name: row.find('input[name*="[name]"]').val(),
      woff2: row.find('input[name*="[woff2]"]').val(),
      url: row.find('input[name*="[url]"]').val()
    };
    openModal(prefill);
  });
  $('#bmf-cancel').on('click', closeModal);

  // Upload inside modal
  var modalFrame;
  $('#bmf-upload-btn').on('click', function(e){
    e.preventDefault();
    if(modalFrame){ modalFrame.open(); return; }
    modalFrame = wp.media({ title: '选择 woff2 文件', button: { text: '使用此文件' } });
    modalFrame.on('select', function(){
      var att = modalFrame.state().get('selection').first().toJSON();
      $('#bmf-woff2').val(att.url);
      $('#bmf-woff2-image').html('<img src="'+att.url+'">');
    });
    modalFrame.open();
  });

  $('#bmf-save').on('click', function(){
    var name = $('#bmf-name').val().trim();
    var woff2 = $('#bmf-woff2').val().trim();
    var url = $('#bmf-url').val().trim();
    clearError();
    if(!name){ showError('请输入显示名称'); $('#bmf-name').addClass('error'); return; }
    if(!woff2 && !url){ showError('上传或链接至少填写其一'); $('#bmf-woff2,#bmf-url').addClass('error'); return; }
    if(woff2 && url){ showError('上传与链接只能填写一个'); $('#bmf-woff2,#bmf-url').addClass('error'); return; }
    var table = currentTable || $('.boxmoe-fonts-table').first();
    var optionName = getOptionNameFromTable(table);
    var nextIndex = (currentIndex===null)? table.find('.fonts-table-row').length : currentIndex;
    if(currentIndex===null){ table.find('.fonts-empty').remove(); }
    var hidden = '<input type="hidden" name="'+optionName+'[boxmoe_fonts]['+nextIndex+'][name]" value="'+name+'" />'
               + '<input type="hidden" name="'+optionName+'[boxmoe_fonts]['+nextIndex+'][woff2]" value="'+(url?'' : woff2)+'" />'
               + '<input type="hidden" name="'+optionName+'[boxmoe_fonts]['+nextIndex+'][url]" value="'+(woff2?'':url)+'" />';
    var rowHtml = ''+
    '<div class="fonts-table-row" data-index="'+nextIndex+'">'
      +'<div class="cell cell-name"><span class="cell-text">'+name+'</span>'+hidden+'</div>'
      +'<div class="cell cell-upload"><span class="cell-text">'+(url?'' : (woff2||'未设置'))+'</span></div>'
      +'<div class="cell cell-url"><span class="cell-text">'+(woff2?'':(url||'未设置'))+'</span></div>'
      +'<div class="cell cell-actions">'
        +'<button type="button" class="btn-pill btn-blue boxmoe-font-edit" data-index="'+nextIndex+'">修改</button>'
        +'<button type="button" class="btn-pill btn-red boxmoe-font-delete-row" data-index="'+nextIndex+'">删除</button>'
      +'</div>'
    +'</div>';
    if(currentIndex===null){
      table.append(rowHtml);
    }else{
      table.find('.fonts-table-row[data-index="'+currentIndex+'"]').replaceWith(rowHtml);
    }
    closeModal();
  });

  // Uploader delegation inside table
  var tableFrame;
  $(document).on('click','.boxmoe-fonts-table .upload-button', function(e){
    e.preventDefault();
    var section = $(this).closest('.section');
    var btn = $(this);
    var input = btn.siblings('.upload');
    var shot = btn.siblings('.screenshot');
    currentTable = btn.closest('.boxmoe-fonts-table');
    if(tableFrame){ tableFrame.open(); return; }
    tableFrame = wp.media({ title: btn.data('choose')||'选择文件', button: { text: btn.data('update')||'使用此文件' } });
    tableFrame.on('select', function(){
      var att = tableFrame.state().get('selection').first().toJSON();
      input.val(att.url);
      var urlInput = btn.closest('.fonts-table-row').find('.cell-url input');
      urlInput.val('').prop('disabled',true).addClass('disabled');
      shot.empty().append('<img src="'+att.url+'"><a class="remove-image">Remove</a>').show();
      btn.addClass('remove-file').removeClass('upload-button').val('删除');
    });
    tableFrame.open();
  });
  $(document).on('click','.boxmoe-fonts-table .remove-file, .boxmoe-fonts-table .remove-image', function(e){
    e.preventDefault();
    var wrap = $(this).closest('.cell-upload');
    wrap.find('.upload').val('');
    wrap.find('.screenshot').empty().hide();
    wrap.find('.remove-file').addClass('upload-button').removeClass('remove-file').val('上传');
    var urlInput = wrap.closest('.fonts-table-row').find('.cell-url input');
    urlInput.prop('disabled',false).removeClass('disabled');
  });

  // Delete row
  $(document).on('click','.boxmoe-font-delete-row', function(){
    var table = $(this).closest('.boxmoe-fonts-table');
    $(this).closest('.fonts-table-row').remove();
    if(table.find('.fonts-table-row').length===0){
      table.append('<div class="fonts-empty" style="padding:12px;color:oklch(0.5683 0.0198 264.41)">当前为空列表，点击“新增”添加自定义字体</div>');
    }
  });
});
});
</script>

<?php
}
