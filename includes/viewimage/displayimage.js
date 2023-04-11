$(document).ready(function(){
		if(typeof jQuery.colorbox=="function"){
				$(".viewImage").colorbox({rel:false});
				$("#click").click(function(){ 
					$('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
					return false;
				});
        }
			});