class RocketLcpBeacon {
	constructor( config ) {
		this.config            = config;
		this.performanceImages = [];
		this.errorCode         = '';
		this.scriptTimer       = new Date();
		this.infiniteLoopId    = null;
	}

	init() {
		if ( ! this._isValidPreconditions() ) {
			this._finalize();
			return;
		}

		this.infiniteLoopId = setTimeout( () => {
			this._handleInfiniteLoop();
		}, 10000 );

		try {
			// Use _generateLcpCandidates method to get all the elements in the viewport.
			const above_the_fold_images = this._generateLcpCandidates( Infinity );
			if ( above_the_fold_images ) {
				this._initWithFirstElementWithInfo( above_the_fold_images );
				this._fillATFWithoutDuplications( above_the_fold_images );
			}
		} catch ( err ) {
			this.errorCode = 'script_error';
			this._logMessage( 'Script Error: ' + err );
		}

		this._saveFinalResultIntoDB();
	}

	_isValidPreconditions() {
		// Check the screensize first because starting any logic.
		if ( this._isNotValidScreensize() ) {
			this._logMessage('Bailing out because screen size is not acceptable');
			return false;
		}

		if ( this._isGeneratedBefore() ) {
			this._logMessage('Bailing out because data is already available');
			return false;
		}

		return true;
	}

	_isGeneratedBefore() {
		// AJAX call to check if there are any records for the current URL.
		let data_check = new FormData();
		data_check.append('action', 'rocket_check_lcp');
		data_check.append('rocket_lcp_nonce', this.config.nonce);
		data_check.append('url', this.config.url);
		data_check.append('is_mobile', this.config.is_mobile);

		const lcp_data_response = fetch(this.config.ajax_url, {
			method: "POST",
			credentials: 'same-origin',
			body: data_check
		});

		return lcp_data_response.success;
	}

	_isNotValidScreensize() {
		// Check screen size
		const screenWidth = window.innerWidth || document.documentElement.clientWidth;
		const screenHeight= window.innerHeight || document.documentElement.clientHeight;

		const isNotValidForMobile = this.config.is_mobile &&
			( screenWidth > this.config.width_threshold || screenHeight > this.config.height_threshold );
		const isNotValidForDesktop = !this.config.is_mobile &&
			( screenWidth < this.config.width_threshold || screenHeight < this.config.height_threshold );

		return isNotValidForMobile || isNotValidForDesktop;
	}

	_generateLcpCandidates( count ) {
		const lcpElements = document.querySelectorAll( this.config.elements );

		if ( lcpElements.length <= 0 ) {
			return [];
		}

		const potentialCandidates = Array.from( lcpElements );

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
				this._isIntersecting(item.rect)
			);
		})
			.map(item => ({
				item,
				area: this._getElementArea(item.rect),
				elementInfo: this._getElementInfo(item.element),
			}))
			.sort((a, b) => b.area - a.area)
			.slice(0, count);

		return topCandidates.map(candidate => ({
			element: candidate.item.element,
			elementInfo: candidate.elementInfo,
		}));
	}

	_isIntersecting(rect) {
		// Check if any part of the image is within the viewport
		return (
			rect.bottom >= 0 &&
			rect.right >= 0 &&
			rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
			rect.left <= (window.innerWidth || document.documentElement.clientWidth)
		);
	}

	_getElementArea(rect) {
		const visibleWidth = Math.min(rect.width, (window.innerWidth || document.documentElement.clientWidth) - rect.left);
		const visibleHeight = Math.min(rect.height, (window.innerHeight || document.documentElement.clientHeight) - rect.top);

		return visibleWidth * visibleHeight;
	}

	_getElementInfo(element) {
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

	_initWithFirstElementWithInfo(elements) {
		const firstElementWithInfo = elements.find(item => item.elementInfo !== null);

		if ( ! firstElementWithInfo ) {
			this._logMessage("No LCP candidate found.");
			this.performanceImages = [];
			return;
		}

		this.performanceImages = [{
			...firstElementWithInfo.elementInfo,
			label: "lcp",
		}];
	}

	_fillATFWithoutDuplications(elements) {
		elements.forEach(({ element, elementInfo }) => {
			if ( this._isDuplicateImage(element) ) {
				return;
			}

			this.performanceImages.push({ ...elementInfo, label: "above-the-fold" });
		});
	}

	_isDuplicateImage(image) {
		const elementInfo = this._getElementInfo(image);

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

		return (isImageOrVideo || isBgImageOrPicture)
			&&
			this.performanceImages.some(item => item.src === elementInfo.src);
	}

	_getFinalStatus() {
		if ( '' !== this.errorCode ) {
			return this.errorCode;
		}

		const scriptTime = ( new Date() - this.scriptTimer ) / 1000;
		if ( 10 <= scriptTime ) {
			return 'timeout';
		}

		return 'success';
	}

	_saveFinalResultIntoDB() {
		const data = new FormData();
		data.append('action', 'rocket_lcp');
		data.append('rocket_lcp_nonce', this.config.nonce);
		data.append('url', this.config.url);
		data.append('is_mobile', this.config.is_mobile);
		data.append('images', JSON.stringify(this.performanceImages));
		data.append('status', this._getFinalStatus());

		fetch(this.config.ajax_url, {
			method: "POST",
			credentials: 'same-origin',
			body: data,
			headers: {
				'wpr-saas-no-intercept':  true
			}
		})
			.then((response) => response.json())
			.then((data) => {
				this._logMessage(data);
			})
			.catch((error) => {
				this._logMessage(error);
			})
			.finally(() => {
				this._finalize();
			});
	}

	_handleInfiniteLoop() {
		this._saveFinalResultIntoDB();
	}

	_finalize() {
		const beaconscript = document.querySelector('[data-name="wpr-lcp-beacon"]');
		beaconscript.setAttribute('beacon-completed', 'true');
		clearTimeout( this.infiniteLoopId );
	}

	_logMessage( msg ) {
		if ( ! this.config.debug ) {
			return;
		}
		console.log( msg );
	}

	static run() {
		if ( !window.rocket_lcp_data ) {
			return;
		}

		const instance = new RocketLcpBeacon( window.rocket_lcp_data );

		if (document.readyState !== 'loading') {
			setTimeout(() => {
				instance.init();
			}, 500);
			return;
		}

		document.addEventListener("DOMContentLoaded", () => {
			setTimeout(() => {
				instance.init();
			}, 500);
		});
	}
}

RocketLcpBeacon.run();