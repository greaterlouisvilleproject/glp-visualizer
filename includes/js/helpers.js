function resizeFrame(obj) {
	// Only works with same-site attribute & will require self-hosted Shiny instance
	obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
}
