(function($) {
    "use strict";
    window.resetForm=function(o){
        let f=$(o).closest("form");
        $(f).find("input[type!='hidden'][type!='reset']").val("").removeAttr("value");
        $(f).find("select>option:selected").removeAttr("selected");
        $(f).find("select").val('').removeAttr("value");
    };

    $('input[type="reset"],button[type="reset"]').click(function(){
        window.resetForm($(this)[0]);
    });

    //側邊選單路徑與瀏覽位置相同時啟動顯示
    var getUrl = window.location;
    var url = getUrl.href;
    var ori = getUrl.origin;
    var path = getUrl.pathname.split('/');
    path[2]=='submenus' ? path[2] = 'mainmenus' : '';
    var newPath = path[0] + '/' + path[1];
    var newUrl = ori + newPath;
    $('#sidebar').find('.active').removeClass('active');
    $('#kt_app_sidebar_menu').find('.active').removeClass('active');
    $('#kt_app_sidebar_menu').find('.here').removeClass('here');
    $('#kt_app_sidebar_menu').find('.show').removeClass('show');
    $('#sidebar a').each(function () {
        if (this.href == url || this.href == newUrl) {
            $(this).addClass('active');
            if ($(this).parents('ul').length == 2) {
                $(this).parent().parent().parent().addClass('menu-open');
                $(this).parent().parent().parent().children('a').addClass('active');
            }
        }
    });
    $('#kt_app_sidebar_menu a').each(function () {
        if (this.href == url || this.href == newUrl) {
            $(this).children('span').addClass('active');
            if ($(this).parents('div').length == 10) {
                $(this).parent().parent().parent().addClass('here');
                $(this).parent().parent().parent().addClass('show');
            }
        }
    });

    //上方選單路徑與瀏覽位置相同時啟動
    $('#topbar a').each(function () {
        if (this.href == url) {
            $(this).addClass('active');
            if ($(this).parents('ul').length == 2) {
                $(this).parent().parent().parent().children('a').addClass('active text-yellow');
            }
        }
    });

    //擴展全螢幕
    $("#fullscreen-button").on("click", function toggleFullScreen() {
		if ((document.fullScreenElement !== undefined && document.fullScreenElement === null) || (document.msFullscreenElement !== undefined && document.msFullscreenElement === null) || (document.mozFullScreen !== undefined && !document.mozFullScreen) || (document.webkitIsFullScreen !== undefined && !document.webkitIsFullScreen)) {
			if (document.documentElement.requestFullScreen) {
				document.documentElement.requestFullScreen();
			} else if (document.documentElement.mozRequestFullScreen) {
				document.documentElement.mozRequestFullScreen();
			} else if (document.documentElement.webkitRequestFullScreen) {
				document.documentElement.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
			} else if (document.documentElement.msRequestFullscreen) {
				document.documentElement.msRequestFullscreen();
			}
		} else {
			if (document.cancelFullScreen) {
				document.cancelFullScreen();
			} else if (document.mozCancelFullScreen) {
				document.mozCancelFullScreen();
			} else if (document.webkitCancelFullScreen) {
				document.webkitCancelFullScreen();
			} else if (document.msExitFullscreen) {
				document.msExitFullscreen();
			}
		}
    });
    var timeOut = 0;
    path[1] != 'shipping' ? timeOut = 5000 : timeOut = 3600000;
    //自動關閉警告訊息
    window.setTimeout(function() {
        $(".alert").fadeTo(500, 0).slideUp(500, function(){
            $(this).remove();
        });
    }, timeOut);
})(jQuery);

function getNowTime(){
    var timeDate= new Date();
    var tMonth = (timeDate.getMonth()+1) > 9 ? (timeDate.getMonth()+1) : '0'+(timeDate.getMonth()+1);
    var tDate = timeDate.getDate() > 9 ? timeDate.getDate() : '0'+timeDate.getDate();
    var tHours = timeDate.getHours() > 9 ? timeDate.getHours() : '0'+timeDate.getHours();
    var tMinutes = timeDate.getMinutes() > 9 ? timeDate.getMinutes() : '0'+timeDate.getMinutes();
    var tSeconds = timeDate.getSeconds() > 9 ? timeDate.getSeconds() : '0'+timeDate.getSeconds();
    return timeDate= timeDate.getFullYear()+'-'+ tMonth +'-'+ tDate +' '+ tHours +':'+ tMinutes +':'+ tSeconds;
}

function getNowDate(){
    var timeDate= new Date();
    var tMonth = (timeDate.getMonth()+1) > 9 ? (timeDate.getMonth()+1) : '0'+(timeDate.getMonth()+1);
    var tDate = timeDate.getDate() > 9 ? timeDate.getDate() : '0'+timeDate.getDate();
    return timeDate= timeDate.getFullYear()+'-'+ tMonth +'-'+ tDate;
}
