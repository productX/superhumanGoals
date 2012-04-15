$(document).on('mobileinit', function(){
	$.mobile.defaultPageTransition = 'none';
});

$(document).on('pageshow', function(){
	// expand a row on daily entry page if clicked
	/*$(document).on('vclick', '.daily-entry-page ul li .title h5', function(){
		$(this).parents('li').find('.arrow').toggleClass('opened');
		$(this).parents('li').find('.holder').stop(true , true).slideToggle(function(){ $(this).trigger('updateLayout');});
	});*/
	
	// expand all rows on daily entry page
	/*$(document).on('vclick', '.expand', function(){
		$('.daily-entry-page ul li .arrow').addClass('opened');
		$('.daily-entry-page ul li .holder').slideDown();
	});*/
	
	// expand tactics or todos on the goal detail page
	$('.goal-detail-page ul li .inner').each(function () { 
		console.log($(this).find('p').height());
		if ($(this).find('p').height() > 40) {
			$(this).parent().find('.more').show();
		}
		else {
			$(this).parent().find('.more').hide();
		};
	});
});

$(function() {
	 $(document).on('vclick', '.goal-detail-page ul li .more', function(){
		if ($(this).parent().find('.inner').hasClass('opened')) {
			var height = 40;
			$(this).parent().find('.inner').removeClass('opened');
		}
		else {
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