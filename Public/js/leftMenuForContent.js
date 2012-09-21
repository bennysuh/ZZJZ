$(document).ready(function(){
    $(".menuItems li,.navList li").click(function(){
		var url = $(this).attr('url');
		$.post(root + "/Admin/Auth/checkAuthByUrl', {url:url}, checkAuthByUrlHandler, 'json');
	    function checkAuthByUrlHandler(response) {
			if(response.info == 'expiry') {
				window.location.href = root + "/Admin/Auth/";
			} else if (response.info) {
				window.location.href = url;
			}else {
				alert('對不起，你沒有權限使用此功能!');
			}		
		}
		
	});

	$(".menuItems li").each(function(){
		var url = $(this).attr('url');
		if (url == actionName) {
			$(this).addClass('select');
		}
	});

})