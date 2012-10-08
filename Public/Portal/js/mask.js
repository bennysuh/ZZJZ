(function(){
	var dom = document,
		ua = navigator.userAgent.toLowerCase(),
		pos = 'fixed',
		defaultTop = 0,
		proColor = '#8ED824',
		cssText = 'body,.ayi-mask,.ayi-mask div,.ayi-mask span,.ayi-mask p,.ayi-mask ul{margin:0;padding:0;}'
			+'.ayi-mask {left:0;}'
			+'.ayi-mask .base{filter:alpha(opacity=50);opacity:0.5;background:#999999;top:0px;left:0px;}'
			+'.ayi-mask .tips{position:absolute;top:0;left:0;background:white;font:15px Arial,Helvetica,"宋体",sans-serif;color:#777;border:3px solid #7F7E7E;}'
			+'.ayi-mask .tips .title{height:22px;line-height:22px;background:#D2E0E6;padding-left:3px;border-bottom:1px solid #7F7E7E;}'
			+'.ayi-mask .pro{width:250px !important;width:254px;height:20px;list-style:none;border:2px solid #117C01;background:#D5F7C5;margin:5px 20px !important;}'
			+'.ayi-mask .pro li{height:20px;float:left;width:25px;}'
			+'.ayi-mask .tips p{height:22px;line-height:22px;padding:5px 0 0 90px;}',
		windowWidth,windowHeight,maskNode,maskNodeStyle,baseNode,baseNodeStyle,tipsNode,tipsNodeStyle,proNode,titleNode,conNode,importStyle,proInterval;	
	 function getSize(){
		var f;
		if (self.innerHeight) { // all except Explorer
			f = function (){
				windowWidth = self.innerWidth;
				windowHeight = self.innerHeight;
			};
		} else if (dom.documentElement && dom.documentElement.clientHeight) { // Explorer 6 Strict Mode
			f = function (){
				windowWidth = dom.documentElement.clientWidth;
				windowHeight = dom.documentElement.clientHeight;
			};
		} else if (dom.body) { // other Explorers
			f = function (){
				windowWidth = dom.body.clientWidth;
				windowHeight = dom.body.clientHeight;
			};
		}
		f();
		getSize = f;
	};
	function winResize(){	
		getSize();
		if(proInterval){
			aYi.mask.resize();
		}
	};
	aYi = (typeof aYi == 'undefined') ? {} : aYi;
	aYi.mask = {
		init : function (content){
			importStyle();
			maskNode = dom.createElement('div');
			baseNode = dom.createElement('div');
			tipsNode = dom.createElement('div');
			titleNode = dom.createElement('div');
			conNode = dom.createElement('p');
			proNode = dom.createElement('ul'); 
			maskNode.className = 'ayi-mask';
			baseNode.className = 'base';
			tipsNode.className = 'tips';
			titleNode.className = 'title';
			titleNode.innerHTML = '提示';
			conNode.innerHTML = content || '请稍后...';
			proNode.className = 'pro';
			proNode.innerHTML='<li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li>';
			maskNodeStyle = maskNode.style;
			baseNodeStyle = baseNode.style;
			tipsNodeStyle = tipsNode.style;
			maskNodeStyle.top = defaultTop;
			maskNodeStyle.position = pos;
			tipsNodeStyle.cssText = 'width:295px !important;width:300px;height:90px;';
			tipsNode.appendChild(titleNode);
			tipsNode.appendChild(conNode);
			tipsNode.appendChild(proNode);
			maskNode.appendChild(baseNode);
			maskNode.appendChild(tipsNode);
			dom.body.appendChild(maskNode);
			winResize();
			this.init = null;
		},
		show : function (content) {
			if(proInterval){return;}
			this.init && this.init(content);
			this.resize();
			maskNodeStyle.display = "";
			var nodes = proNode.childNodes,
				i=0,
				color = proColor;
			nodes[i++].style.background = color;
			proInterval = setInterval(function(){
				var node = nodes[i++];
				if(!node){
					i = 0;
					color = (color == proColor) ? '' : proColor;
				}else{
					node.style.background = color;
				}
			},500);
		},
		resize : function(){
			tipsNodeStyle.top = windowHeight/2 - parseInt(tipsNodeStyle.height)/2 + "px";
			tipsNodeStyle.left = windowWidth/2 - parseInt(tipsNodeStyle.width)/2 + "px";
			baseNodeStyle.width = windowWidth + "px";
			baseNodeStyle.height = windowHeight + "px";
		},
		hide : function () {
			clearInterval(proInterval);
			proInterval = null;
			var nodes = proNode.childNodes;
			for(var i=nodes.length;i--;){
				nodes[i].style.background = '';
			}
			maskNodeStyle.display = "none";
		}
	};
	importStyle = function(){
		var s = dom.createElement('style');
		s.type = 'text/css';
		//Safari、Chrome 下innerHTML只读 
		s.textContent = cssText;
		dom.getElementsByTagName('head')[0].appendChild(s);
	};
	if(window.attachEvent){
		window.attachEvent('onresize',winResize);
		importStyle = function(){
			dom.createStyleSheet().cssText=cssText;
		};
		if(/msie 6/.test(ua)){
			pos = 'absolute';
			window.attachEvent('onscroll',(function(){
				var mode = dom.compatMode && (dom.compatMode === 'CSS1Compat');
				function getTop(){
					getTop = mode ? function(){
						return (dom.documentElement.scrollTop + "px");
					} : function(){
						return (dom.body.scrollTop + "px");
					};
					return getTop();
				};
				return function(){		
					if(proInterval){
						maskNodeStyle.top = getTop();
					}else{
						defaultTop = getTop();
						if(maskNodeStyle){
							maskNodeStyle.top = defaultTop;
						}
					}
				};
			})());
		}
	}else if(window.addEventListener){
		window.addEventListener('resize', winResize, false);
	}else{
		window.onresize = winResize;
	}
})();