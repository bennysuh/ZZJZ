// +----------------------------------------------------------------------
// | Elibrary [ ENJOY LIFE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://elibrary.nmg.com.hk All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: ccxopen <ccxopen@gmail.com>
// +----------------------------------------------------------------------
// $Id$
(function($){
$.fn.overlay = function(options){
    $this = $(this);
	if(typeof options =="string"){
		var fun = $.fn.overlay.methods[options];
		if(fun){
			return fun($this);
		}
	}

	if ($('#overlayWrap').length > 0) return;

	var opts = $.extend({}, $.fn.overlay.defaults, options);
	var overlayWrap = $("<div id='overlayWrap'></div>");
	
	$this.data('wrapper', overlayWrap);
	
	overlayWrap.css('background-color','#000000');
	overlayWrap.css('position','absolute');
	overlayWrap.css('left','0');
	overlayWrap.css('top','0');
	overlayWrap.css('z-index','9999');
	overlayWrap.css('opacity',opts.opacity);
	overlayWrap.appendTo($('body'));

	$this.appendTo(overlayWrap);
	$this.css('display', 'block');
	$this.css('position','absolute');

	adjustMaskPosition();

	function adjustMaskPosition() {
		var w = windowSize().width;
		var h = windowSize().height;
		overlayWrap.css("width",w).css("height",h);
		$this.css('top', ($(window).height()-$this.height())/2+$(document).scrollTop());
		$this.css('left', ($(window).width()-$this.width())/2+$(document).scrollLeft());
	}

	function windowSize(){
		if(document.compatMode=="BackCompat"){
			return {width:Math.max(document.body.scrollWidth,document.body.clientWidth),height:Math.max(document.body.scrollHeight,document.body.clientHeight)};
		}else{
			return {width:Math.max(document.documentElement.scrollWidth,document.documentElement.clientWidth),height:Math.max(document.documentElement.scrollHeight,document.documentElement.clientHeight)};
		}
	}

	//關閉快捷鍵
	$(window).keydown(function(event){
	    if (event.which == 27) {
			var fun = $.fn.overlay.methods['close'];
			if(fun){
				return fun($this);
			}
		}
	});

	$(window).resize(function(){
	    overlayWrap.css({width:$(window).width(),height:$(window).height()});
        adjustMaskPosition();
	});
};

})(jQuery);

$.fn.overlay.defaults = {
	opacity:1.0
}

$.fn.overlay.methods = {
    close:function(o){
		o.css('display', 'none');
		o.appendTo($('body'));
		o.data('wrapper').remove();
	}
}