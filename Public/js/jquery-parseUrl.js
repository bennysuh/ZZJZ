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
jQuery.parseUrl = {
	parts:[],
	parameters:[],
	url:null,
	length:0,
	parse: function() {
		if (this.url == null) {
			this.url = window.location.href;
		}
		this.length = 0;

		this.parts = this.url.split('?');
		if (this.parts.length > 1) {
			var p = this.parts[1];//所有参数
			var list = p.split('&');//参数数组
			for (var i = 0; i < list.length; i++) {
				var pair = list[i].split('=');//名值对
				var val = '';//参数值
				if (pair.length > 1) {
					val = pair[1];
				}
				if (pair[0].slice(-2) =='[]' || pair[0].slice(-6) =='%5B%5D') {
					if (this.parameters[pair[0]] == null) {
						this.parameters[pair[0]] = [];
						this.length++;
					}
					this.parameters[pair[0]].push(val);
				} else {
					this.parameters[pair[0]] = val;
					this.length++;
				}
				
			}
		}
		
		return this;
	},
	param: function(name, val, isAppend) {
		if (this.length == 0) {
			this.parse();
		}
		if (val != null) {
			if (isAppend == true) {
				if (this.parameters[name] == null) {
					this.parameters[name] = [];
				}
				this.parameters[name].push(encodeURIComponent(val));
			} else {
				this.parameters[name] = encodeURIComponent(val);
			}
		}
		return this.parameters[name];
	},
	setUrl:function(url) {
		this.url = url;
		this.parameters = [];
		this.parse();
		return this;
	},
	removeParam: function(name) {
		if (this.length == 0) {
			this.parse();
		}
		this.parameters[name] = null;
		return this;
	},
	//返回根據參數拼裝的URL
	queryString: function(pure) {
		var query = '';
		for (var key in this.parameters) {
			if (this.parameters[key] == null) continue;
			if ($.isArray(this.parameters[key])) {
				for (var i = 0; i < this.parameters[key].length; i++) {
					query = query + key + '=' + this.parameters[key][i] + '&';
				}
			} else {
				query = query + key + '=' + this.parameters[key] + '&';
			}
			
		}

		if (pure == true) return query;
		
		if (query.length == 0) {
			return this.parts[0];
		} else {
			query = query.substr(0, query.length - 1);
			return this.parts[0] + '?' + query;
		}
	}
};