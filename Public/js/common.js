$(document).ready(function(){
	//分页插件的事件。
    $(".pageButton").click(function(){
		var url = $(this).attr('href');
		window.location.href = url;
	});
	//日期输入框只读。dom中加readonly属性无效
	
	 setTimeout(function(){
		$(".datebox :text").attr("readonly","readonly");
	},2000);
})

// 
// var LEVEL = ["初级","中级","高级","特级","星级"];
// var EDUCATE = ["小学","初中","高中","中专","大专","大学","研究生"];
// var BOO = ["否","是"];
// var LANG = ["普通话","粤语","客家话","英语","闽南语"];
// //生成select的option内容
// function createOptions(options)
// {
	// var str = "";
	// $.each(options,function(){
		// str += "<option>" + this + "</option>"
	// })
	// return str;
// }
// //生成checkbox
// function createCheckBox(options,name)
// {
	// var str = "";
	// $.each(options,function(i){
		// if(i===0){
			// str += "<input type='checkbox' name='" + name + "' checked='checked' value='" + this + "'>" + this;
		// }else{
			// str += "<input type='checkbox' name='" + name + "' value='" + this + "'>" + this;
		// }
// 		
	// })
	// return str;
// }
// 


//+---------------------------------------------------  
//| 字符串转成日期类型   
//| 格式 MM/dd/YYYY MM-dd-YYYY YYYY/MM/dd YYYY-MM-dd  
//+---------------------------------------------------  
function StringToDate(DateStr)  
{   
  
    var converted = Date.parse(DateStr);  
    var myDate = new Date(converted);  
    if (isNaN(myDate))  
    {   
        //var delimCahar = DateStr.indexOf('/')!=-1?'/':'-';  
        var arys= DateStr.split('-');  
        myDate = new Date(arys[0],--arys[1],arys[2]);  
    }  
    return myDate;  
}

//格式化日期
function formater(date)
{
	return date.getFullYear()+"/"+(parseInt(date.getMonth())+1) +"/"+date.getDate();
}