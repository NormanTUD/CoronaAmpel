jQuery(document).ready(function($) {
	function sticky(tablehead) {
		var window_top = $(window).scrollTop();
		var top_position = $('body').offset().top;
		var element_to_stick = $('#' + tablehead);
		if(element_to_stick) {
			if (window_top > top_position) {
				element_to_stick.addClass('sticky');
			} else {
				element_to_stick.removeClass('sticky');
			}
		}
	}

	function sticky_wartungstabelle_head () {
		sticky("wartungstabelle_thead");
	}

	$(window).scroll(sticky_wartungstabelle_head);
});
