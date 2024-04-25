function isDuplicateImage(image, performance_images) {
	const elementInfo = getElementInfo(image);

	if (elementInfo === null) {
		return false;
	}

	const isImageOrVideo =
		elementInfo.type === "img" ||
		elementInfo.type === "img-srcset" ||
		elementInfo.type === "video";

	const isBgImageOrPicture =
		elementInfo.type === "bg-img" ||
		elementInfo.type === "bg-img-set" ||
		elementInfo.type === "picture";

	return (
		isImageOrVideo || isBgImageOrPicture
	) && performance_images.some(item => item.src === elementInfo.src);
}

function isIntersecting(rect) {
	// Check if any part of the image is within the viewport
	return (
		rect.bottom >= 0 &&
		rect.right >= 0 &&
		rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
		rect.left <= (window.innerWidth || document.documentElement.clientWidth)
	);
}

function LCPCandidates(count) {
	const potentialCandidates = Array.from(document.querySelectorAll(
		rocket_lcp_data.elements,
	));

	const topCandidates = potentialCandidates.map(element => {
		const rect = element.getBoundingClientRect();
		return {
			element: element,
			rect: rect,
		};
	}).filter(item => {
		return (
			item.rect.width > 0 &&
			item.rect.height > 0 &&
			isIntersecting(item.rect)
		);
	})
	.map(item => ({
		item,
		area: getArea(item.rect),
		elementInfo: getElementInfo(item.element),
	}))
	.sort((a, b) => b.area - a.area)
	.slice(0, count);

	return topCandidates.map(candidate => ({
		element: candidate.item.element,
		elementInfo: candidate.elementInfo,
	}));
}

function getArea(rect) {
	const visibleWidth = Math.min(rect.width, (window.innerWidth || document.documentElement.clientWidth) - rect.left);
	const visibleHeight = Math.min(rect.height, (window.innerHeight || document.documentElement.clientHeight) - rect.top);

	return visibleWidth * visibleHeight;
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

	if (nodeName === "img" && element.srcset) {
		element_info.type = "img-srcset";
		element_info.src = element.src;
		element_info.srcset = element.srcset; // capture srcset
		element_info.sizes = element.sizes; // capture sizes
		element_info.current_src = element.currentSrc;
	} else if (nodeName === "img") {
		element_info.type = "img";
		element_info.src = element.src;
		element_info.current_src = element.currentSrc;
	} else if (nodeName === "video") {
		element_info.type = "img";
		const source = element.querySelector('source');
		element_info.src = element.poster || (source ? source.src : '');
		element_info.current_src = element_info.src;
	} else if (nodeName === "svg") {
		const imageElement = element.querySelector('image');
		if (imageElement) {
			element_info.type = "img";
			element_info.src = imageElement.getAttribute('href') || '';
			element_info.current_src = element_info.src;
		}
	} else if (nodeName === "picture") {
		element_info.type = "picture";
		const img = element.querySelector('img:not(picture>img)');
		element_info.src = img ? img.src : "";
		element_info.sources = Array.from(element.querySelectorAll('source')).map(source => ({
			srcset: source.srcset || '',
			media: source.media || ''
		}));
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
		element_info.bg_set = matches.map(m => m[1] ? {src: m[1].trim() + (m[2] ? " " + m[2].trim() : "")} : {});
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

function main() {

	// AJAX call to check if there are any records for the current URL
	let data_check = new FormData();
	data_check.append('action', 'rocket_check_lcp');
	data_check.append('rocket_lcp_nonce', rocket_lcp_data.nonce);
	data_check.append('url', rocket_lcp_data.url);
	data_check.append('is_mobile', rocket_lcp_data.is_mobile);

	const lcp_data_response = fetch(rocket_lcp_data.ajax_url, {
		method: "POST",
		credentials: 'same-origin',
		body: data_check
	});

	if ( true === lcp_data_response.success ) {
		console.log('Bailing out because data is already available');
		return;
	}

	// Check screen size
	const screenWidth = window.innerWidth || document.documentElement.clientWidth;
	const screenHeight = window.innerHeight || document.documentElement.clientHeight;

	if ( ( rocket_lcp_data.is_mobile && ( screenWidth > rocket_lcp_data.width_threshold || screenHeight > rocket_lcp_data.height_threshold ) ) ||
		( ! rocket_lcp_data.is_mobile && ( screenWidth < rocket_lcp_data.width_threshold || screenHeight < rocket_lcp_data.height_threshold ) ) )
	{
		console.log('Bailing out because screen size is not acceptable');
		return;
	}

	// Filter the array based on the condition imageURL is not null
	const filteredArray = LCPCandidates(1)
	if (filteredArray.length !== 0) {
		console.log("Estimated LCP element:", filteredArray);
		performance_images = filteredArray.map((item) => ({
			src: item.imageURL,
			label: "lcp",
		}));
	} else {
		console.log("No LCP candidate found.");
	}

	// Use LCPCandidates function to get all the elements in the viewport
	const above_the_fold_images = LCPCandidates(Infinity);

	const firstElementWithInfo = above_the_fold_images.find(item => item.elementInfo !== null);

	if (firstElementWithInfo) {
		performance_images = [{
			...firstElementWithInfo.elementInfo,
			label: "lcp",
		}];
	} else {
		console.log("No LCP candidate found.");
	}

	above_the_fold_images.forEach(({ element, elementInfo }) => {
		const isDuplicate = isDuplicateImage(element, performance_images);

		if (!isDuplicate) {
			performance_images.push({ ...elementInfo, label: "above-the-fold" });
		}
	});

	let performance_images_json = JSON.stringify(performance_images);
	window.performance_images_json = performance_images_json;
	const payload = {
		action: 'rocket_lcp',
		rocket_lcp_nonce: rocket_lcp_data.nonce,
		url: rocket_lcp_data.url,
		is_mobile: rocket_lcp_data.is_mobile,
		images: performance_images_json,
		status: 'success'
	};

	const data = new FormData();
	data.append('action', 'rocket_lcp');
	data.append('rocket_lcp_nonce', rocket_lcp_data.nonce);
	data.append('url', rocket_lcp_data.url);
	data.append('is_mobile', rocket_lcp_data.is_mobile);
	data.append('images', performance_images_json);
	data.append('status', 'success');

	fetch(rocket_lcp_data.ajax_url, {
		method: "POST",
		credentials: 'same-origin',
		body: data,
		headers: {
			'wpr-saas-no-intercept':  true
		}
	})
	.then((response) => response.json())
	.then((data) => {
		const beaconscript = document.querySelector('[data-name="wpr-lcp-beacon"]');
		beaconscript.setAttribute('beacon-completed', 'true');
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
	document.addEventListener("DOMContentLoaded", function () {
		console.time("extract");
		setTimeout(main, 500);
		console.timeEnd("extract");
	});
}