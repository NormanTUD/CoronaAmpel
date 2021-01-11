$(document).ready(function(){
	
	function show_info (message, time) {
		if($('.info').is(':visible')) {
			$("<div class='info'>" + message + "</div>").appendTo("body").delay(time).hide(0);
		} else {
			$("<div class='info'>" + message + "</div>").appendTo("body").delay(time).hide(0);
		}

	}

	function do_submit (this_form) {
		var loc = window.location.pathname;
		var dir = loc.substring(0, loc.lastIndexOf('/'));
		var submitfile = dir + '/submit.php';
		$.ajax({
			url : submitfile,
			type: "POST",
			data: $(this_form).serialize(),
			success: function (data) {
				show_info(data, 2000);
			},
			error: function (jXHR, textStatus, errorThrown) {
				show_info(errorThrown, 10000);
			}
		});
	}

	$(".form_autosubmit, :input").each(function (index) {
		if(!$(this).attr('noautosubmit')) {
			$(this).change(function (index) {
				var do_continue = 1;

				if($(this).attr("autosubmitwarning")) {
					swal({
						title: $(this).attr("autosubmitwarning"),
						text: "Das lässt sich nicht mehr rückgängig machen!",
						type: "warning",
						showCancelButton: true,
						buttons: ['Ja', 'Abbrechen'],
						closeOnConfirm: false,
						dangerMode: true
						}
					).then((value) =>  {
						if(!value) {
							do_submit(this.form);
						} else {
							swal({
								title: "Gut, ich hab lieber nix gemacht",
								type: "success"
							});
							if($(this).attr("resetdefault")) {
								$(this).val(($(this).attr("resetdefault")));
							}
						}

					});
				} else {
					do_submit(this.form);
				}
			});
		}
	});
});
