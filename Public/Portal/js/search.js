$(function(){
    var obj = {
    		s_work_exp : 0,
    		s_salary : 0,
    		s_mark : 0,
    		s_zj : 0
    	},
    	currentCls = 'current',
    	srCurrentCls = 'sr-current',
    	date_click = $("#date_click"),
    	birthday_click = $("#birthday_click"),
    	date_show = $("#date_show"),
    	birthday_show = $("#birthday_show"),
    	pageIndex = $("#p"),
    	pageField = $("#page"),
    	resultList = $("#nurse_list"),
    	searchKey = 'score',
    	searchValue = 'uparrow';
    	
    function getDataValue()
    {
    	var data = '',s_month,s_week,birthday;
    	for(var key in obj){
    		data += (key + '=' + obj[key] + '&');
    	}
    	data += ('p='+pageIndex.attr("value"));
        if(date_click.parents("li").attr("class") == currentCls){
            return data + "&s_month="+$("#s_month").attr("value")+"&s_week="+$("#s_week").attr("value");
        }else{
            return data + "&birthday="+$("#birthday").attr("value");
        }
    };
    
    function indexList(indexObj, indexValue)
    {
        var data = getDataValue();
        $.ajax({
            type:"POST",
            url:"ajax/i_search.php?key="+indexObj+"&value="+indexValue,
            dataType:"json",
            data:data,
            success:function(msg)
            {
                if(msg.code == 0)
                {
                    resultList.html(msg.data.list);
                    pageField.html(msg.data.page);
                }
                searchKey = indexObj;
                searchValue = indexValue;
            }
        });
    };	
    
    //翻页
    $(".page_showpage", pageField).live('click', function(){
        $(this).attr("href", "javascript:void(0)");
        pageIndex.attr("value", $(this).attr("page"));
        indexList(searchKey, searchValue);
    });
       
    //孕期tab
    date_click.mouseover(function(){
        var el = date_click.parents("li"),
        	currentClass = el.attr("class");
        if(currentClass != currentCls)
        {
            el.attr("class", currentCls);
            birthday_show.hide();
            date_show.show();
            el.next("li").attr("class", "");
        }
    });
    //预产期tab
    birthday_click.mouseover(function(){
        var el = birthday_click.parents("li"),
        	currentClass = el.attr("class");
        if(currentClass != currentCls)
        {
            el.attr("class", currentCls);
            date_show.hide();
            birthday_show.show();
            el.prev("li").attr("class", "");
        }
    });
    
    //预产期或孕期确定按钮
    $("#datebtn, #birthdaybtn").click(function(){
        pageIndex.attr("value","1");
        indexList('sort', 'sort');
    });
    
    //工作经验，薪酬范围，评分，中介
    $(".s_work_exp, .s_salary, .s_mark, .s_zj", ".index_search").click(function(){
        var el = $(this),
       		thisClass = el.attr("class");
       	if(srCurrentCls != thisClass){	
	        $("."+thisClass,el.parent()).removeClass(srCurrentCls);
	        el.addClass(srCurrentCls);
       	}
        if(thisClass.substring(0,10) != 'headerClass')
        {
            obj[thisClass] = el.attr("value");
            pageIndex.attr("value","1");
            indexList('sort', 'sort');
        }
    });
    
    $( "#birthday" ).datepicker({
        changeMonth: true,
        changeYear: true,
        numberOfMonths: 1,
        yearRange:'<{$fromDate}>:<{$endDate}>',
        onSelect: function( selectedDate ) {
            instance = $( this ).data( "datepicker" ),
            date = $.datepicker.parseDate(
                instance.settings.dateFormat ||
                $.datepicker._defaults.dateFormat,
                selectedDate, instance.settings );
        },
        dateFormat: 'yy-mm-dd',
        clearText:'清除',
		closeText:'关闭',
		prevText:'前一月',
		nextText:'后一月',
		dayNamesMin :['日','一','二','三','四','五','六'],
		monthNamesShort:['一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月']
    });
    $(".infoblock", resultList).live('mouseover',function(){
        $(this).addClass("hover");
    }).live('mouseout', function(){
        $(this).removeClass("hover");
    });
    //薪酬
    $("#salary").toggle(
        function(){
            $(this).next("em").attr("class", "down");
            indexList($(this).attr("id"), 'uparrow');
        },
        function(){
            $(this).next("em").attr("class", "uparrow");
            indexList($(this).attr("id"), 'down');
        }
    );
    //评分
    $("#mark").click(function(){
    	indexList($(this).attr("id"), 'down');
    });
    //资料完整度
    $("#score").click(function(){
    	indexList($(this).attr("id"), 'uparrow');
    });
    
    $("#sort_selected").change(function(){
    	var v1,v2;
        switch($(this).val()){
        	case 'score' :
        		v1 = 'score';
        		v2 = 'uparrow';
        		break;
        	case 'work_exp:ASC' :
        		v1 = 'work_exp';
        		v2 = 'down';
        		break;
        	case 'work_exp:DESC' :
        		v1 = 'work_exp';
        		v2 = 'uparrow';
        		break;
        	default : 
        		v1 = 'score';
        		v2 = 'uparrow';
        }
       	indexList(v1, v2);
    });
    $('.ac_favorite').live('click',function(){
        var butt = $(this);
        $.getJSON(butt.attr('srv'),$("form").serialize(),function(r){
            if(r.code == 2) {
            	window.location.href='http://www.ayi800.com/auth/login.php';
			}else{
				alert(r.msg);
				if(r.code == 0)
				{
					butt.parent().html('已收藏');
				}
			}
        });
    });
});
