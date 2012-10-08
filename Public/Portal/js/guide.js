(function(){
	var storage = window.localStorage;
	
	if(/guided=1/.test(document.cookie) || (storage && storage.getItem('guided')=='1')){
		arguments.callee = null;
		return;
	}
	var dom = document,
		ua = navigator.userAgent.toLowerCase(),
		pos = 'fixed',
		defaultTop = 0,
		cssText = 'body,.ayi-mask,.ayi-mask div,.ayi-mask span,.ayi-mask p,.ayi-mask ul{margin:0;padding:0;}'
			+'.ayi-mask {left:0;z-index:1000;}'
			+'.ayi-mask .base{filter:alpha(opacity=50);opacity:0.5;background:#999999;top:0px;left:0px;}'
			+'.goway{top:0px;left:0px;position:absolute;z-index:1001;}'
			+'.goway div{width:215px;height:301px;position:relative;cursor:pointer;}'
			+'.goway .k01{top:70px;left:650px;background:url("/asset/images/way01.png") no-repeat;_background:none;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="/asset/images/way01.png");}'
			+'.goway .k02{top:280px;left:0;background:url("/asset/images/way02.png") no-repeat;_background:none;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="/asset/images/way02.png");}'
			+'.goway .k03{top:620px;left:450px;background:url("/asset/images/way03.png") no-repeat;_background:none;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src="/asset/images/way03.png");}',
		windowWidth,windowHeight,maskNode,maskNodeStyle,baseNode,baseNodeStyle,guideNode,guideNodeStyle,importStyle;	
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
		baseNodeStyle.width = windowWidth + "px";
		baseNodeStyle.height = windowHeight + "px";
		var w = windowWidth - 960;
		guideNodeStyle.marginLeft = (w > 0 ? w/2 : 0)+'px';
	};
	aYi = (typeof aYi == 'undefined') ? {} : aYi;
	maskNode = dom.createElement('div');
	baseNode = dom.createElement('div');
	guideNode = dom.createElement('div');
	maskNode.className = 'ayi-mask';
	baseNode.className = 'base';
	guideNode.className = 'goway';
	guideNode.innerHTML = '<div class="k01"></div>';
	maskNodeStyle = maskNode.style;
	baseNodeStyle = baseNode.style;
	guideNodeStyle = guideNode.style;
	aYi.guide = {
		show : function () {
			importStyle();
			maskNodeStyle.top = defaultTop;
			maskNodeStyle.position = pos;
			maskNode.appendChild(baseNode);
			dom.body.appendChild(maskNode);
			dom.body.appendChild(guideNode);
			winResize();
		},
		hide : function () {
			maskNodeStyle.display = "none";
			guideNodeStyle.display = "none";
		}
	};
	importStyle = function(){
		var s = dom.createElement('style');
		s.type = 'text/css';
		//Safari、Chrome 下innerHTML只读 
		s.textContent = cssText;
		dom.getElementsByTagName('head')[0].appendChild(s);
	};
	var arrCls = ['k03','k02'];
	function readed(e){
		e = e || window.event;
		var target = e.target || e.srcElement;
		if(arrCls.length == 0){
			aYi.guide.hide();
			document.cookie = 'guided=1';
			storage && storage.setItem('guided','1');
		}else{
			target.className = arrCls.pop();
			window.scrollTo(0,target.offsetTop - 130);
		}
	};
	
	if(window.attachEvent){
		window.attachEvent('onresize',winResize);
		guideNode.attachEvent('onclick', readed);
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
					maskNodeStyle.top = getTop();
				};
			})());
		}
	}else if(window.addEventListener){
		window.addEventListener('resize', winResize, false);
		guideNode.addEventListener('click', readed, false);
	}else{
		window.onresize = winResize;
		guideNode.onclick = readed;
	}
})();