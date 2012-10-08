(function(){
	var win = window,
		dom = document,
		pos = 'fixed',
		//ie6 _position:absolute;_bottom:auto;_top:expression(eval(document.documentElement.scrollTop+document.documentElement.clientHeight-this.offsetHeight-(parseInt(this.currentStyle.marginTop,10)||0)-(parseInt(this.currentStyle.marginBottom,10)||0)));_margin-top:140px;
		cssText = '.customer{ width:117px; height:236px; background:url(asset/images/callcenter.png) no-repeat;right:10px;top:150px;_filter: expression(document.execCommand("BackgroundImageCache", false, true));}'
			+'.customer p{ width:107px;font-size:12px; margin:0 auto;margin-top:63px; text-align:center; background:url(asset/images/callcenter2.png) no-repeat bottom left}'
			+'.customer ul{ width:107px; margin:0 auto;background:url(asset/images/callcenter2.png) no-repeat bottom left; font-size:12px; padding-bottom:5px;}'
			+'.customer ul li{margin:18px 0 20px 10px;padding-left:10px; height:20px; line-height:20px; cursor:pointer;}'
			+'.customer ul li img{border:0;vertical-align: middle;padding-right:3px}'
			+'.customer .back-top{ display:block; width:50px; line-height:28px; margin:0 auto; text-align:center; background:url(asset/images/callcenter4.png) no-repeat left center; font-size:12px;cursor:pointer; padding-left:20px;}',
		html = '<p>工作时间：9：30-18：00</p><ul>LI</ul><a class="back-top" href="javascript:;">返回顶部</a>',
		sHtml = '<li><a href="tencent://message/?v=3&uin=QQ&site=ayi800&menu=yes"><img src="http://wpa.qq.com/pa?p=1:QQ:45">NICK</a></li>',
		rootNodeStyle;
	aYi = (typeof aYi == 'undefined') ? {} : aYi;
	aYi.customerSer = {
		init : function(){
			var args = arguments,
				tmp = '',
				imgs = [];
			importStyle();
			this.root = dom.createElement('div');
			this.root.className = 'customer';
			for(var i=args.length;i--;){
				var p = args[i];
				tmp += (sHtml.replace(/QQ/g,p.qq).replace('NICK',p.nick));
			}
			this.root.innerHTML = html.replace('LI',tmp);
			tmp = this.root.getElementsByTagName('a');
			this.backTop = tmp[tmp.length-1];
			this.backTop.onclick = goTop;
			tmp = this.root.getElementsByTagName('ul')[0];
			tmp = tmp.getElementsByTagName('li');
			for(var i=tmp.length;i--;){
				imgs.push(tmp[i].getElementsByTagName('img')[0]);
			}
			rootNodeStyle = aYi.customerSer.root.style;
			rootNodeStyle.position = pos;
			(dom.body || dom.documentElement).appendChild(this.root);
			setInterval(function(){
				for(var i = imgs.length;i--;){
					var img = imgs[i],
						src = img.src,
						index = src.indexOf('&');
					src = index == -1 ? src : src.slice(0,index);	
					img.src = src + '&t=' + (+new Date());
				}
			}, 20000);
		}
	};
	function goTop(ac,time){
		ac = ac || 0.1;
		time = time || 16;
		var x1 = 0,
			y1 = 0,
			x2 = 0,
			y2 = 0,
			x3 = 0,
			y3 = 0,
			x,y,speed,
			domEl = dom.documentElement,
			domBody = dom.body;
		if(domEl){
			x1 = domEl.srollLeft || 0;
			y1 = domEl.srollTop || 0;
		}
		if(domBody){
			x2 = domBody.srollLeft || 0;
			y2 = domBody.srollTop || 0;
		}
		x3 = win.scrollX || 0;
		y3 = win.scrollY || 0;
		x = Math.max(x1,Math.max(x2,x3));
		y = Math.max(y1,Math.max(y2,y3));
		speed = 1 + ac;
		win.scrollTo(Math.floor(x/speed),Math.floor(y/speed));
		if(x > 0 || y > 0){
			win.setTimeout(function(){
				goTop(ac,time);
			},time);
		}
	};
	importStyle = function(){
		var s = dom.createElement('style');
		s.type = 'text/css';
		//Safari、Chrome 下innerHTML只读 
		s.textContent = cssText;
		dom.getElementsByTagName('head')[0].appendChild(s);
	};
	if(win.attachEvent){
		importStyle = function(){
			dom.createStyleSheet().cssText = cssText;
		};
		if(/msie 6/.test(navigator.userAgent.toLowerCase())){
			pos = 'absolute';
			win.attachEvent('onscroll',(function(){
				var mode = dom.compatMode && (dom.compatMode === 'CSS1Compat');
				function getTop(){
					getTop = mode ? function(){
						return (dom.documentElement.scrollTop + 150 + "px");
					} : function(){
						return (dom.body.scrollTop + 150 + "px");
					};
					return getTop();
				};
				return function(){		
					if(rootNodeStyle){
						rootNodeStyle.top = getTop();
					}
				};
			})());
		}
	}
})();