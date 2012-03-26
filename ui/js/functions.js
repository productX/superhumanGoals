$(document).ready(function() {
	
	$('.field, textarea').focus(function() {
        if(this.title==this.value) {
            this.value = '';
        }
    }).blur(function(){
        if(this.value=='') {
            this.value = this.title;
        }
    });
    
    $('#header .user-image').hover(function(){
		$(this).find('.dd:eq(0)').stop(true, true).slideToggle();
		}, function(){
			$(this).find('.dd:eq(0)').stop(true, true).slideToggle();
		}
	);
    
    $("#search input.field").focus(function () {
        $(this).next('.complete-dd').stop(true, true).slideToggle();
    }).blur(function(){
        $(this).next('.complete-dd').stop(true, true).slideToggle();
    });
    
    /* CheckBox */
	
	$(".tests .row input[type=checkbox]").change(function() {
		var checked = $(this).is(":checked");
		if( checked )
			$(this).parent().addClass("checked");
		else
			$(this).parent().removeClass("checked");
	}).change();
	
	if( $.browser.msie ) {
		$(".tests .row input[type='checkbox']").parent().click(function() {
			var input = $("input[type='checkbox']", this);
			var value = input.is(":checked");
			
			if(value) input.attr("checked", false);
			else input.attr("checked", "checked");
			
			input.trigger('change');
		});
	}
		
	/* End CheckBox */
	
	$('#file').customFileInput();
	
	$('.box .add').click(function () {
		var added_div = $(this).parents('.box').find('.dd-row');
		if( added_div.css('display') == 'none' ){
			added_div.show();
		}else {
			added_div.hide();
		}
		return false;
	});
	
	$('.signup-box a.signup-btn').click(function(){
		$(this).slideUp();
		$(this).next().slideDown();
	});
    
});

function autoHeightContainer() {
	var containerHeight = $(window).height() - 111 -$('.head').height() - $('#footer').height();
	var heightDefault = $('.scrollarea').height();
	
    if ( heightDefault < containerHeight ) {
	    $('.scrollarea').css({ height: containerHeight});
	    $('.scrollarea').jScrollPane({
			showArrows: false,
			verticalDragMinHeight: 165,
			verticalDragMaxHeight: 165,
			animateScroll: true,
			autoReinitialise: true
		});
	} else {
	    $('.scrollarea').css({ height: heightDefault});
	    $('.scrollarea').jScrollPane();
	}
	$(window).bind('resize', function(){
		containerHeight = $(window).height() - 111 -$('.head').height() - $('#footer').height();
		if ( heightDefault < containerHeight ) {
	    $('.scrollarea').css({ height: containerHeight});
	    $('.scrollarea').jScrollPane({
			showArrows: false,
			verticalDragMinHeight: 165,
			verticalDragMaxHeight: 165,
			animateScroll: true,
			autoReinitialise: true
		});
	} else {
	    $('.scrollarea').css({ height: heightDefault});
	    $('.scrollarea').jScrollPane();
	}
	});
}