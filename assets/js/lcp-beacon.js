function isIntersecting(rect) {
	return (
		rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
		rect.left <= (window.innerWidth || document.documentElement.clientWidth)
	);
}

function LCPCandidates(count) {
	const potentialCandidates = document.querySelectorAll(
		"img, video, picture, p, main, div"
	);
	const topCandidates = [];

	potentialCandidates.forEach(( element ) => {
		const rect = element.getBoundingClientRect();
		if (
			rect.width > 0 &&
			rect.height > 0 &&
			isIntersecting( rect )
		) {
			const visibleWidth = Math.min(rect.width, (window.innerWidth || document.documentElement.clientWidth) - rect.left);
			const visibleHeight = Math.min(rect.height, (window.innerHeight || document.documentElement.clientHeight) - rect.top);
			const area = visibleWidth * visibleHeight;
			const elementInfo = getElementInfo(element);
			if (elementInfo !== null) {
				// Insert element into topCandidates in descending order of area
				for (let i = 0; i < topCandidates.length; i++) {
					if (area > topCandidates[i].area) {
						topCandidates.splice(i, 0, {element, area, elementInfo});
						topCandidates.length = Math.min(
							count,
							topCandidates.length
						); // Keep only specified number of elements
						break;
					}
				}
				// If topCandidates is not full, append
				if (topCandidates.length < count) {
					topCandidates.push({element, area, elementInfo});
				}
			}
		}
	});

	return topCandidates.map((candidate) => ({
		element: candidate.element,
		elementInfo: getElementInfo(candidate.element),
	}));

}

function getElementInfo(element) {
	const nodeName = element.nodeName.toLowerCase();
	const element_info = {
		type: "",
		src: "",
		srcset: "",
		sizes: "",
		sources: [],
		bg_set: [],
		current_src: ""
	};

	const css_bg_url_rgx = /url\(\s*?['"]?\s*?(\S+?)\s*?["']?\s*?\)\s*?([a-zA-Z0-9\s]*[x|dpcm|dpi|dppx]?)/ig;

	if (nodeName === "img") {
		element_info.type = "img";
		element_info.src = element.src;
		element_info.current_src = element.currentSrc;
	} else if (nodeName === "video" && element.poster) {
		element_info.type = "img";
		element_info.src = element.poster;
		element_info.current_src = element.poster;
	} else {
		const computed_style = window.getComputedStyle(element, null);
		const bg_props = [
			computed_style.getPropertyValue("background-image"),
			getComputedStyle(element, ":after").getPropertyValue("background-image"),
			getComputedStyle(element, ":before").getPropertyValue("background-image")
		];

		const full_bg_prop = bg_props.filter(prop => prop !== "none").join("");
		element_info.type = "bg-img";
		if (full_bg_prop.includes("image-set(")) {
			element_info.type = "bg-img-set";
		}
		if (!full_bg_prop || full_bg_prop === "") {
			return null;
		}

		const matches = [...full_bg_prop.matchAll(css_bg_url_rgx)];
		console.log('matches : '+ JSON.stringify(matches));
		element_info.bg_set = matches.map(m => m[1] ? {src: m[1].trim() + (m[2] ? " " + m[2].trim() : "")} : {});		console.log('element_info.bg_set : '+ JSON.stringify(element_info.bg_set));
		// Check if bg_set array is populated with empty objects
		if (element_info.bg_set.every(item => item.src === "")) {
			// If bg_set array is populated with empty objects, populate it with the URLs from the matches array
			element_info.bg_set = matches.map(m => m[1] ? {src: m[1].trim()} : {});
		}

		if (element_info.bg_set.length > 0) {
			element_info.src = element_info.bg_set[0].src;
			if (element_info.type === "bg-img-set") {
				element_info.src = element_info.bg_set;
			}
		}
	}

	return element_info;
}

let performance_images = [];

async function main() {
	const filteredArray = LCPCandidates(1);
	if (filteredArray.length !== 0) {
		console.log("Estimated LCP element:", filteredArray);
		performance_images = filteredArray.map((item) => ({
			...item.elementInfo,
			label: "lcp",
		}));
	} else {
		console.log("No LCP candidate found.");
	}

	var above_the_fold_images = document.querySelectorAll("img");

	for (var i = 0; i < above_the_fold_images.length; i++) {
		var image = above_the_fold_images[i];
		var rect = image.getBoundingClientRect();
		var intersecting = isIntersecting(rect);
		if (intersecting) {
			var parent = image.parentNode;
			while (parent !== document) {
				var displayStyle = window.getComputedStyle(parent).display;
				var visibilityStyle =
					window.getComputedStyle(parent).visibility;
				if (displayStyle === "none" || visibilityStyle === "hidden") {
					break;
				}
				parent = parent.parentNode;
			}
			const isDuplicate = performance_images.some(
				(item) => item.src === image.src
			);

			// If it's not a duplicate, push the new element
			if (!isDuplicate && parent === document) {
				performance_images.push({
					src: image.src,
					label: "above-the-fold",
				});
			}
		}
	}
	console.log(performance_images);
	var performance_images_json = JSON.stringify(performance_images);
	window.performance_images_json = performance_images_json;
	console.log(performance_images_json);

	const data = new FormData();

	data.append('action', 'rocket_lcp');
	data.append('rocket_lcp_nonce', rocket_lcp_data.nonce);
	data.append('url', rocket_lcp_data.url);
	data.append('is_mobile', rocket_lcp_data.is_mobile);
	data.append('images', performance_images_json);
	data.append('status', 'success');

	await fetch(rocket_lcp_data.ajax_url, {
		method: "POST",
		credentials: 'same-origin',
		body: data
	})
	.then((response) => response.json())
	.then((data) => {
		console.log(data);
	})
	.catch((error) => {
		console.error(error);
	});
}

if (document.readyState !== 'loading') {
	console.time("extract");
	setTimeout(main, 500);
	console.timeEnd("extract");
} else {
	document.addEventListener("DOMContentLoaded", async function () {
		console.time("extract");
		setTimeout(main, 500);
		console.timeEnd("extract");
	});
}