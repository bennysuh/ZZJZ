/*模块展开和关闭*/
$(document).ready(function() {
	// 首页顶部轮训图片
    var index = 0;
    var count = 7;
    $(".imgshow_1 a").click(function(){
        window.open(this.href);
        return false;
    })
    $(".imgshow_1 .num li").mouseover(function(){
        index = $(".imgshow_1 .num li").index(this);
        showImg(index);
    });
    $('.imgshow_1').hover(function(){
        if (MyTime) {
            clearInterval(MyTime);
        }
    }, function(){
        MyTime = setInterval(function(){
            showImg(index)
            index++;
            if (index == count) {
                index = 0;
            }
        }, 5000);
    });
    
    var MyTime = setInterval(function(){
	        showImg(index)
	        index++;
	        if (index == count) {
	            index = 0;
	        }
	    }, 5000);
	function showImg(i){
	    $(".imgshow_1 .list img").eq(i).stop(true, true).fadeIn().parent().siblings().find("img").hide();
	    $(".imgshow_1 .imgtit li").eq(i).show().siblings().hide();
	    $(".imgshow_1 .num li").eq(i).addClass("on").siblings().removeClass("on");
	}
    $('.imgshow_1 .list img:first, .imgshow_1 .imgtit li:first').show();
    $('.imgshow_1 .num li:first').addClass('on');

	
	// 推荐月嫂 纪念品列表滚动
	$('.first-and-second-carousel').jcarousel({
		auto : 2,
		wrap : 'circular'
	});
	/* 最新动态滚动 */
	var $this = $(".scrollNews");
	var scrollTimer;
	$this.hover(function() {
		clearInterval(scrollTimer);
	}, function() {
		scrollTimer = setInterval(function() {
			scrollNews($this);
		}, 3000);
	}).trigger("mouseleave");
	function scrollNews(obj) {
		var $self = obj.find("ul:first");
		var lineHeight = $self.find("li:first").height(); // 获取行高
		$self.animate({
			"marginTop" : -lineHeight + "px"
		}, 600, function() {
			$self.css({
				marginTop : 0
			}).find("li:first").appendTo($self); // appendTo能直接移动元素
		})
	}
});