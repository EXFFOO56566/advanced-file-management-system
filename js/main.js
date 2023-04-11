// JavaScript Document
document.createElement("nav");
document.createElement("header");
document.createElement("footer");
document.createElement("section");
document.createElement("article");
document.createElement("aside");
document.createElement("hgroup");


$(document).ready(function(){
	

	
	//radio buttons
		$('.boxRegis input[type=radio], #order input[type=radio]').each(function(){
			if($(this).is(':checked'))
				$(this).wrap('<span class="radio checked"></span>');
			else
				$(this).wrap('<span class="radio"></span>');
		});
		$('span.radio').click(function(){
			$('input[name="'+$(this).find('input').attr('name')+'"]').each(function(){
				$(this).removeAttr('checked');
				$(this).parent().removeClass('checked');
			});
			$(this).find('input').attr('checked','checked');
			$(this).addClass('checked');
		});

	 /*hover option list our key services*/
	$('.listOptions li a').hover(
	function () {
		
		$('.listOptions li a').find('span').removeClass('active');
		$(this).find('span').addClass('active');
	},
	function () {
		$('.listOptions li a').find('span').removeClass('active');
	}
	);	
	/*hover option list our key services*/
	/*hover circles*/
	$('.step1, .step2, .step3, .step4').hover(
	function () {
		$(this).css({'background-position':'-202px 7px'});	
	},
	function () {
		$(this).css({'background-position':'19px  6px'});
	}
	);
	/*hover circles*/	
	
	
	/*slider quote*/
	var currentImageQuote=0;
	$('.sliderQuote > ul li:eq(0)').css({display:'block'});
	
	$('.arrowLeftQuote').addClass('inactive').click(function(){
		
		countImagesQuote=$('.sliderQuote > ul li').length-1;
		$('.sliderQuote > ul li:eq('+ currentImageQuote +')').fadeOut(600);
		currentImageQuote--;
		$('.circles > span').removeClass('active');
		if(currentImageQuote<0)
			currentImageQuote=countImagesQuote;			
		
		$('.sliderQuote > ul li:eq('+ currentImageQuote +')').fadeIn(600);
			
		$('.arrowLeftQuote, .arrowRightQuote').removeClass('inactive');
		if(currentImageQuote==0)
			$(this).addClass('inactive');
		
	});
	
	
	$('.arrowRightQuote').click(function(){
		countImagesQuote=$('.sliderQuote > ul li').length-1;
		
		
		$('.sliderQuote > ul li:eq('+ currentImageQuote +')').fadeOut(600);
		currentImageQuote+=1;
	
		if(currentImageQuote>countImagesQuote)
		{
			currentImageQuote=0;
		}
		
        $('.sliderQuote > ul li:eq('+ currentImageQuote +')').fadeIn(600);
		
		$('.arrowLeftQuote, .arrowRightQuote').removeClass('inactive');
		if(currentImageQuote==countImagesQuote)
			$(this).addClass('inactive');
	});
	/*slider quote*/

	
	/*validate Form Contact*/
	
	$('#subContact').click(function(e){
		e.preventDefault();
		var error='';
		$('#formContact .contentInput .tooltip').remove();
		$('#formContact .contentInput input[type="text"]').css({border:'2px solid #CECECE'});	
		
		
		var validateEmail=false;
		var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
			if(reg.test($('#email').val()) == false && $('#email').val().length!=0)
			{
				var parentThis=$('#email').parents('.contentInput');
				$('.contentToolTip .tooltip').clone().appendTo(parentThis);
				parentThis.find('.tooltip').css({ top:'5px', left:'322px' });
				parentThis.find('.tooltip > .restTool').html($('#email').attr('data-validate'));
				$('#email').css({ border:'2px solid #6E9DE5 '});
				error='error';
				validateEmail=true;
			}
		
		
		$('#formContact input[type="text"]').each(function(){
			
			if($(this).attr('name')=='name' || 	$(this).attr('name')=='subject')
			{
				if($(this).val().length==0)
				{
					var parentThis=$(this).parents('.contentInput');
					$('.contentToolTip .tooltip').clone().appendTo(parentThis);
					parentThis.find('.tooltip').css({ top:'5px', left:'322px' });
					parentThis.find('.tooltip > .restTool').html($(this).attr('data-value'));
					$(this).css({ border:'2px solid #6E9DE5 '});
					error='error';
				}
			}
			if($(this).attr('name')=='email' && validateEmail==false  )
			{
				if($(this).val().length==0)
				{
					var parentThis=$(this).parents('.contentInput');
					$('.contentToolTip .tooltip').clone().appendTo(parentThis);
					parentThis.find('.tooltip').css({ top:'5px', left:'322px' });
					parentThis.find('.tooltip > .restTool').html($(this).attr('data-value'));
					$(this).css({ border:'2px solid #6E9DE5 '});					
					error='error';
				}
			}
		});
		
		if(error!='')
			{
			 return false;
			}
		else
		 $('#formContact').submit();
		
	});
	/*validate Form Contact*/
	
	
	
	
	/*validate Form Register*/
	
	$('#subRegister').click(function(e){
		e.preventDefault();
		var error='';
		$('#formRegister .contentInput .tooltip').remove();
		$('#formRegister .contentInput input[type="text"]').css({border:'2px solid #CECECE'});	
		
		
		var validateEmail=false;
		var pwd=false;
		var cpwd=false;		
		var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
			if(reg.test($('#email').val()) == false && $('#email').val().length!=0)
			{
				var parentThis=$('#email').parents('.contentInput');
				$('.contentToolTip .tooltip').clone().appendTo(parentThis);
				parentThis.find('.tooltip').css({ top:'5px', left:'322px' });
				parentThis.find('.tooltip > .restTool').html($('#email').attr('data-validate'));
				$('#email').css({ border:'2px solid #6E9DE5 '});
				error='error';
				validateEmail=true;
			}
			if($('#pwd').val().length>0 && $('#pwd').val().length<6 )
			{
				var parentThis=$('#pwd').parents('.contentInput');
				$('.contentToolTip .tooltip').clone().appendTo(parentThis);
				parentThis.find('.tooltip').css({ top:'5px', left:'322px' });
				parentThis.find('.tooltip > .restTool').html($('#pwd').attr('data-validate'));
				$(this).css({ border:'2px solid #6E9DE5 '});
				error='error';
				pwd=true;
				
			}
			if($('#cpwd').val().length>0 && $('#cpwd').val().length<6  )
			{
				var parentThis=$('#cpwd').parents('.contentInput');
				$('.contentToolTip .tooltip').clone().appendTo(parentThis);
				parentThis.find('.tooltip').css({ top:'5px', left:'322px' });
				parentThis.find('.tooltip > .restTool').html($('#cpwd').attr('data-validate'));
				$(this).css({ border:'2px solid #6E9DE5 '});
				error='error';
				cpwd=true;
				
			}
		
			if($('#cpwd').val().length==0 && cpwd==false)
			{
				var parentThis=$('#cpwd').parents('.contentInput');
				$('.contentToolTip .tooltip').clone().appendTo(parentThis);
				parentThis.find('.tooltip').css({ top:'5px', left:'322px' });
				parentThis.find('.tooltip > .restTool').html($('#cpwd').attr('data-value'));
				$(this).css({ border:'2px solid #6E9DE5 '});
				error='error';
			}
			
			if($('#pwd').val().length==0 && pwd==false)
			{
				var parentThis=$('#pwd').parents('.contentInput');
				$('.contentToolTip .tooltip').clone().appendTo(parentThis);
				parentThis.find('.tooltip').css({ top:'5px', left:'322px' });
				parentThis.find('.tooltip > .restTool').html($('#pwd').attr('data-value'));
				$(this).css({ border:'2px solid #6E9DE5 '});
				error='error';
			}
			
		
		$('#formRegister input[type="text"]').each(function(){
			
			if($(this).attr('name')!='email')
			{
				if($(this).val().length==0)
				{
					var parentThis=$(this).parents('.contentInput');
					$('.contentToolTip .tooltip').clone().appendTo(parentThis);
					parentThis.find('.tooltip').css({ top:'5px', left:'322px' });
					parentThis.find('.tooltip > .restTool').html($(this).attr('data-value'));
					$(this).css({ border:'2px solid #6E9DE5 '});
					error='error';
				}
			}
			if($(this).attr('name')=='email' && validateEmail==false  )
			{
				if($(this).val().length==0)
				{
					var parentThis=$(this).parents('.contentInput');
					$('.contentToolTip .tooltip').clone().appendTo(parentThis);
					parentThis.find('.tooltip').css({ top:'5px', left:'322px' });
					parentThis.find('.tooltip > .restTool').html($(this).attr('data-value'));
					$(this).css({ border:'2px solid #6E9DE5 '});					
					error='error';
				}
			}
		});
		
		if(error!='')
			{
			 return false;
			}
		else
		 $('#formContact').submit();
		
	});
	/*validate Form Register*/
	
});


