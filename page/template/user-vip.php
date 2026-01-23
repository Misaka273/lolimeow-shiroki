<?php
/**
 * @link https://www.boxmoe.com
 * @package lolimeow
 */
//boxmoe.com===安全设置=阻止直接访问主题文件
if(!defined('ABSPATH')){echo'Look your sister';exit;}

// 处理VIP升级表单提交
if (isset($_POST['Submit']) && $_POST['Submit'] == '确认购买') {
    // 获取表单数据
    $userType = isset($_POST['userType']) && is_numeric($_POST['userType']) ? intval($_POST['userType']) : 0;
    $payType = isset($_POST['payType']) ? $_POST['payType'] : '';
    
    // 根据支付方式处理
    if ($payType == 'alipay' || $payType == 'wechat') {
        // 在线支付，跳转到相应的支付页面
        $priceArr = array('6' => 'erphp_day_price', '7' => 'erphp_month_price', '8' => 'erphp_quarter_price', '9' => 'erphp_year_price', '10' => 'erphp_life_price');
        $priceType = $priceArr[$userType];
        $price = get_option($priceType);
        
        // 跳转到支付页面
        $paymentUrl = constant("erphpdown") . "payment/" . $payType . ".php?ice_money=" . $price . "&user_type=" . $userType;
        wp_redirect($paymentUrl);
        exit;
    } else {
        // 余额支付，调用现有的AJAX接口
        // 这里使用JavaScript处理，保持页面不刷新
    }
}

