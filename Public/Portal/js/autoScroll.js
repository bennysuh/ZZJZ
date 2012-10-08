(function(){
	aYi = (typeof aYi == 'undefined') ? {} : aYi;
	aYi.autoScroll = {
		init : function(opt){
			var scrollDiv = opt.root;
			scrollDiv.style.cssText = 'height:'+opt.height+'px;overflow:hidden;'+opt.cssText;
			var pos = scrollDiv.style.position;
			if(!pos || (pos != 'absolute' && pos !='relative')){
				scrollDiv.style.position = 'relative';
			}		
			this.list = scrollDiv.getElementsByTagName('ul')[0];
			this.list.style.cssText = 'padding:0;margin:0;position:relative;top:0;left:0;';
			this.listHeight = this.list.offsetHeight;
			if(this.listHeight < 110){return;}
			this.listClone = this.list.cloneNode(true);
			scrollDiv.appendChild(this.listClone);
			this.index = 0;
			this.scroll();
			var me = this;
			function mr(){
				clearTimeout(me.timeout);
			}
			function mt(){
				me.scroll();
			}
			if(scrollDiv.addEventListener){
				scrollDiv.addEventListener('mouseover',mr,false);
				scrollDiv.addEventListener('mouseout',mt,false);
			}else if(scrollDiv.attachEvent){
				scrollDiv.attachEvent('onmouseover',mr);
				scrollDiv.attachEvent('onmouseout',mt);
			}else{
				scrollDiv.onmouseover = mr;
				scrollDiv.onmouseout = mt;
			}
		},
		scroll : function(){
			var me = this;
			this.timeout = setTimeout(function(){
				me.list.style.top = me.listClone.style.top = (-(me.index+=1))+'px';
				if(me.index == me.listHeight){me.index = 0;}
				me.scroll();
			},150);
		}
	};
})();