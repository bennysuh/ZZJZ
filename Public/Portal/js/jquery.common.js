$(function(){
    $("input,textarea").focus(function(){
        $(this).addClass("focus");
    }).blur(function(){
        $(this).removeClass("focus");
    }); 
});
function showError(id, msg)
{
	$("#"+id).next(".error").remove();
	$("#"+id).after('<p class="error"><i></i>'+msg+'</p>');
}
function delError()
{
	$(".error").remove();
}
function getDBString(s){
	var a = "0"+ s;
	return a.slice(-2);
}
function getDateDiff(date,diffday){
	var d30 = 1000*60*60*24*30,
	time = date.split('-'),
	ms = +new Date(time[0],time[1]-1,time[2]),
	result = new Date(ms+d30);
	return  result.getFullYear()+'-'+(result.getMonth()+1)+'-'+result.getDate();
}
function getLogin(objName)
{
    $.ajax({
        type:"POST",
        url:"/ajax/getlogin.php",
        dataType:"json",
        success:function(msg)
        {
            if(msg.code == 0)
            {
                $("#alertlogin").replaceWith("<div id='alertlogin'>"+msg.data+"</div>");
                objName = new myPopup(".arrowbox2",".mask",".close_gray");
                objName.pup();
            }
        }
    });
}
