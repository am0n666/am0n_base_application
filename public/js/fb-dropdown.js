		function initFbComboboxes()
		{
			const FbComboboxes = document.querySelectorAll('.fb-dropdown');

			closeOptionsOnClickOutside(FbComboboxes);
			closeOptionsOnEscKey(FbComboboxes);

			FbComboboxes.forEach((FbCombobox) => {
				FbCombobox.addEventListener("click", (e) => {
					if (!FbCombobox.classList.contains('open')) {
						openFbCombobox(FbCombobox);
					}else{
						closeFbCombobox(FbCombobox);
					}
				});

				setDefaultValue(FbCombobox, FbCombobox.getAttribute("data-default-value"));

				var span_text = '-';

				const fb_options = FbCombobox.querySelector('.fb-options').querySelectorAll('.option');
				const span_ValueText = FbCombobox.querySelector('.main').querySelector('.value');

				for(var i = 0; i < fb_options.length; i++) {
					const fb_option = fb_options[i];
					const fb_option_label = fb_option.querySelector('label');
					const fb_option_input = fb_option.querySelector('input');

					if (fb_option_input.checked) {
						span_text = fb_option_label.innerText;
					}

					updateValueText(span_ValueText, span_text);

					fb_option.addEventListener('click', function (e) {
						this.querySelector('label').click;
					});

					fb_option_label.addEventListener('click', function (e) {
						fb_option_input.change;
						closeFbCombobox(FbCombobox);
					});

					fb_option_input.addEventListener('change', function (e) {
						span_text = fb_option_label.innerText;
						updateValueText(span_ValueText, span_text);
						closeFbCombobox(FbCombobox);
					}, false);
				}
			});
		}

		function setDefaultValue(FbCombobox, default_value)
		{
			if (default_value !== null) {
				const fb_options = FbCombobox.querySelector('.fb-options').querySelectorAll('.option');
				for(var i = 0; i < fb_options.length; i++) {
					const fb_option_input = fb_options[i].querySelector('input');
					if (fb_option_input.value == default_value) {
						fb_option_input.checked = true;
					}
				}
			}
		}

		function updateValueText(span_ValueText, newText)
		{
			span_ValueText.innerText = newText;
		}

		// Closing options when clicked outside
		function closeOptionsOnClickOutside(FbComboboxes)
		{
			FbComboboxes.forEach((FbCombobox) => {
				window.addEventListener("click", (e) => {
					if (!FbCombobox.contains(e.target)) {
						closeFbCombobox(FbCombobox);
					}
				});
			});

		}

		// Closing options when with esc key
		function closeOptionsOnEscKey(FbComboboxes)
		{
			document.addEventListener("keydown", (e) => {
				if (e.key === "Escape") {
					FbComboboxes.forEach((FbCombobox) => {
						closeFbCombobox(FbCombobox);
					});
				}
			});

		}

		const openFbCombobox = (FbCombobox) => FbCombobox.classList.add("open");
		const closeFbCombobox = (FbCombobox) => FbCombobox.classList.remove("open");

		initFbComboboxes();