$(window).load(function(e) {
	var currentImage=0;
	
	
	$('.slider > ul li:eq(0)').css({display:'block'});
	
	galleryInterval=setInterval(function (){
		ImagesRight();
	},5000);
	
	$('.arrowLeftSlider').click (function (){		  
			clearInterval(galleryInterval);
			galleryInterval=null;
			ImagesLeft();								  
			
	});
	   
	$('.arrowRightSlider').click (function (){
			clearInterval(galleryInterval);	
			galleryInterval=null;							  
			ImagesRight();			
	});
	  
	  
	  
	function ImagesLeft	()
	{
		countImages=$('.slider > ul li').length-1;
		$('.slider > ul li:eq('+ currentImage +')').fadeOut(600);
		currentImage--;
		$('.circles > span').removeClass('active');
		if(currentImage<0)
			currentImage=countImages;
		
		$('.slider > ul li:eq('+ currentImage +')').fadeIn(600);
		$('.circles > span:eq('+ currentImage +')').addClass('active');
	}
	
	function ImagesRight()
	{
		countImages=$('.slider > ul li').length-1;
		
		
		$('.slider > ul li:eq('+ currentImage +')').fadeOut(600);
		currentImage+=1;
		$('.circles > span').removeClass('active');
		if(currentImage>countImages)
		{
			currentImage=0;
		}
		
		$('.slider > ul li:eq('+ currentImage +')').fadeIn(600);
		$('.circles > span:eq('+ currentImage +')').addClass('active');
	}
	
	var containThum='';
	
	countImages=$('.slider > ul li').length-1;
	for(i=1; i<=(countImages+1); i++)
	{
		if(i==1)
			adclass='class="active"';
		else
			adclass="";
		containThum+=	'<span '+adclass+'></span>';
	
	}
	$('.circles').css({width:((countImages+1)*24)});
	$('.circles').append(containThum);
	
	
	$('.circles span').on("click", function()
	{
		clearInterval(galleryInterval);	
		$('.slider > ul li:eq('+ currentImage +')').fadeOut();
		$('.circles > span').removeClass('active');
		currentImage=$(this).index();
		$('.slider > ul li:eq('+ currentImage +')').fadeIn();	
		$('.circles > span:eq('+ currentImage +')').addClass('active');
		
	});
 });
 

