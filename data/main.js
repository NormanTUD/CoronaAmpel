function reset_comment_box_size (element) {
	element.style.width = '80px';
}

function resizable (el, factor) {
	var int = Number(factor) || 7.7;
	function resize() {
		el.style.width = ((el.value.length + 1) * int) + 'px';
	}
	var e = 'keyup,keypress,focus,blur,change'.split(',');
	for (var i in e) el.addEventListener(e[i],resize,false);
	resize();
}

function resize_comment_box (box) {
	resizable(box, 10);
}

$(function() {
	$('.datepicker').datepicker({
		prevText: '&#x3c;zurück', prevStatus: '',
			prevJumpText: '&#x3c;&#x3c;', prevJumpStatus: '',
			nextText: 'Vor&#x3e;', nextStatus: '',
			nextJumpText: '&#x3e;&#x3e;', nextJumpStatus: '',
			currentText: 'heute', currentStatus: '',
			todayText: 'heute', todayStatus: '',
			clearText: '-', clearStatus: '',
			closeText: 'schließen', closeStatus: '',
			monthNames: ['Januar','Februar','März','April','Mai','Juni',
			'Juli','August','September','Oktober','November','Dezember'],
			monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun',
			'Jul','Aug','Sep','Okt','Nov','Dez'],
			dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
			dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
			dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
			showMonthAfterYear: false,
			showOn: 'both',
			buttonImageOnly: false,
			dateFormat:'dd.mm.yy'
	});

	$('.monthpicker').datepicker( {
		changeMonth: true,
		changeYear: true,
		showButtonPanel: true,
		dateFormat: 'yy-mm',
		onClose: function(dateText, inst) { 
			$(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
		}
	});
});
