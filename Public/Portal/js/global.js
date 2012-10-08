$(document).ready(function(){
	//blockUI 提示弹层
	function tipsUI(msg){
		$.blockUI({
			message:'<div style="padding:30px; position:relative;"><a href="#" id="uiClose" class="icon_close"></a>' + msg + '</div>',
			css:{
				width:'auto',
				left:'50%',
				top:'50%'
			}
		});
		//alert($('#blockContent').width() + ' -- ' + $('#blockContent').height() + 'window:' + $(window).width() + ' -- ' + $(window).height());
		centerUI();
		$('#uiClose').click(function(){
			$.unblockUI();
			return false;
		});
	}
	window.tipsUI = tipsUI;

	//blockUI 成功提示
	function successUI(msg){
		$.blockUI({
			message:'<div style="padding:10px 15px; position:relative;">' + msg + '</div>',
			css:{
				width:'auto',
				left:'50%',
				top:'50%'
			}
		});
		centerUI();
		setTimeout($.unblockUI,1600);
	}
	window.successUI = successUI;

	//blockUI 上下左右居中
	function centerUI(){
		$('.blockMsg').css({
			'margin-left':- $('.blockMsg').width()/2,
			'margin-top':- $('.blockMsg').height()/2
		});
	}
	window.centerUI = centerUI;

	//---------------------------首页-----------------------------
	//findbox 找育儿嫂 暂时隐藏
	/*$('.findbox .bar li').click(function(){
		$(this).siblings('li').removeClass('current').end().addClass('current');
		return false;
	});
	$('.findbox .bar li').eq(1).click(function(){
		tipsUI('阿姨800即将提供育儿嫂服务，感谢您的关注。');
	});
	$('#nav ul li').eq(2).click(function(){
		tipsUI('阿姨800即将提供育儿嫂服务，感谢您的关注。');
	});
	*/

	$('.findbox .bar li').click(function(){
		$(this).siblings('li').removeClass('current').end().addClass('current');
		if ($(this).find('a').html() == '找月嫂') {
			$('#type_id').val('1');
		} else {
			$('#type_id').val('2');
		}
		return false;
	});

	//焦点轮播启动
	if($('#slideBox').length>0){
		$('#slideBox').slideBox();
	}

	//滚动人员列表 点击跳页
	$('.carousels ul li a').click(function(){
		window.open($(this).attr('href'),'_self');
	});

});