/**
 * @author davidhuang
 */
var actionName = '__ACTION__';
$(document).ready(function(){
	var curFileNum = 0;
	var rowsnum = 0; //记录行数
	var rindex = ""; //列索引
	var tbl = document.getElementById('tbl');
	
	var status;
	addRow();
	//编辑时显示删除按钮
	if(window.location.href.indexOf("staffID") != -1){
		$("#removeStaffBtn").show();
		status = "edit";
		$("#staffName").attr("readonly",true);
	}else{
		status = "add";
	}
	//保存
	$("#saveStaffBtn").click(function(){
		var form = $('#staffInfoForm')[0];
		//if(!$(form.name).validatebox('isValid') || !$(form.phone).validatebox('isValid')) {
		if(!$('#staffInfoForm').form("validate")) {
			alert('請填寫必須的信息');
			return;
		}
		var data = $(form).serialize();
		if(status == "add"){
			$.post('{:U("Staff/addStaff")}',data,ajaxHandler,"json");
		}else if(status == "edit"){
			$.post('{:U("Staff/saveStaff")}',data,ajaxHandler,"json");
		}
		
	});
	function ajaxHandler(response){
		alert(response.info);
		if(response.status == 1)
			window.location.href = '{:U("/Admin/Staff")}';
	}
	$("#removeStaffBtn").click(function(){
		$.post('{:U("Staff/removeStaff")}',"staffID="+$("#staffID").val(),ajaxHandler,"json");
	});
    //封面图片切换
    $("input[name=showWeb]").live("click",function(){
    	$("#imgShowIndex").val(this.selectedIndex);
    });
    //删除上传图片
    $("#thumbList .delImg").live("click",function(){
    	var data = {};
    	var imgSrc = $(this).prev().attr("src");// 格式：/Public/Uploads/XX.JPG
    	data.thumbUrl = imgSrc.substr(1);// 去掉首个“/” 带个/表示的是web地址？在action中删除操作的地址是不能带/的
    	data.index = $(this).parent().index();
    	$.post('{:U("Staff/removeThumb")}',{json:$.toJSON(data)},removeThumbHandler,"json");
    });
    function removeThumbHandler(response){
    	//删除Li图片
    	$("#thumbList li").eq(data.index).remove();
    	//重置上传图片地址 INPUT
    	$("#uploadImages").val("");
    	var images = "";
    	$("#thumbList li").each(function(){
    		images += $(this).find(".uploadImg").eq(0).attr("src") + ",";
    	});
    	images = images.substr(0,1);
    	$("#uploadImages").val(images);
    }
    //新增联系方式
	$("#addContactImg").click(function(){
		var contactHtml = '<li ><select class="contactTypeSel">' +
								'<option selected="true">电话</option>' + 
								'<option>EMail</option>' +
							'</select>' +
		'<input type="text" class="easyui-validatebox" validType="number"  required="true" />' +
		"<img class='delImg' src='/Public/style/img/delete.png' /></li>";
    	$("#contactUl").append(contactHtml);
    	$("#contactUl input[validType='number']").last().validatebox({
			required:true
		});	        	
	})
	//删除联系方式
	$("#contactUl .delImg").live("click",function(e){
		var i = $(this).parent().index();
		$("#contactUl li").eq(i).find("input").validatebox("destroy");//删除验证销毁组件
		$("#contactUl li").eq(i).remove();
	})
	//更改联系方式
	$(".contactTypeSel").live("change",function(e){
		var selectedIndex = e.currentTarget.selectedIndex;
		var input = $(this).next();
		switch(selectedIndex){
			case 0:
				input.attr("validType","number");
				input.validatebox('remove');
				input.validatebox({
					required:true
				});
				break;
			case 1:
				input.attr("validType","email");
				input.validatebox('remove');
				input.validatebox({
					required:true,
					validType:"email"
				})
				break;
		}
	});
	//增加上传附件
	$("#addUploadBtn").click(function(){
		addRow();
	})
	$("#uploadBtn").click(function(){
		uploading();
	})
	
	//ajax回调
	function uploadComplete(name){
		var oldImgName = $("#uploadImages").val();
		$("#uploadImages").val(oldImgName+","+name);
		var tmp = name.split(",");
		var ele = "";
		$.each(tmp,function(i){
			ele += "<li class='smallThumb'><input class='checkShow' name='showWeb' type='radio'/>"
			 + "<img  src='/Public/Uploads/" + this + "' class='uploadImg'/>" 
			 + "<img class='delImg' src='/Public/style/img/delete.png' />";
	    }) 
		$("#thumbList ul").append(ele);
	}
	//增加上传行数
	function addRow(){
		if(curFileNum>2) return;
	    curFileNum++;
	    rowsnum++;
	    var row = tbl.insertRow(-1);
	    //var td = arow.insertCell();
	    var cell = document.createElement("td");
	    cell.innerHTML = '<div class="impBtn  fLeft" ><input type="file" id="file' + curFileNum + '" name="file' + curFileNum + '" class="file  huge"></div><div class="fLeft hMargin"><img src="../../public/style/img/del.gif"  style="cursor:hand" onfocus="javascript:getObject(this)" onclick="deleteRow();" width="20" height="20" border="0" alt="删除" align="absmiddle"></div> ';
	    cell.align = "center";
	    row.appendChild(cell);
	}
	
	//上传
	function uploading(){
		if($("#tbl").find("input[type=file]").length == 0) return;
	    $('#result').css("display",'block');
	    $('#result').html('<img src="../../public/style/img/ajaxloading.gif" width="16" height="16" border="0" alt="" align="absmiddle"> 文件上传中～');
	    $('#upload')[0].submit();          
	}
	//删除上传行数
	function deleteRow(){
	    if (tbl.rows.length > 0) {
	        tbl.deleteRow(rindex); //删除当前行
	        rowsnum--;
	    }
	    else {
	        return;
	    }
	    rindex = "";
	}
});
