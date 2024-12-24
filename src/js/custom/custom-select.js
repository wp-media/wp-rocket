document.querySelectorAll(".custom-select").forEach(customSelect => {
	const selectBtn = customSelect.querySelector(".select-button");
	const selectedValue = customSelect.querySelector(".selected-value");
	const handler = function(elm) {
		const customChangeEvent = new CustomEvent('custom-select-change', {
			detail: {
				selectedOption: elm
			}
		});
		selectedValue.textContent = elm.textContent;
		customSelect.classList.remove("active");
		customSelect.dispatchEvent(customChangeEvent);

	}
	selectBtn.addEventListener("click", () => {
		customSelect.classList.toggle("active");
		selectBtn.setAttribute(
			"aria-expanded",
			selectBtn.getAttribute("aria-expanded") === "true" ? "false" : "true"
		);
	});

	customSelect.addEventListener('click', function(e) {
		if (e.target.matches('label')) {

			const allItems = customSelect.querySelectorAll('li');
			allItems.forEach(item => item.classList.remove('active'));
			const clickedPlan = e.target.closest('li');

			if (clickedPlan) {
				clickedPlan.classList.add('active');
				handler(clickedPlan);
			}
		}
	});
	document.addEventListener("click", (e) => {
		if (!customSelect.contains(e.target)) {
			customSelect.classList.remove("active");
			selectBtn.setAttribute("aria-expanded", "false");
		}
	});
});