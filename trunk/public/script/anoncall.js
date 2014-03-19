function submiteInvitation() {
	$("#invitationFrom").ajaxSubmit(function(result) {
		if (result.success == true) {
			window.location.replace(result.url);
		} else {
			$(result.validFields).each(function(index, element){
				$("#"+element).attr("style", "display:none");
			});$(result.invalidFields).each(function(index, element){
				$("#"+element).attr("style", "display:block");
			});
		}
	});
}

function toglePayType(payType) {
	if (payType == 0) {
		$("#selfPayInfo").attr("style", "display:none");
	} else {
		$("#selfPayInfo").attr("style", "display:block");
	}
}

function inputCheck(keyWords, evt) {
	var theEvent = evt || window.event;
	var key = theEvent.keyCode || theEvent.which;
	key = String.fromCharCode(key);
	var regex = new RegExp(keyWords);
	if (!regex.test(key)) {
		theEvent.returnValue = false;
		if (theEvent.preventDefault)
			theEvent.preventDefault();
	}
}