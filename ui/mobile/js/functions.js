$(document).on('mobileinit', function(){
	$.mobile.defaultPageTransition = 'none';

});

$(document).on('pageshow', function(){
	$('.profile').on('vclick', function(){
		$(this).find('.dropdown').toggle();
	});
		
	 $(document).on('vclick', '.daily-entry-page ul li .title h5', function(){
		 	$(this).parents('li').find('.arrow').toggleClass('opened');
		  	$(this).parents('li').find('.holder').stop(true , true).slideToggle(function(){ $(this).trigger('updateLayout');});
//		$(this).parents('li').find('.ui-btn').stop(true , true).slideToggle(function(){  });
	});
	
	$('.expand').click(function () { 
		$('.daily-entry-page ul li .arrow').addClass('opened');
		$('.daily-entry-page ul li .holder').slideDown();
//		$('.daily-entry-page ul li .ui-btn').slideDown();
	});
	
 	 $('.goal-detail-page ul li .inner').each(function () { 
 	 	console.log($(this).find('p').height());
 	 	if ($(this).find('p').height() > 40) {
 	 		$(this).parent().find('.more').show();
 	 	} else {
 	 		$(this).parent().find('.more').hide();
 	 	};
 	  });
});

$(function() {

	$('.jqtransform').jqTransform();

       
	 $(document).on('vclick', '.goal-detail-page ul li .more', function(){
 	   		if ($(this).parent().find('.inner').hasClass('opened')) {
 	 	   		var height = 40;
 	 	   		$(this).parent().find('.inner').removeClass('opened');
   			
 	   		} else {
	 	   		var height = $(this).parent().find('.inner p').height();
 	 	   		$(this).parent().find('.inner').addClass('opened');
 	   		};
 	   		
 	   		$(this).parent().find('.inner').animate({
 	   			'height' : height
 	   		});
 	   		return false;
 	 	});
 	 	

 	 	
	 $(document).on('vclick', '.friends-page .nav a', function(){
 	   		scrolltop($(this).attr('href'));
 	  	 	return false;
 	 	});
  
	$(document).scroll(function () { 
		var top = $(this).scrollTop();
		if (top > 122) {
			$('.friends-page .nav').addClass('fixed');
		} else {
			$('.friends-page .nav').removeClass('fixed');
		};
	 });
        
});

function scrolltop(id) {
	
	if ($(id).length) {
		var position = $(id).offset().top;
		if (position != null) {
			$('html , body').animate({
				scrollTop: position - 85
			});
		};
	};
}