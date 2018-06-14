function checksearch(event, obj) {
	if (event.keyCode == 13) {
		location.href = "./?a=search&search=" + obj.value;
	}
}
