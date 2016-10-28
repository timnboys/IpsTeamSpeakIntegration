function openInSameWindow(evt) {
	window.location = evt;
}

function ts3ssvconnect(id, channel) {
	var id = "ts3ssv-" + id;
	var hostport = document.getElementById(id + "-hostport").value;
	var nickname = document.getElementById(id + "-nickname");
	var command = "ts3server://" + hostport.replace(":", "?port=");
	var dateExpire = new Date;

	dateExpire.setMonth(dateExpire.getMonth() + 1);

	if (channel != null) {
		command += "&cid=" + channel;
	}

	openInSameWindow(command);
}