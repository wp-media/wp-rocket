(function($) {

$('.log-modal').dialog({
	title: action_scheduler_str.title,
	dialogClass: 'wp-dialog',
	autoOpen: false,
	draggable: false,
	width: 'auto',
	modal: true,
	resizable: false,
	closeOnEscape: true,
	position: {
		my: "center",
		at: "center",
		of: window
	},
	open: function () {
		// close dialog by clicking the overlay behind it
		$('.ui-widget-overlay').bind('click', function(){
			$('.log-modal').dialog('close');
		})
	},
	create: function () {
		// style fix for WordPress admin
		$('.ui-dialog-titlebar-close').addClass('ui-button');
	},
});

$('a.log-modal-open').click(function(e) {
	e.preventDefault();
	$('#log-' + $(this).data('log-id')).dialog('open');
});

})(jQuery);
