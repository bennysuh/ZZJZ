/****
 * jquery.validatebox.js 扩展 
 */
$(document).ready(function(){
    $.extend($.fn.validatebox.defaults.rules, {  
		userID: {  
			validator: function(value){
			    var reg = /^[a-zA-Z_0-9]+$/
		        return reg.test(value);  
			},  
			message: '登錄名必須為字符[A-Z]或數字'  
		}  
    }); 
	
	$.extend($.fn.validatebox.defaults.rules, {  
		validateText: {  
			validator: function(value){
			    var reg = /[<>]+/
		        return !reg.test(value);  
			},  
			message: '不能含有特殊字符<>'  
		}  
    }); 

	$.extend($.fn.validatebox.defaults.rules, {  
		number: {  
			validator: function(value){
			    var reg = /^[0-9]+$/
		        return reg.test(value);  
			},  
			message: '此項必須為數字'  
		}  
    }); 

	$.extend($.fn.validatebox.defaults.rules, {  
		date: {  
			validator: function(value){
			    var reg = /^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/
		        return reg.test(value);  
			},  
			message: '此項必須為yyyy-mm-dd格式的日期'  
		}  
    }); 
    
    $.extend($.fn.validatebox.defaults.rules,{
        mobile:{
            validator:function(value,param){
                return /^(13[0-9]|15[0|1|2|3|6|7|8|9]|18[6|8|9])\d{8}$/.test(value);
            },
            message:'请输入正确的11位手机号码.格式:13120002221'
        },
        postcode:{
        	validator:function(value,param){
            return /^\d{6}$/.test(value);
        },
        message:'请输入正确的6位邮政编码'
       }
    });
    //移除控件的验证
    $.extend($.fn.validatebox.methods, {  
		remove: function(jq,param){  
			var f = param?param:false;  
	        if(f){  
	            //动态combo  
	            var v = $.data(jq[0], 'combo').combo.find('input.combo-text')[0];  
	        }else{  
	            var v = jq[0] ;  
	        }  
	          
	        if($.data(v,'validatebox')){  
	            var tip = $.data(v, 'validatebox').tip;  
	            if (tip){  
	                tip.remove();  
	            }  
	            $(v).removeClass('validatebox-invalid');  
	            $(v).removeClass('validatebox-text');  
	            $(v).unbind('.validatebox');  
	            $(v).removeData('validatebox');  
	        }  
		}
	});  

});
//日期格式化
function formatDate() {
	var year=this.getFullYear();
	var month=this.getMonth()+1;
	var date=this.getDate();
	return year+"-"+month+"-"+date;
}
Date.prototype.formatDate=formatDate; 