// 处理VIP充值卡
if (isset($_POST['Submit']) && $_POST['Submit'] == '确认升级') {
    $vipCard = isset($_POST['vipCard']) ? trim($_POST['vipCard']) : '';
    if ($vipCard) {
        // 调用充值卡处理函数
        if (function_exists('erphpdown_vipcard_exchange')) {
            $result = erphpdown_vipcard_exchange($vipCard);
            if ($result === true) {
                $error = '<div class="updated settings-error"><p>VIP充值卡使用成功！</p></div>';
            } else {
                $error = '<div class="error settings-error"><p>' . $result . '</p></div>';
            }
        }
    } else {
        $error = '<div class="error settings-error"><p>请输入VIP卡号！</p></div>';
    }
}

    global $wpdb, $current_user;
    $vip_update_pay = get_option('vip_update_pay');
    $error = '';                         
    $erphp_life_price    = get_option('erphp_life_price');
    $erphp_year_price    = get_option('erphp_year_price');
    $erphp_quarter_price = get_option('erphp_quarter_price');
    $erphp_month_price  = get_option('erphp_month_price');
    $erphp_day_price  = get_option('erphp_day_price');
    $erphp_life_name    = get_option('erphp_life_name')?get_option('erphp_life_name'):'终身VIP';
    $erphp_year_name    = get_option('erphp_year_name')?get_option('erphp_year_name'):'包年VIP';
    $erphp_quarter_name = get_option('erphp_quarter_name')?get_option('erphp_quarter_name'):'包季VIP';
    $erphp_month_name  = get_option('erphp_month_name')?get_option('erphp_month_name'):'包月VIP';
    $erphp_day_name  = get_option('erphp_day_name')?get_option('erphp_day_name'):'体验VIP';
    $erphp_life_days    = get_option('erphp_life_days');
    $erphp_year_days    = get_option('erphp_year_days');
    $erphp_quarter_days = get_option('erphp_quarter_days');
    $erphp_month_days  = get_option('erphp_month_days');
    $erphp_day_days  = get_option('erphp_day_days');
    $moneyVipName = get_option('ice_name_alipay');
    $okMoney=erphpGetUserOkMoney();
    $userTypeId=getUsreMemberType();
    
    // 获取当前VIP类型名称
    function getCurrentVipTypeName($userTypeId) {
        global $erphp_life_name, $erphp_year_name, $erphp_quarter_name, $erphp_month_name, $erphp_day_name;
        switch($userTypeId) {
            case 10: return $erphp_life_name;
            case 9: return $erphp_year_name;
            case 8: return $erphp_quarter_name;
            case 7: return $erphp_month_name;
            case 6: return $erphp_day_name;
            default: return '普通会员';
        }
    }
    
    $currentVipName = getCurrentVipTypeName($userTypeId);
  ?>
  
  <!-- 当前VIP状态 -->
  <div class="card border-primary mb-4 mt-3 border-1 shadow-sm">
    <div class="card-header border-0 bg-primary bg-opacity-10 py-3">
      <h3 class="mb-1 text-primary-emphasis">当前VIP状态</h3>
    </div>
    <div class="card-body">
      <p class="card-text">您当前是：<strong><?php echo $currentVipName; ?></strong><?php if($userTypeId > 5 && $userTypeId < 10){?>，到期时间：<?php echo getUsreMemberTypeEndTime(); ?><?php }?></p>
    </div>
  </div>
  
  <!-- 升级VIP选项卡 -->
  <div class="row g-4 mb-4">
    <!-- 左侧：在线支付和余额支付 -->
    <div class="col-lg-6">
      <!-- 在线支付 -->
      <div class="card border-primary mb-4 border-1 shadow-sm">
        <div class="card-header border-0 bg-primary bg-opacity-10 py-3">
          <h3 class="mb-1 text-primary-emphasis">在线充值</h3>
        </div>
        <div class="card-body">
          <form method="post" id="vip-upgrade-form" class="row g-3 needs-validation">
            <div class="col-lg-12">
              <label for="userType" class="form-label">VIP类型</label>
              <select class="form-select fontsize" name="userType" id="userType" required>
                <?php if($erphp_day_price){?>
                  <option value="6" selected><?php echo $erphp_day_name;?> --- <?php echo $erphp_day_price;?><?php echo $moneyVipName;?> (1天)</option>
                <?php }?>
                <?php if($erphp_month_price){?>
                  <option value="7"><?php echo $erphp_month_name;?> --- <?php echo $erphp_month_price;?><?php echo $moneyVipName;?> (30天)</option>
                <?php }?>
                <?php if($erphp_quarter_price){?>
                  <option value="8"><?php echo $erphp_quarter_name;?> --- <?php echo $erphp_quarter_price;?><?php echo $moneyVipName;?> (3个月)</option>
                <?php }?>
                <?php if($erphp_year_price){?>
                  <option value="9"><?php echo $erphp_year_name;?> --- <?php echo $erphp_year_price;?><?php echo $moneyVipName;?> (12个月)</option>
                <?php }?>
                <?php if($erphp_life_price){?>
                  <option value="10"><?php echo $erphp_life_name;?> --- <?php echo $erphp_life_price;?><?php echo $moneyVipName;?></option>
                <?php }?>
              </select>
            </div>
            
            <div class="col-lg-12">
              <label for="payType" class="form-label">支付方式</label>
              <div class="payment-options">
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="payType" id="alipay" value="alipay">
                  <label class="form-check-label" for="alipay">
                    支付宝
                  </label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="payType" id="wechat" value="wechat" checked>
                  <label class="form-check-label" for="wechat">
                    微信
                  </label>
                </div>
              </div>
            </div>
            
            <div class="col-lg-12">
              <button type="submit" class="btn btn-primary" name="Submit" value="确认购买">立即升级</button>
            </div>
          </form>
        </div>
      </div>
      
      <!-- 余额支付 -->
      <div class="card border-success mb-4 border-1 shadow-sm">
        <div class="card-header border-0 bg-success bg-opacity-10 py-3">
          <h3 class="mb-1 text-success-emphasis">余额支付</h3>
        </div>
        <div class="card-body">
          <form method="post" id="vip-balance-form" class="row g-3 needs-validation">
            <div class="col-lg-12">
              <div class="d-flex align-items-center">
                <label for="balance" class="form-label me-3">当前余额</label>
                <p class="text-success mb-0"><strong><?php echo sprintf("%.2f",$okMoney);?><?php echo $moneyVipName;?></strong></p>
              </div>
            </div>
            
            <div class="col-lg-12">
              <label for="balanceType" class="form-label">VIP类型</label>
              <select class="form-select fontsize" name="userType" id="balanceType" required>
                <?php if($erphp_day_price){?>
                  <option value="6" selected><?php echo $erphp_day_name;?> --- <?php echo $erphp_day_price;?><?php echo $moneyVipName;?> (1天)</option>
                <?php }?>
                <?php if($erphp_month_price){?>
                  <option value="7"><?php echo $erphp_month_name;?> --- <?php echo $erphp_month_price;?><?php echo $moneyVipName;?> (30天)</option>
                <?php }?>
                <?php if($erphp_quarter_price){?>
                  <option value="8"><?php echo $erphp_quarter_name;?> --- <?php echo $erphp_quarter_price;?><?php echo $moneyVipName;?> (3个月)</option>
                <?php }?>
                <?php if($erphp_year_price){?>
                  <option value="9"><?php echo $erphp_year_name;?> --- <?php echo $erphp_year_price;?><?php echo $moneyVipName;?> (12个月)</option>
                <?php }?>
                <?php if($erphp_life_price){?>
                  <option value="10"><?php echo $erphp_life_name;?> --- <?php echo $erphp_life_price;?><?php echo $moneyVipName;?></option>
                <?php }?>
              </select>
            </div>
            
            <div class="col-lg-12">
              <button type="submit" class="btn btn-success" name="Submit" value="确认购买">立即升级</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <!-- 右侧：充值卡支付和VIP价格列表 -->
    <div class="col-lg-6">
      <div class="row g-4">
        <!-- 充值卡支付 -->
        <div class="col-12">
          <div class="card border-info mb-4 border-1 shadow-sm">
            <div class="card-header border-0 bg-info bg-opacity-10 py-3">
              <h3 class="mb-1 text-info-emphasis">充值卡支付</h3>
            </div>
            <div class="card-body">
              <form method="post" id="vip-card-form" class="row g-3 needs-validation">
                <div class="col-lg-12">
                  <label for="vipCard" class="form-label">VIP卡号</label>
                  <input type="text" class="form-control fontsize" id="vipCard" name="vipCard" placeholder="请输入VIP卡号" required>
                </div>
                
                <div class="col-lg-12">
                  <button type="submit" class="btn btn-info" name="Submit" value="确认升级">立即升级</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        
        <!-- VIP特权说明 -->
        <div class="col-12">
          <div class="card border-success mb-4 border-1 shadow-sm">
            <div class="card-header border-0 bg-success bg-opacity-10 py-3">
              <h3 class="mb-1 text-success-emphasis">VIP特权说明</h3>
            </div>
            <div class="card-body">
              <ul class="list-unstyled">
                <li class="mb-2">
                  <i class="fa fa-check-circle text-success me-2"></i>享受所有VIP专享资源
                </li>
                <li class="mb-2">
                  <i class="fa fa-check-circle text-success me-2"></i>下载无限制
                </li>
                <li class="mb-2">
                  <i class="fa fa-check-circle text-success me-2"></i>专属客服支持
                </li>
                <li class="mb-2">
                  <i class="fa fa-check-circle text-success me-2"></i>优先获取新品资源
                </li>
                <li class="mb-2">
                  <i class="fa fa-check-circle text-success me-2"></i>享受折扣优惠
                </li>
              </ul>
            </div>
          </div>
        </div>
        
        <!-- VIP常见问题 -->
        <div class="col-12">
          <div class="card border-warning mb-4 border-1 shadow-sm">
            <div class="card-header border-0 bg-warning bg-opacity-10 py-3">
              <h3 class="mb-1 text-warning-emphasis">常见问题</h3>
            </div>
            <div class="card-body">
              <div class="accordion" id="vipFAQ">
                <div class="accordion-item">
                  <h6 class="accordion-header" id="faq1">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1">
                      VIP有效期如何计算？
                    </button>
                  </h6>
                  <div id="collapse1" class="accordion-collapse collapse" aria-labelledby="faq1" data-bs-parent="#vipFAQ">
                    <div class="accordion-body">
                      VIP有效期从购买之日起计算，例如购买1个月VIP，有效期为30天。
                    </div>
                  </div>
                </div>
                <div class="accordion-item">
                  <h6 class="accordion-header" id="faq2">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                      如何查看VIP到期时间？
                    </button>
                  </h6>
                  <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="faq2" data-bs-parent="#vipFAQ">
                    <div class="accordion-body">
                      在个人中心可以查看VIP类型和到期时间。
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- VIP价格列表 -->
  <div class="card border-primary mb-4 mt-3 border-1 shadow-sm">
    <div class="card-header border-0 bg-primary bg-opacity-10 py-3">
      <h3 class="mb-1 text-primary-emphasis">VIP价格列表</h3>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-lg-12">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>VIP类型</th>
                  <th>价格</th>
                  <th>有效期</th>
                  <th>操作</th>
                </tr>
              </thead>
              <tbody>
                <?php if($erphp_day_price): ?>
                  <tr>
                    <td><?php echo $erphp_day_name; ?></td>
                    <td><?php echo $erphp_day_price;?><?php echo $moneyVipName;?></td>
                    <td><?php echo $erphp_day_days;?>天</td>
                    <td>
                      <button class="btn btn-primary btn-sm fontsize" 
                              data-level="6" 
                              data-price="<?php echo $erphp_day_price;?>" 
                              data-type="<?php echo $erphp_day_name;?>" 
                              data-days="<?php echo $erphp_day_days;?>">
                          立即升级
                      </button>
                    </td>
                  </tr>
                <?php endif; ?>
                
                <?php if($erphp_month_price): 
                          $old_price = '';
                          if($vip_update_pay){
                                  if($userTypeId == 6 && $erphp_day_price){
                                     $old_price .= '原价<del>'.$erphp_month_price.'</del> 差价';
                                      $old_price .= $erphp_month_price - $erphp_day_price;
                                   }else{
                                      $old_price .= $erphp_month_price;
                                   }
                               }else{
                                   $old_price .= $erphp_month_price;
                               }?>
                  <tr>
                    <td><?php echo $erphp_month_name; ?></td>
                    <td><?php echo $old_price;?><?php echo $moneyVipName;?></td>
                    <td><?php echo $erphp_month_days;?>天</td>
                    <td>
                      <button class="btn btn-primary btn-sm fontsize" 
                              data-level="7" 
                              data-price="<?php echo $erphp_month_price;?>" 
                              data-type="<?php echo $erphp_month_name;?>" 
                              data-days="<?php echo $erphp_month_days;?>">
                          立即升级
                      </button>
                    </td>
                  </tr>
                <?php endif; ?>
                
                <?php if($erphp_quarter_price):
                          $old_price = '';
                          if($vip_update_pay){
                                  if($userTypeId == 6 && $erphp_day_price){
                                     $old_price .= '原价<del>'.$erphp_quarter_price.'</del> 差价';
                                      $old_price .= $erphp_quarter_price - $erphp_day_price;
                                   }elseif($userTypeId == 7 && $erphp_month_price){
                                      $old_price .= '原价<del>'.$erphp_quarter_price.'</del> 差价';
                                      $old_price .= $erphp_quarter_price - $erphp_month_price;
                                   }else{
                                      $old_price .= $erphp_quarter_price;
                                   }
                               }else{
                                   $old_price .= $erphp_quarter_price;
                               }?>
                  <tr>
                    <td><?php echo $erphp_quarter_name; ?></td>
                    <td><?php echo $old_price;?><?php echo $moneyVipName;?></td>
                    <td><?php echo $erphp_quarter_days;?>个月</td>
                    <td>
                      <button class="btn btn-primary btn-sm fontsize" 
                              data-level="8" 
                              data-price="<?php echo $erphp_quarter_price;?>" 
                              data-type="<?php echo $erphp_quarter_name;?>" 
                              data-days="<?php echo $erphp_quarter_days;?>">
                          立即升级
                      </button>
                    </td>
                  </tr>
                <?php endif; ?>
                
                <?php if($erphp_year_price): 
                          $old_price = '';
                          if($vip_update_pay){
                                  if($userTypeId == 6 && $erphp_day_price){
                                     $old_price .= '原价<del>'.$erphp_year_price.'</del> 差价';
                                      $old_price .= $erphp_year_price - $erphp_day_price;
                                   }elseif($userTypeId == 7 && $erphp_month_price){
                                      $old_price .= '原价<del>'.$erphp_year_price.'</del> 差价';
                                      $old_price .= $erphp_year_price - $erphp_month_price;
                                   }elseif($userTypeId == 8 && $erphp_quarter_price){
                                      $old_price .= '原价<del>'.$erphp_year_price.'</del> 差价';
                                      $old_price .= $erphp_year_price - $erphp_quarter_price;
                                   }else{
                                      $old_price .= $erphp_year_price;
                                   }
                               }else{
                                   $old_price .= $erphp_year_price;
                               }?>
                  <tr>
                    <td><?php echo $erphp_year_name; ?></td>
                    <td><?php echo $old_price;?><?php echo $moneyVipName;?></td>
                    <td><?php echo $erphp_year_days;?>个月</td>
                    <td>
                      <button class="btn btn-primary btn-sm fontsize" 
                              data-level="9" 
                              data-price="<?php echo $erphp_year_price;?>" 
                              data-type="<?php echo $erphp_year_name;?>" 
                              data-days="<?php echo $erphp_year_days;?>">
                          立即升级
                      </button>
                    </td>
                  </tr>
                <?php endif; ?>
                
                <?php if($erphp_life_price): 
                          $old_price = '';
                          if($vip_update_pay){
                                  if($userTypeId == 6 && $erphp_day_price){
                                     $old_price .= '原价<del>'.$erphp_life_price.'</del> 差价';
                                      $old_price .= $erphp_life_price - $erphp_day_price;
                                   }elseif($userTypeId == 7 && $erphp_month_price){
                                      $old_price .= '原价<del>'.$erphp_life_price.'</del> 差价';
                                      $old_price .= $erphp_life_price - $erphp_month_price;
                                   }elseif($userTypeId == 8 && $erphp_quarter_price){
                                      $old_price .= '原价<del>'.$erphp_life_price.'</del> 差价';
                                      $old_price .= $erphp_life_price - $erphp_quarter_price;
                                   }elseif($userTypeId == 9 && $erphp_year_price){
                                      $old_price .= '原价<del>'.$erphp_life_price.'</del> 差价';
                                      $old_price .= $erphp_life_price - $erphp_year_price;
                                   }else{
                                      $old_price .= $erphp_life_price;
                                   }
                               }else{
                                  $old_price .= $erphp_life_price;
                               }?>
                  <tr>
                    <td><?php echo $erphp_life_name; ?></td>
                    <td><?php echo $old_price;?><?php echo $moneyVipName;?></td>
                    <td>永久</td>
                    <td>
                      <button class="btn btn-primary btn-sm fontsize" 
                              data-level="10" 
                              data-price="<?php echo $erphp_life_price;?>" 
                              data-type="<?php echo $erphp_life_name;?>" 
                              data-days="永久">
                          立即升级
                      </button>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <script type="text/javascript">
  // 余额支付表单提交
  jQuery(document).ready(function($) {
    // 余额支付表单提交
    $('#vip-balance-form').submit(function(e) {
      e.preventDefault();
      var userType = $(":select[name='userType']").val();
      
      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          action: 'epd_vip_pay',
          type: userType
        },
        dataType: 'json',
        success: function(response) {
          if (response.error == 0) {
            alert('升级成功！');
            window.location.reload();
          } else {
            alert(response.msg);
          }
        },
        error: function() {
          alert('升级失败，请稍后重试！');
        }
      });
    });
    
    // VIP升级按钮点击事件
    $('.btn-upgrade').click(function() {
      var level = $(this).data('level');
      var price = $(this).data('price');
      var type = $(this).data('type');
      var days = $(this).data('days');
      
      // 自动选择对应的VIP类型
      $(':select[name="userType"]').val(level);
      
      // 滚动到余额支付表单
      $('html, body').animate({
        scrollTop: $('#vip-balance-form').offset().top - 100
      }, 500);
    });
  });
  </script>

