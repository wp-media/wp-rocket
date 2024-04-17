function isIntersecting(rect) {
	return (
		rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
		rect.left <= (window.innerWidth || document.documentElement.clientWidth)
	);
}

function LCPCandidates(count) {
    const potentialCandidates = document.querySelectorAll(
        "img, video, p, main, div"
    ); // Adjust selectors as needed
    const topCandidates = [];

    potentialCandidates.forEach((element) => {
        const rect = element.getBoundingClientRect();
		if (
			rect.width > 0 &&
			rect.height > 0 &&
			isIntersecting(rect)
		) {
			const visibleWidth = Math.min(rect.width, (window.innerWidth || document.documentElement.clientWidth) - rect.left);
			const visibleHeight = Math.min(rect.height, (window.innerHeight || document.documentElement.clientHeight) - rect.top);
			const area = visibleWidth * visibleHeight;
			const imageURL = getImageUrlFromElement(element);
            if (imageURL !== null) {
                // Insert element into topCandidates in descending order of area
                for (let i = 0; i < topCandidates.length; i++) {
                    if (area > topCandidates[i].area) {
                        topCandidates.splice(i, 0, { element, area, imageURL });
                        topCandidates.length = Math.min(
                            count,
                            topCandidates.length
                        ); // Keep only specified number of elements
                        break;
                    }
                }
                // If topCandidates is not full, append
                if (topCandidates.length < count) {
                    topCandidates.push({ element, area, imageURL });
                }
            }
        }
    });

	return topCandidates.map((candidate) => ({
		element: candidate.element,
		imageURL: candidate.imageURL,
	}));
}

function getImageUrlFromElement(element) {
    // Check if the element is an <img> element
    if (element.tagName === "IMG") {
        return element.src;
    }

    // Check if the element has a background image using computed style
    const backgroundImage = window
        .getComputedStyle(element)
        .getPropertyValue("background-image");

    // Check if the background image property is not 'none'
    if (backgroundImage && backgroundImage !== "none") {
        // Extract the URL from the 'url("...")' format
        const imageUrl = backgroundImage.replace(/^url\(['"](.+)['"]\)$/, "$1");
        return imageUrl;
    }

    // If no image is found
    return null;
}

let performance_images = [];

async function main() {
	// AJAX call to check if there are any records for the current URL
	let data_check = new FormData();
	data_check.append('action', 'rocket_check_lcp');
	data_check.append('rocket_lcp_nonce', rocket_lcp_data.nonce);
	data_check.append('url', rocket_lcp_data.url);
	data_check.append('is_mobile', rocket_lcp_data.is_mobile);

	const response = await fetch(rocket_lcp_data.ajax_url, {
		method: "POST",
		credentials: 'same-origin',
		body: data_check
	});

	if ( true === lcp_data.success ) {
		console.log('Bailing out because data is already available');
		return;
	}

	// Check screen size
	const screenWidth = window.innerWidth || document.documentElement.clientWidth;
	const screenHeight = window.innerHeight || document.documentElement.clientHeight;
	if (
		( ( screenWidth < rocket_lcp_data.width_threshold || screenHeight < rocket_lcp_data.height_threshold ) ) ) {
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