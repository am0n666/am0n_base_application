function link_disabling_fix(links_class_name, styles) {
	var all_links = document.querySelectorAll('.' + links_class_name);

	var i;
	for (i = 0; i < all_links.length; i++) {
		let link = all_links[i];

		if (link.tagName === 'A')
		{
			let is_disabled = link.hasAttribute('disabled');
			if (is_disabled) {
				link.setAttribute('style', styles);
				link.href = "javascript:void(0)";
			}
		}
	}
}

link_disabling_fix('navbar_link', 'cursor: not-allowed; opacity: 0.5;');
link_disabling_fix('fb_button', 'background-color:  var(--disabled-button-background); color:  var(--disabled-button-text); transform: scale(1); cursor: not-allowed;');
