$(document).bind("mobileinit", function(){
	$.support.cors = true;
	$.mobile.allowCrossDomainPages = true;
	$.mobile.defaultPageTransition = 'flip';
	$.mobile.defaultDialogTransition = 'pop';
	//$.mobile.page.prototype.options.addBackBtn = true;
});