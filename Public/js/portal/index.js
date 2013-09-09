$(document).ready(function(){
	// getNotices();
	// getPageContent();
	// getBibles();
	function getPageContent(){
		var page = "index";
		var url = "admin/service/pageContentService.php";
		var postData = {"action":"query","page":page};
		$.post(url,postData,function(data){
		console.log(data);
			var jsonobj = JSON.parse(data);
			$("#mainContent").html(jsonobj[0].content);
			$("#loadInfo").hide();
		});
	}
	function getNotices(){
		var url = "admin/service/noticeService.php";
		var postData = {"action":"queryByPage","startPage":0,"pageSize":5};
		$.post(url,postData,function(data){
		console.log(data);
			var jsonobj = JSON.parse(data);
			var notice;
			$.each(jsonobj,function(){
				notice = this;
				$("<li><a href='notice/notice.php?id=" + notice.id + "' title='"+ notice.title + "' id='notice_'" + notice.id + "'>" + notice.title + "</a></li>").appendTo($("#scrollNewsUL"));
			});
			$("#loadNotice").hide();
			/*********文字翻屏滚动***************/
			new Marquee(["scrollNews","scrollNewsUL"],0,1,200,100,20,4000,1000);
		});
	}
	function getBibles(){
		var url = "admin/service/bibleService.php";
		var postData = {"action":"queryByPage","startPage":0,"pageSize":10};
		$.post(url,postData,function(data){
			var jsonobj = JSON.parse(data);
			var notice;
			$.each(jsonobj,function(){
				bible = this;
				$("<li><a href='bible/bible_content.php?id=" + bible.articleid + "' title='"+ bible.title + "' id='bible_'" + bible.articleid + "'>" + bible.title + "</a></li>").appendTo($("#scrollBiblesUL"));
			});
			$("#loadBible").hide();
			/*********文字翻屏滚动***************/
			new Marquee(["scrollBibles","scrollBiblesUL"],0,1,200,100,20,4000,1000);
		});
	}
});