function getDateFormat(date_value){
 							var date = new Date(date_value);
						    var day = date.getDate();
						    var mon = date.getMonth() + 1; 
						    if(mon==1){
						    	var month= "Jan";
						    }else if(mon==2){
						    	var month= "Feb";
						    }else if(mon==3){
						    	var month= "Mar";
						    }else if(mon==4){
						    	var month= "Apr";
						    }else if(mon==5){
						    	var month= "May";
						    }else if(mon==6){
						    	var month= "Jun";
						    }else if(mon==7){
						    	var month= "Jul";
						    }else if(mon==8){
						    	var month= "Aug";
						    }else if(mon==9){
						    	var month= "Sep";
						    }else if(mon==10){
						    	var month= "Oct";
						    }else if(mon==11){
						    	var month= "Nov";
						    }else if(mon==12){
						    	var month= "Dec";
						    }
						    var year = date.getFullYear();
						    var hr = date.getHours();
						    var min = date.getMinutes();
						    if(min==1){
						    	var minute = "01";
						    }else if(min==2){
						    	var minute = "02";
						    }else if(min==3){
						    	var minute = "03";
						    }else if(min==4){
						    	var minute = "04";
						    }else if(min==5){
						    	var minute = "05";
						    }else if(min==6){
						    	var minute = "06";
						    }else if(min==7){
						    	var minute = "07";
						    }else if(min==8){
						    	var minute = "08";
						    }else if(min==9){
						    	var minute = "09";
						    }else{
						    	var minute= date.getMinutes();
						    }

						    var date_value= day + " " + month + " " + year +", " + hr +":"+minute;
						   return date_value;
 						}