(function(){
	var dom = document,
		win = window,
		ua = navigator.userAgent.toLowerCase(),
		pos = 'fixed',
		defaultTop = 0,
		aList = [],
		cssText = 'body,.ayi-photoPlayer,.ayi-photoPlayer div,.ayi-photoPlayer span,.ayi-photoPlayer p,.ayi-photoPlayer ul{margin:0;padding:0;}'
			+'.ayi-photoPlayer {left:0;z-index:999;_filter : expression(document.execCommand("BackgroundImageCache", false, true));}'
			+'.ayi-photoPlayer .base{filter:alpha(opacity=50);opacity:0.5;background:#999999;top:0px;left:0px;}'
			+'.ayi-photoPlayer .photo-frame{position:absolute;top:0;left:0;background:url("/asset/images/loading.gif") no-repeat;border:10px solid #FFFFFF;border-radius:5px;cursor:pointer;}'
			+'.ayi-photoPlayer .frame-border{position:absolute;border-radius:3px;}'
			+'.ayi-photoPlayer .frame-border.hover{background: #216444;}'
			+'.ayi-photoPlayer .frame-border.hover a{background: url("/asset/images/ico_del.gif") no-repeat scroll right top transparent;float:right;width:30px;height:20px;margin:5px 5px 0 0;cursor:pointer;}'
			+'.ayi-photoPlayer .left-arrow{position:absolute;left:0;top:0;filter:alpha(opacity=0);background:#000\\9;}'
			+'.ayi-photoPlayer .right-arrow{position:absolute;right:0;top:0;filter:alpha(opacity=0);background:#000\\9;}'
			+'.ayi-photoPlayer .left-arrow.hlt{background:url("/asset/images/40left.png") no-repeat left center;filter:alpha(opacity=50);}'
			+'.ayi-photoPlayer .right-arrow.hrt{background:url("/asset/images/40right.png") no-repeat right center;filter:alpha(opacity=50);}',
		srcList = [],
		imgsObj = {},
		curIndex = 0,
		windowWidth,windowHeight,maskNode,maskNodeStyle,baseNode,baseNodeStyle,importStyle,photoFrameStyle,
		photoFrame,frameBorder,closeBtn,bindEvent,leftArrow,rightArrow,lArrowStyle,rArrowStyle,frameBorderStyle,inited;	
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
		if(photoFrameStyle && photoFrameStyle.height){
			aYi.photoPlayer.resize();
		}
	};
	function checkSrc(src){
		for(var i=srcList.length;i--;){
			if(srcList[i] == src){
				break;
			}
		}
		return i;
	};
	aYi = (typeof aYi == 'undefined') ? {} : aYi;
	aYi.photoPlayer = {
		init : function (){
			var tmp = dom.getElementsByTagName('img'),
				me = this,
				base = function(){
					importStyle();
					maskNode = dom.createElement('div');
					baseNode = dom.createElement('div');
					photoFrame = dom.createElement('div');
					frameBorder = dom.createElement('div');
					leftArrow = dom.createElement('div');
					rightArrow = dom.createElement('div');
					photoFrame.className = 'photo-frame';
					baseNode.className = 'base';
					maskNode.className = 'ayi-photoPlayer';
					frameBorder.className = 'frame-border';
					frameBorder.innerHTML = '<a href="javascript:;" title="关闭" hidefocus="true"></a>';
					closeBtn = frameBorder.firstChild;
					closeBtn.style.display = 'none';
					leftArrow.className = 'left-arrow';
					rightArrow.className = 'right-arrow';
					lArrowStyle = leftArrow.style;
					rArrowStyle = rightArrow.style;
					maskNodeStyle = maskNode.style;
					baseNodeStyle = baseNode.style;
					frameBorderStyle = frameBorder.style;
					photoFrameStyle = photoFrame.style;
					maskNodeStyle.top = defaultTop;
					maskNodeStyle.position = pos;
					photoFrame.appendChild(leftArrow);
					photoFrame.appendChild(rightArrow);
					maskNode.appendChild(baseNode);
					maskNode.appendChild(frameBorder);
					maskNode.appendChild(photoFrame);
					dom.body.appendChild(maskNode);
					bindEvent(leftArrow,'mouseover',function(){
						var cls = leftArrow.className;
						if(cls.indexOf('hlt') == -1){
							leftArrow.className = cls + ' hlt';
							me.showFrameBorder();
						}
					});
					bindEvent(leftArrow,'mouseout',function(){
						var cls = leftArrow.className;
						if(cls.indexOf('hlt') != -1){
							leftArrow.className = 'left-arrow';
							me.hideFrameBorder();
						}
					});
					bindEvent(rightArrow,'mouseover',function(){
						var cls = rightArrow.className;
						if(cls.indexOf('hrt') == -1){
							rightArrow.className = cls + ' hrt';
							me.showFrameBorder();
						}
					});
					bindEvent(rightArrow,'mouseout',function(){
						var cls = rightArrow.className;
						if(cls.indexOf('hrt') != -1){
							rightArrow.className = 'right-arrow';
							me.hideFrameBorder();
						}
					});
					bindEvent(frameBorder,'mouseover',function(){
						me.showFrameBorder();
					});
					bindEvent(frameBorder,'mouseout',function(){
						me.hideFrameBorder();
					});
					bindEvent(leftArrow,'click',function(){
						me.pre();
					});
					bindEvent(rightArrow,'click',function(){
						me.next();
					});
					bindEvent(closeBtn,'click',function(){
						me.hideImg(curIndex);
						me.hide();
					});
					winResize();
					base = null;
				};
			for(var i=0,len = tmp.length;i<len;i++){
				var img = tmp[i],
					a = img.parentNode,
					h = a.href,
					index;
				if(a && a.tagName.toLowerCase() == 'a' && h && /jpg|gif|png/i.test(h.substring(h.lastIndexOf('.')))){
					a.target = '';
					a.href = 'javascript:;';
					index = checkSrc(h);
					if(index == -1){
						index = srcList.push(h)-1;
					}
					bindEvent(a,'click',(function(idx){
						return function(){
							base && base();
							me.show(idx);
						};
					})(index));
				}
			}
			this.init = null;
		},
		show : function (index) {
			if(this._loading){return;}
			var img = imgsObj[index],imgStyle;
			curIndex = index;
			this._loading = true;
			maskNodeStyle.display = "";
			this.setFrameSize(352, 400);
			if(img){
				this.setSize(img);
				img.style.display = '';
				return;
			}
			var me = this,
			photoImg = dom.createElement('img');
			photoImg.onload = function(e){
				photoFrame.appendChild(photoImg);
				photoImg.w = photoImg.clientWidth;
				photoImg.h = photoImg.clientHeight;
				me.setSize(photoImg);
			};
			photoImg.src = srcList[index];
			imgsObj[index] = photoImg;
		},
		resize : function(){
			var pTop = windowHeight/2 - parseInt(photoFrameStyle.height)/2,
				pLeft = windowWidth/2 - parseInt(photoFrameStyle.width)/2;
			frameBorderStyle.top = pTop - 22 + "px";
			frameBorderStyle.left = pLeft - 3 + "px";
			photoFrameStyle.top = pTop + "px";
			photoFrameStyle.left = pLeft + "px";
			baseNodeStyle.width = windowWidth + "px";
			baseNodeStyle.height = windowHeight + "px";
		},
		hide : function () {
			maskNodeStyle.display = "none";
		},
		hideImg : function(index){
			var img = imgsObj[index];
			if(img){
				img.style.display = 'none';
			}
		},
		setFrameSize : function(h, w){
			var hw = w/2 + 'px',
				fh = h + 46 + 'px',
				fw = w + 26 + 'px';
			w = w + 'px';
			h = h + 'px';
			frameBorderStyle.width = fw;
			frameBorderStyle.height = fh;
			photoFrameStyle.width = w;
			photoFrameStyle.height = h;
			lArrowStyle.height = h;
			lArrowStyle.width = hw;
			rArrowStyle.height = h;
			rArrowStyle.width = hw;
			this.resize();
		},
		next : function(){
			if(this._loading){return;}
			var i = curIndex + 1;
			if(i == srcList.length){return;}
			this.hideImg(curIndex);
			this.show(i);
		},
		pre : function(){
			if(this._loading){return;}
			var i = curIndex - 1;
			if(i < 0){return;}
			this.hideImg(curIndex);
			this.show(i);
		},
		showFrameBorder : function(){
			var cls = frameBorder.className;
			if(cls.indexOf('hover') == -1){
				frameBorder.className = cls + ' hover';
				closeBtn.style.display = '';
			}
		},
		hideFrameBorder : function(){
			var cls = frameBorder.className;
			if(cls.indexOf('hover') != -1){
				frameBorder.className = 'frame-border';
				closeBtn.style.display = 'none';
			}
		},
		setSize : function(photoImg){
			var w = photoImg.w,
				h = photoImg.h,
				pd = 80,
				dw = w - windowWidth + pd,
				dh;	
			if(dw > 0){
				h = (1-dw/w)*h;
				w = w - dw;
			}
			dh = h - windowHeight + pd;
			if(dh > 0){
				w = (1-dh/h)*w;
				h = h - dh;
			}
			photoImg.style.width = w + 'px';
			photoImg.style.height = h + 'px';
			this.setFrameSize(h, w);
			this._loading = false;
		}
	};
	importStyle = function(){
		var s = dom.createElement('style');
		s.type = 'text/css';
		//Safari、Chrome 下innerHTML只读 
		s.textContent = cssText;
		dom.getElementsByTagName('head')[0].appendChild(s);
	};
	
	function start(){
		aYi.photoPlayer.init && aYi.photoPlayer.init();
	};
	
	function checkReady(){
		if(!inited){
			if(document.readyState === 'complete'){
				return true;
			}
		}
		return inited;
	};
	
	
	function readyStateMethod(){
		if(checkReady()){
			start();
			return;
		}
		setTimeout(arguments.callee, 25);
	};
	
	if(win.addEventListener){
		bindEvent = function(obj, evt, foo){
			obj.addEventListener(evt, foo, false);
		};
	}else if(win.attachEvent){
		bindEvent = function(obj, evt, foo){
			obj.attachEvent('on'+evt, foo);
		};
		importStyle = function(){
			dom.createStyleSheet().cssText=cssText;
		};
		if(/msie 6/.test(ua)){
			pos = 'absolute';
			bindEvent(win,'scroll',(function(){
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
					defaultTop = getTop();
					if(maskNodeStyle){
						maskNodeStyle.top = defaultTop;
					}
				};
			})());
		}
		if(window === window.top){
			(function() {
				if(checkReady()){
					start();
					return;
				}
				try {
					document.documentElement.doScroll("left");
				}catch(e) {
					setTimeout(arguments.callee, 25);
					return;
				}
				start();
			})();
		}
	}else{
		bindEvent = function(obj, evt, foo){
			obj['on'+evt] = foo;
		};
	}
	bindEvent(win,'resize',winResize);
	
	if(!checkReady()){
		bindEvent(dom, 'DOMContentLoaded', start);
		readyStateMethod();
		bindEvent(window,'load',start);
	}
})();