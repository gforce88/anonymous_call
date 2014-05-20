function submiteAnonCall(formId) {
	var data = {};
	var arr = $(formId).serializeArray();

	$.each(arr, function() {
		if (data[this.name] !== undefined) {
			if (!data[this.name].push) {
				data[this.name] = [ data[this.name] ];
			}
			data[this.name].push(this.value || '');
		} else {
			data[this.name] = this.value || '';
		}
	});

	$.post($(formId).attr("action"), data, function(result) {
		if (result.redirect == true) {
			window.location.replace(result.url);
		} else {
			$(result.validFields).each(function(index, element) {
				$("#" + element).attr("style", "display:none");
			});
			$(result.invalidFields).each(function(index, element) {
				$("#" + element).attr("style", "display:block");
			});
		}
	});
}

function inputCheck(keyWords, evt) {
	var theEvent = evt || window.event;
	var key = theEvent.keyCode || theEvent.which;
	var keyStr = String.fromCharCode(key);
	var regex = new RegExp(keyWords);
	if (!regex.test(keyStr) && key != 8 && key != 46 && key != 37 && key != 39) {
		theEvent.returnValue = false;
		if (theEvent.preventDefault)
			theEvent.preventDefault();
	}
}

function timestamp2His(totalTime) {
	second = totalTime % 60;
	totalMinute = (totalTime - second) / 60;
	minute = totalMinute % 60;
	hour = (totalTime - minute * 60 - second) / 3600;
	if (second < 10)
		second = "0" + second;
	if (minute < 10)
		minute = "0" + minute;
	if (hour < 10)
		hour = "0" + hour;
	return hour + ":" + minute + ":" + second;
}

function checkAgree(agreeCheckbox, agreeButton) {
	if ($(agreeCheckbox).is(':checked')) {
		$(agreeButton).removeAttr("disabled");
	} else {
		$(agreeButton).attr("disabled", true);
	}
}
