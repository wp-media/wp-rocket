(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';
const LcpBeacon = require("./src/LcpBeacon");
module.exports = LcpBeacon;
},{"./src/LcpBeacon":2}],2:[function(require,module,exports){
'use strict';
class LcpBeacon {

    constructor( config ) {
        this.config            = config;
        this.performanceImages = [];
        this.errorCode         = '';
        this.scriptTimer       = new Date();
        this.infiniteLoopId    = null;
    }

    async init() {
        if ( ! await this._isValidPreconditions() ) {
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

    async _isValidPreconditions() {
        // Check the screensize first because starting any logic.
        if ( this._isNotValidScreensize() ) {
            this._logMessage('Bailing out because screen size is not acceptable');
            return false;
        }

        if ( this._isPageCached() && await this._isGeneratedBefore() ) {
            this._logMessage('Bailing out because data is already available');
            return false;
        }

        return true;
    }

    _isPageCached() {
        const signature = document.documentElement.nextSibling && document.documentElement.nextSibling.data ? document.documentElement.nextSibling.data : '';

        return signature && signature.includes( 'Debug: cached' );
    }

    async _isGeneratedBefore() {
        // AJAX call to check if there are any records for the current URL.
        let data_check = new FormData();
        data_check.append('action', 'rocket_check_lcp');
        data_check.append('rocket_lcp_nonce', this.config.nonce);
        data_check.append('url', this.config.url);
        data_check.append('is_mobile', this.config.is_mobile);

        const lcp_data_response = await fetch(this.config.ajax_url, {
            method: "POST",
            credentials: 'same-origin',
            body: data_check
        })
            .then(data => data.json());
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
            // Skip if the element is an img and its parent is a picture
            if ('img' === element.nodeName.toLowerCase() && 'picture' === element.parentElement.nodeName.toLowerCase() ) {
                return null;
            }
            let rect;
            if ('picture' === element.nodeName.toLowerCase()) {
                const imgElement = element.querySelector('img');
                if (imgElement) {
                    rect = imgElement.getBoundingClientRect();
                } else {
                    return null;
                }
            } else {
                rect = element.getBoundingClientRect();
            }

            return {
                element: element,
                rect: rect,
            };
        })
            .filter(item => item !== null) // Filter out null values here
            .filter(item => {
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

        const css_bg_url_rgx = /url\(\s*?['"]?\s*?(.+?)\s*?["']?\s*?\)/ig;

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
            const img = element.querySelector('img');
            element_info.src = img ? img.src : "";
            element_info.sources = Array.from(element.querySelectorAll('source')).map(source => ({
                srcset: source.srcset || '',
                media: source.media || '',
                type: source.type || '',
                sizes: source.sizes || ''
            }));
        } else {
            const computed_style = window.getComputedStyle(element, null);
            const bg_props = [
                computed_style.getPropertyValue("background-image"),
                getComputedStyle(element, ":after").getPropertyValue("background-image"),
                getComputedStyle(element, ":before").getPropertyValue("background-image")
            ].filter(prop => prop !== "none");

            if (bg_props.length === 0) {
                return null;
            }
            const full_bg_prop = bg_props[0];
            element_info.type = "bg-img";
            if (full_bg_prop.includes("image-set(")) {
                element_info.type = "bg-img-set";
            }
            if (!full_bg_prop || full_bg_prop === "" || full_bg_prop.includes( 'data:image' ) ) {
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
            if ( this._isDuplicateImage(element) || !elementInfo ) {
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
}

module.exports = LcpBeacon;

},{}],3:[function(require,module,exports){
"use strict";

var _rocketScripts = _interopRequireDefault(require("rocket-scripts"));
function _interopRequireDefault(e) { return e && e.__esModule ? e : { default: e }; }
(rocket_lcp_data => {
  if (!rocket_lcp_data) {
    return;
  }
  const instance = new _rocketScripts.default(rocket_lcp_data);
  if (document.readyState !== 'loading') {
    setTimeout(() => {
      instance.init();
    }, rocket_lcp_data.delay);
    return;
  }
  document.addEventListener("DOMContentLoaded", () => {
    setTimeout(() => {
      instance.init();
    }, rocket_lcp_data.delay);
  });
})(window.rocket_lcp_data);

},{"rocket-scripts":1}]},{},[3])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJub2RlX21vZHVsZXMvcm9ja2V0LXNjcmlwdHMvaW5kZXguanMiLCJub2RlX21vZHVsZXMvcm9ja2V0LXNjcmlwdHMvc3JjL0xjcEJlYWNvbi5qcyIsInNyYy9qcy9jdXN0b20vbGNwLWJlYWNvbi5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtBQ0FBO0FBQ0E7QUFDQTs7QUNGQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7Ozs7QUM5VkEsSUFBQSxjQUFBLEdBQUEsc0JBQUEsQ0FBQSxPQUFBO0FBQXVDLFNBQUEsdUJBQUEsQ0FBQSxXQUFBLENBQUEsSUFBQSxDQUFBLENBQUEsVUFBQSxHQUFBLENBQUEsS0FBQSxPQUFBLEVBQUEsQ0FBQTtBQUV2QyxDQUFFLGVBQWUsSUFBSTtFQUNwQixJQUFLLENBQUMsZUFBZSxFQUFHO0lBQ3ZCO0VBQ0Q7RUFFQSxNQUFNLFFBQVEsR0FBRyxJQUFJLHNCQUFTLENBQUUsZUFBZ0IsQ0FBQztFQUVqRCxJQUFJLFFBQVEsQ0FBQyxVQUFVLEtBQUssU0FBUyxFQUFFO0lBQ3RDLFVBQVUsQ0FBQyxNQUFNO01BQ2hCLFFBQVEsQ0FBQyxJQUFJLENBQUMsQ0FBQztJQUNoQixDQUFDLEVBQUUsZUFBZSxDQUFDLEtBQUssQ0FBQztJQUN6QjtFQUNEO0VBRUEsUUFBUSxDQUFDLGdCQUFnQixDQUFDLGtCQUFrQixFQUFFLE1BQU07SUFDbkQsVUFBVSxDQUFDLE1BQU07TUFDaEIsUUFBUSxDQUFDLElBQUksQ0FBQyxDQUFDO0lBQ2hCLENBQUMsRUFBRSxlQUFlLENBQUMsS0FBSyxDQUFDO0VBQzFCLENBQUMsQ0FBQztBQUNILENBQUMsRUFBSSxNQUFNLENBQUMsZUFBZ0IsQ0FBQyIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uKCl7ZnVuY3Rpb24gcihlLG4sdCl7ZnVuY3Rpb24gbyhpLGYpe2lmKCFuW2ldKXtpZighZVtpXSl7dmFyIGM9XCJmdW5jdGlvblwiPT10eXBlb2YgcmVxdWlyZSYmcmVxdWlyZTtpZighZiYmYylyZXR1cm4gYyhpLCEwKTtpZih1KXJldHVybiB1KGksITApO3ZhciBhPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIraStcIidcIik7dGhyb3cgYS5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGF9dmFyIHA9bltpXT17ZXhwb3J0czp7fX07ZVtpXVswXS5jYWxsKHAuZXhwb3J0cyxmdW5jdGlvbihyKXt2YXIgbj1lW2ldWzFdW3JdO3JldHVybiBvKG58fHIpfSxwLHAuZXhwb3J0cyxyLGUsbix0KX1yZXR1cm4gbltpXS5leHBvcnRzfWZvcih2YXIgdT1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlLGk9MDtpPHQubGVuZ3RoO2krKylvKHRbaV0pO3JldHVybiBvfXJldHVybiByfSkoKSIsIid1c2Ugc3RyaWN0JztcbmNvbnN0IExjcEJlYWNvbiA9IHJlcXVpcmUoXCIuL3NyYy9MY3BCZWFjb25cIik7XG5tb2R1bGUuZXhwb3J0cyA9IExjcEJlYWNvbjsiLCIndXNlIHN0cmljdCc7XG5jbGFzcyBMY3BCZWFjb24ge1xuXG4gICAgY29uc3RydWN0b3IoIGNvbmZpZyApIHtcbiAgICAgICAgdGhpcy5jb25maWcgICAgICAgICAgICA9IGNvbmZpZztcbiAgICAgICAgdGhpcy5wZXJmb3JtYW5jZUltYWdlcyA9IFtdO1xuICAgICAgICB0aGlzLmVycm9yQ29kZSAgICAgICAgID0gJyc7XG4gICAgICAgIHRoaXMuc2NyaXB0VGltZXIgICAgICAgPSBuZXcgRGF0ZSgpO1xuICAgICAgICB0aGlzLmluZmluaXRlTG9vcElkICAgID0gbnVsbDtcbiAgICB9XG5cbiAgICBhc3luYyBpbml0KCkge1xuICAgICAgICBpZiAoICEgYXdhaXQgdGhpcy5faXNWYWxpZFByZWNvbmRpdGlvbnMoKSApIHtcbiAgICAgICAgICAgIHRoaXMuX2ZpbmFsaXplKCk7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLmluZmluaXRlTG9vcElkID0gc2V0VGltZW91dCggKCkgPT4ge1xuICAgICAgICAgICAgdGhpcy5faGFuZGxlSW5maW5pdGVMb29wKCk7XG4gICAgICAgIH0sIDEwMDAwICk7XG5cbiAgICAgICAgdHJ5IHtcbiAgICAgICAgICAgIC8vIFVzZSBfZ2VuZXJhdGVMY3BDYW5kaWRhdGVzIG1ldGhvZCB0byBnZXQgYWxsIHRoZSBlbGVtZW50cyBpbiB0aGUgdmlld3BvcnQuXG4gICAgICAgICAgICBjb25zdCBhYm92ZV90aGVfZm9sZF9pbWFnZXMgPSB0aGlzLl9nZW5lcmF0ZUxjcENhbmRpZGF0ZXMoIEluZmluaXR5ICk7XG4gICAgICAgICAgICBpZiAoIGFib3ZlX3RoZV9mb2xkX2ltYWdlcyApIHtcbiAgICAgICAgICAgICAgICB0aGlzLl9pbml0V2l0aEZpcnN0RWxlbWVudFdpdGhJbmZvKCBhYm92ZV90aGVfZm9sZF9pbWFnZXMgKTtcbiAgICAgICAgICAgICAgICB0aGlzLl9maWxsQVRGV2l0aG91dER1cGxpY2F0aW9ucyggYWJvdmVfdGhlX2ZvbGRfaW1hZ2VzICk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0gY2F0Y2ggKCBlcnIgKSB7XG4gICAgICAgICAgICB0aGlzLmVycm9yQ29kZSA9ICdzY3JpcHRfZXJyb3InO1xuICAgICAgICAgICAgdGhpcy5fbG9nTWVzc2FnZSggJ1NjcmlwdCBFcnJvcjogJyArIGVyciApO1xuICAgICAgICB9XG5cbiAgICAgICAgdGhpcy5fc2F2ZUZpbmFsUmVzdWx0SW50b0RCKCk7XG4gICAgfVxuXG4gICAgYXN5bmMgX2lzVmFsaWRQcmVjb25kaXRpb25zKCkge1xuICAgICAgICAvLyBDaGVjayB0aGUgc2NyZWVuc2l6ZSBmaXJzdCBiZWNhdXNlIHN0YXJ0aW5nIGFueSBsb2dpYy5cbiAgICAgICAgaWYgKCB0aGlzLl9pc05vdFZhbGlkU2NyZWVuc2l6ZSgpICkge1xuICAgICAgICAgICAgdGhpcy5fbG9nTWVzc2FnZSgnQmFpbGluZyBvdXQgYmVjYXVzZSBzY3JlZW4gc2l6ZSBpcyBub3QgYWNjZXB0YWJsZScpO1xuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKCB0aGlzLl9pc1BhZ2VDYWNoZWQoKSAmJiBhd2FpdCB0aGlzLl9pc0dlbmVyYXRlZEJlZm9yZSgpICkge1xuICAgICAgICAgICAgdGhpcy5fbG9nTWVzc2FnZSgnQmFpbGluZyBvdXQgYmVjYXVzZSBkYXRhIGlzIGFscmVhZHkgYXZhaWxhYmxlJyk7XG4gICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gdHJ1ZTtcbiAgICB9XG5cbiAgICBfaXNQYWdlQ2FjaGVkKCkge1xuICAgICAgICBjb25zdCBzaWduYXR1cmUgPSBkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQubmV4dFNpYmxpbmcgJiYgZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50Lm5leHRTaWJsaW5nLmRhdGEgPyBkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQubmV4dFNpYmxpbmcuZGF0YSA6ICcnO1xuXG4gICAgICAgIHJldHVybiBzaWduYXR1cmUgJiYgc2lnbmF0dXJlLmluY2x1ZGVzKCAnRGVidWc6IGNhY2hlZCcgKTtcbiAgICB9XG5cbiAgICBhc3luYyBfaXNHZW5lcmF0ZWRCZWZvcmUoKSB7XG4gICAgICAgIC8vIEFKQVggY2FsbCB0byBjaGVjayBpZiB0aGVyZSBhcmUgYW55IHJlY29yZHMgZm9yIHRoZSBjdXJyZW50IFVSTC5cbiAgICAgICAgbGV0IGRhdGFfY2hlY2sgPSBuZXcgRm9ybURhdGEoKTtcbiAgICAgICAgZGF0YV9jaGVjay5hcHBlbmQoJ2FjdGlvbicsICdyb2NrZXRfY2hlY2tfbGNwJyk7XG4gICAgICAgIGRhdGFfY2hlY2suYXBwZW5kKCdyb2NrZXRfbGNwX25vbmNlJywgdGhpcy5jb25maWcubm9uY2UpO1xuICAgICAgICBkYXRhX2NoZWNrLmFwcGVuZCgndXJsJywgdGhpcy5jb25maWcudXJsKTtcbiAgICAgICAgZGF0YV9jaGVjay5hcHBlbmQoJ2lzX21vYmlsZScsIHRoaXMuY29uZmlnLmlzX21vYmlsZSk7XG5cbiAgICAgICAgY29uc3QgbGNwX2RhdGFfcmVzcG9uc2UgPSBhd2FpdCBmZXRjaCh0aGlzLmNvbmZpZy5hamF4X3VybCwge1xuICAgICAgICAgICAgbWV0aG9kOiBcIlBPU1RcIixcbiAgICAgICAgICAgIGNyZWRlbnRpYWxzOiAnc2FtZS1vcmlnaW4nLFxuICAgICAgICAgICAgYm9keTogZGF0YV9jaGVja1xuICAgICAgICB9KVxuICAgICAgICAgICAgLnRoZW4oZGF0YSA9PiBkYXRhLmpzb24oKSk7XG4gICAgICAgIHJldHVybiBsY3BfZGF0YV9yZXNwb25zZS5zdWNjZXNzO1xuICAgIH1cblxuICAgIF9pc05vdFZhbGlkU2NyZWVuc2l6ZSgpIHtcbiAgICAgICAgLy8gQ2hlY2sgc2NyZWVuIHNpemVcbiAgICAgICAgY29uc3Qgc2NyZWVuV2lkdGggPSB3aW5kb3cuaW5uZXJXaWR0aCB8fCBkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQuY2xpZW50V2lkdGg7XG4gICAgICAgIGNvbnN0IHNjcmVlbkhlaWdodD0gd2luZG93LmlubmVySGVpZ2h0IHx8IGRvY3VtZW50LmRvY3VtZW50RWxlbWVudC5jbGllbnRIZWlnaHQ7XG5cbiAgICAgICAgY29uc3QgaXNOb3RWYWxpZEZvck1vYmlsZSA9IHRoaXMuY29uZmlnLmlzX21vYmlsZSAmJlxuICAgICAgICAgICAgKCBzY3JlZW5XaWR0aCA+IHRoaXMuY29uZmlnLndpZHRoX3RocmVzaG9sZCB8fCBzY3JlZW5IZWlnaHQgPiB0aGlzLmNvbmZpZy5oZWlnaHRfdGhyZXNob2xkICk7XG4gICAgICAgIGNvbnN0IGlzTm90VmFsaWRGb3JEZXNrdG9wID0gIXRoaXMuY29uZmlnLmlzX21vYmlsZSAmJlxuICAgICAgICAgICAgKCBzY3JlZW5XaWR0aCA8IHRoaXMuY29uZmlnLndpZHRoX3RocmVzaG9sZCB8fCBzY3JlZW5IZWlnaHQgPCB0aGlzLmNvbmZpZy5oZWlnaHRfdGhyZXNob2xkICk7XG5cbiAgICAgICAgcmV0dXJuIGlzTm90VmFsaWRGb3JNb2JpbGUgfHwgaXNOb3RWYWxpZEZvckRlc2t0b3A7XG4gICAgfVxuXG4gICAgX2dlbmVyYXRlTGNwQ2FuZGlkYXRlcyggY291bnQgKSB7XG4gICAgICAgIGNvbnN0IGxjcEVsZW1lbnRzID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCggdGhpcy5jb25maWcuZWxlbWVudHMgKTtcblxuICAgICAgICBpZiAoIGxjcEVsZW1lbnRzLmxlbmd0aCA8PSAwICkge1xuICAgICAgICAgICAgcmV0dXJuIFtdO1xuICAgICAgICB9XG5cbiAgICAgICAgY29uc3QgcG90ZW50aWFsQ2FuZGlkYXRlcyA9IEFycmF5LmZyb20oIGxjcEVsZW1lbnRzICk7XG5cbiAgICAgICAgY29uc3QgdG9wQ2FuZGlkYXRlcyA9IHBvdGVudGlhbENhbmRpZGF0ZXMubWFwKGVsZW1lbnQgPT4ge1xuICAgICAgICAgICAgLy8gU2tpcCBpZiB0aGUgZWxlbWVudCBpcyBhbiBpbWcgYW5kIGl0cyBwYXJlbnQgaXMgYSBwaWN0dXJlXG4gICAgICAgICAgICBpZiAoJ2ltZycgPT09IGVsZW1lbnQubm9kZU5hbWUudG9Mb3dlckNhc2UoKSAmJiAncGljdHVyZScgPT09IGVsZW1lbnQucGFyZW50RWxlbWVudC5ub2RlTmFtZS50b0xvd2VyQ2FzZSgpICkge1xuICAgICAgICAgICAgICAgIHJldHVybiBudWxsO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgbGV0IHJlY3Q7XG4gICAgICAgICAgICBpZiAoJ3BpY3R1cmUnID09PSBlbGVtZW50Lm5vZGVOYW1lLnRvTG93ZXJDYXNlKCkpIHtcbiAgICAgICAgICAgICAgICBjb25zdCBpbWdFbGVtZW50ID0gZWxlbWVudC5xdWVyeVNlbGVjdG9yKCdpbWcnKTtcbiAgICAgICAgICAgICAgICBpZiAoaW1nRWxlbWVudCkge1xuICAgICAgICAgICAgICAgICAgICByZWN0ID0gaW1nRWxlbWVudC5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKTtcbiAgICAgICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgICAgICByZXR1cm4gbnVsbDtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgIHJlY3QgPSBlbGVtZW50LmdldEJvdW5kaW5nQ2xpZW50UmVjdCgpO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgICAgIGVsZW1lbnQ6IGVsZW1lbnQsXG4gICAgICAgICAgICAgICAgcmVjdDogcmVjdCxcbiAgICAgICAgICAgIH07XG4gICAgICAgIH0pXG4gICAgICAgICAgICAuZmlsdGVyKGl0ZW0gPT4gaXRlbSAhPT0gbnVsbCkgLy8gRmlsdGVyIG91dCBudWxsIHZhbHVlcyBoZXJlXG4gICAgICAgICAgICAuZmlsdGVyKGl0ZW0gPT4ge1xuICAgICAgICAgICAgICAgIHJldHVybiAoXG4gICAgICAgICAgICAgICAgICAgIGl0ZW0ucmVjdC53aWR0aCA+IDAgJiZcbiAgICAgICAgICAgICAgICAgICAgaXRlbS5yZWN0LmhlaWdodCA+IDAgJiZcbiAgICAgICAgICAgICAgICAgICAgdGhpcy5faXNJbnRlcnNlY3RpbmcoaXRlbS5yZWN0KVxuICAgICAgICAgICAgICAgICk7XG4gICAgICAgICAgICB9KVxuICAgICAgICAgICAgLm1hcChpdGVtID0+ICh7XG4gICAgICAgICAgICAgICAgaXRlbSxcbiAgICAgICAgICAgICAgICBhcmVhOiB0aGlzLl9nZXRFbGVtZW50QXJlYShpdGVtLnJlY3QpLFxuICAgICAgICAgICAgICAgIGVsZW1lbnRJbmZvOiB0aGlzLl9nZXRFbGVtZW50SW5mbyhpdGVtLmVsZW1lbnQpLFxuICAgICAgICAgICAgfSkpXG4gICAgICAgICAgICAuc29ydCgoYSwgYikgPT4gYi5hcmVhIC0gYS5hcmVhKVxuICAgICAgICAgICAgLnNsaWNlKDAsIGNvdW50KTtcblxuICAgICAgICByZXR1cm4gdG9wQ2FuZGlkYXRlcy5tYXAoY2FuZGlkYXRlID0+ICh7XG4gICAgICAgICAgICBlbGVtZW50OiBjYW5kaWRhdGUuaXRlbS5lbGVtZW50LFxuICAgICAgICAgICAgZWxlbWVudEluZm86IGNhbmRpZGF0ZS5lbGVtZW50SW5mbyxcbiAgICAgICAgfSkpO1xuICAgIH1cblxuICAgIF9pc0ludGVyc2VjdGluZyhyZWN0KSB7XG4gICAgICAgIC8vIENoZWNrIGlmIGFueSBwYXJ0IG9mIHRoZSBpbWFnZSBpcyB3aXRoaW4gdGhlIHZpZXdwb3J0XG4gICAgICAgIHJldHVybiAoXG4gICAgICAgICAgICByZWN0LmJvdHRvbSA+PSAwICYmXG4gICAgICAgICAgICByZWN0LnJpZ2h0ID49IDAgJiZcbiAgICAgICAgICAgIHJlY3QudG9wIDw9ICh3aW5kb3cuaW5uZXJIZWlnaHQgfHwgZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50LmNsaWVudEhlaWdodCkgJiZcbiAgICAgICAgICAgIHJlY3QubGVmdCA8PSAod2luZG93LmlubmVyV2lkdGggfHwgZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50LmNsaWVudFdpZHRoKVxuICAgICAgICApO1xuICAgIH1cblxuICAgIF9nZXRFbGVtZW50QXJlYShyZWN0KSB7XG4gICAgICAgIGNvbnN0IHZpc2libGVXaWR0aCA9IE1hdGgubWluKHJlY3Qud2lkdGgsICh3aW5kb3cuaW5uZXJXaWR0aCB8fCBkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQuY2xpZW50V2lkdGgpIC0gcmVjdC5sZWZ0KTtcbiAgICAgICAgY29uc3QgdmlzaWJsZUhlaWdodCA9IE1hdGgubWluKHJlY3QuaGVpZ2h0LCAod2luZG93LmlubmVySGVpZ2h0IHx8IGRvY3VtZW50LmRvY3VtZW50RWxlbWVudC5jbGllbnRIZWlnaHQpIC0gcmVjdC50b3ApO1xuXG4gICAgICAgIHJldHVybiB2aXNpYmxlV2lkdGggKiB2aXNpYmxlSGVpZ2h0O1xuICAgIH1cblxuICAgIF9nZXRFbGVtZW50SW5mbyhlbGVtZW50KSB7XG4gICAgICAgIGNvbnN0IG5vZGVOYW1lID0gZWxlbWVudC5ub2RlTmFtZS50b0xvd2VyQ2FzZSgpO1xuICAgICAgICBjb25zdCBlbGVtZW50X2luZm8gPSB7XG4gICAgICAgICAgICB0eXBlOiBcIlwiLFxuICAgICAgICAgICAgc3JjOiBcIlwiLFxuICAgICAgICAgICAgc3Jjc2V0OiBcIlwiLFxuICAgICAgICAgICAgc2l6ZXM6IFwiXCIsXG4gICAgICAgICAgICBzb3VyY2VzOiBbXSxcbiAgICAgICAgICAgIGJnX3NldDogW10sXG4gICAgICAgICAgICBjdXJyZW50X3NyYzogXCJcIlxuICAgICAgICB9O1xuXG4gICAgICAgIGNvbnN0IGNzc19iZ191cmxfcmd4ID0gL3VybFxcKFxccyo/WydcIl0/XFxzKj8oLis/KVxccyo/W1wiJ10/XFxzKj9cXCkvaWc7XG5cbiAgICAgICAgaWYgKG5vZGVOYW1lID09PSBcImltZ1wiICYmIGVsZW1lbnQuc3Jjc2V0KSB7XG4gICAgICAgICAgICBlbGVtZW50X2luZm8udHlwZSA9IFwiaW1nLXNyY3NldFwiO1xuICAgICAgICAgICAgZWxlbWVudF9pbmZvLnNyYyA9IGVsZW1lbnQuc3JjO1xuICAgICAgICAgICAgZWxlbWVudF9pbmZvLnNyY3NldCA9IGVsZW1lbnQuc3Jjc2V0OyAvLyBjYXB0dXJlIHNyY3NldFxuICAgICAgICAgICAgZWxlbWVudF9pbmZvLnNpemVzID0gZWxlbWVudC5zaXplczsgLy8gY2FwdHVyZSBzaXplc1xuICAgICAgICAgICAgZWxlbWVudF9pbmZvLmN1cnJlbnRfc3JjID0gZWxlbWVudC5jdXJyZW50U3JjO1xuICAgICAgICB9IGVsc2UgaWYgKG5vZGVOYW1lID09PSBcImltZ1wiKSB7XG4gICAgICAgICAgICBlbGVtZW50X2luZm8udHlwZSA9IFwiaW1nXCI7XG4gICAgICAgICAgICBlbGVtZW50X2luZm8uc3JjID0gZWxlbWVudC5zcmM7XG4gICAgICAgICAgICBlbGVtZW50X2luZm8uY3VycmVudF9zcmMgPSBlbGVtZW50LmN1cnJlbnRTcmM7XG4gICAgICAgIH0gZWxzZSBpZiAobm9kZU5hbWUgPT09IFwidmlkZW9cIikge1xuICAgICAgICAgICAgZWxlbWVudF9pbmZvLnR5cGUgPSBcImltZ1wiO1xuICAgICAgICAgICAgY29uc3Qgc291cmNlID0gZWxlbWVudC5xdWVyeVNlbGVjdG9yKCdzb3VyY2UnKTtcbiAgICAgICAgICAgIGVsZW1lbnRfaW5mby5zcmMgPSBlbGVtZW50LnBvc3RlciB8fCAoc291cmNlID8gc291cmNlLnNyYyA6ICcnKTtcbiAgICAgICAgICAgIGVsZW1lbnRfaW5mby5jdXJyZW50X3NyYyA9IGVsZW1lbnRfaW5mby5zcmM7XG4gICAgICAgIH0gZWxzZSBpZiAobm9kZU5hbWUgPT09IFwic3ZnXCIpIHtcbiAgICAgICAgICAgIGNvbnN0IGltYWdlRWxlbWVudCA9IGVsZW1lbnQucXVlcnlTZWxlY3RvcignaW1hZ2UnKTtcbiAgICAgICAgICAgIGlmIChpbWFnZUVsZW1lbnQpIHtcbiAgICAgICAgICAgICAgICBlbGVtZW50X2luZm8udHlwZSA9IFwiaW1nXCI7XG4gICAgICAgICAgICAgICAgZWxlbWVudF9pbmZvLnNyYyA9IGltYWdlRWxlbWVudC5nZXRBdHRyaWJ1dGUoJ2hyZWYnKSB8fCAnJztcbiAgICAgICAgICAgICAgICBlbGVtZW50X2luZm8uY3VycmVudF9zcmMgPSBlbGVtZW50X2luZm8uc3JjO1xuICAgICAgICAgICAgfVxuICAgICAgICB9IGVsc2UgaWYgKG5vZGVOYW1lID09PSBcInBpY3R1cmVcIikge1xuICAgICAgICAgICAgZWxlbWVudF9pbmZvLnR5cGUgPSBcInBpY3R1cmVcIjtcbiAgICAgICAgICAgIGNvbnN0IGltZyA9IGVsZW1lbnQucXVlcnlTZWxlY3RvcignaW1nJyk7XG4gICAgICAgICAgICBlbGVtZW50X2luZm8uc3JjID0gaW1nID8gaW1nLnNyYyA6IFwiXCI7XG4gICAgICAgICAgICBlbGVtZW50X2luZm8uc291cmNlcyA9IEFycmF5LmZyb20oZWxlbWVudC5xdWVyeVNlbGVjdG9yQWxsKCdzb3VyY2UnKSkubWFwKHNvdXJjZSA9PiAoe1xuICAgICAgICAgICAgICAgIHNyY3NldDogc291cmNlLnNyY3NldCB8fCAnJyxcbiAgICAgICAgICAgICAgICBtZWRpYTogc291cmNlLm1lZGlhIHx8ICcnLFxuICAgICAgICAgICAgICAgIHR5cGU6IHNvdXJjZS50eXBlIHx8ICcnLFxuICAgICAgICAgICAgICAgIHNpemVzOiBzb3VyY2Uuc2l6ZXMgfHwgJydcbiAgICAgICAgICAgIH0pKTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIGNvbnN0IGNvbXB1dGVkX3N0eWxlID0gd2luZG93LmdldENvbXB1dGVkU3R5bGUoZWxlbWVudCwgbnVsbCk7XG4gICAgICAgICAgICBjb25zdCBiZ19wcm9wcyA9IFtcbiAgICAgICAgICAgICAgICBjb21wdXRlZF9zdHlsZS5nZXRQcm9wZXJ0eVZhbHVlKFwiYmFja2dyb3VuZC1pbWFnZVwiKSxcbiAgICAgICAgICAgICAgICBnZXRDb21wdXRlZFN0eWxlKGVsZW1lbnQsIFwiOmFmdGVyXCIpLmdldFByb3BlcnR5VmFsdWUoXCJiYWNrZ3JvdW5kLWltYWdlXCIpLFxuICAgICAgICAgICAgICAgIGdldENvbXB1dGVkU3R5bGUoZWxlbWVudCwgXCI6YmVmb3JlXCIpLmdldFByb3BlcnR5VmFsdWUoXCJiYWNrZ3JvdW5kLWltYWdlXCIpXG4gICAgICAgICAgICBdLmZpbHRlcihwcm9wID0+IHByb3AgIT09IFwibm9uZVwiKTtcblxuICAgICAgICAgICAgaWYgKGJnX3Byb3BzLmxlbmd0aCA9PT0gMCkge1xuICAgICAgICAgICAgICAgIHJldHVybiBudWxsO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgY29uc3QgZnVsbF9iZ19wcm9wID0gYmdfcHJvcHNbMF07XG4gICAgICAgICAgICBlbGVtZW50X2luZm8udHlwZSA9IFwiYmctaW1nXCI7XG4gICAgICAgICAgICBpZiAoZnVsbF9iZ19wcm9wLmluY2x1ZGVzKFwiaW1hZ2Utc2V0KFwiKSkge1xuICAgICAgICAgICAgICAgIGVsZW1lbnRfaW5mby50eXBlID0gXCJiZy1pbWctc2V0XCI7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBpZiAoIWZ1bGxfYmdfcHJvcCB8fCBmdWxsX2JnX3Byb3AgPT09IFwiXCIgfHwgZnVsbF9iZ19wcm9wLmluY2x1ZGVzKCAnZGF0YTppbWFnZScgKSApIHtcbiAgICAgICAgICAgICAgICByZXR1cm4gbnVsbDtcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgY29uc3QgbWF0Y2hlcyA9IFsuLi5mdWxsX2JnX3Byb3AubWF0Y2hBbGwoY3NzX2JnX3VybF9yZ3gpXTtcbiAgICAgICAgICAgIGVsZW1lbnRfaW5mby5iZ19zZXQgPSBtYXRjaGVzLm1hcChtID0+IG1bMV0gPyB7c3JjOiBtWzFdLnRyaW0oKSArIChtWzJdID8gXCIgXCIgKyBtWzJdLnRyaW0oKSA6IFwiXCIpfSA6IHt9KTtcbiAgICAgICAgICAgIC8vIENoZWNrIGlmIGJnX3NldCBhcnJheSBpcyBwb3B1bGF0ZWQgd2l0aCBlbXB0eSBvYmplY3RzXG4gICAgICAgICAgICBpZiAoZWxlbWVudF9pbmZvLmJnX3NldC5ldmVyeShpdGVtID0+IGl0ZW0uc3JjID09PSBcIlwiKSkge1xuICAgICAgICAgICAgICAgIC8vIElmIGJnX3NldCBhcnJheSBpcyBwb3B1bGF0ZWQgd2l0aCBlbXB0eSBvYmplY3RzLCBwb3B1bGF0ZSBpdCB3aXRoIHRoZSBVUkxzIGZyb20gdGhlIG1hdGNoZXMgYXJyYXlcbiAgICAgICAgICAgICAgICBlbGVtZW50X2luZm8uYmdfc2V0ID0gbWF0Y2hlcy5tYXAobSA9PiBtWzFdID8ge3NyYzogbVsxXS50cmltKCl9IDoge30pO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBpZiAoZWxlbWVudF9pbmZvLmJnX3NldC5sZW5ndGggPiAwKSB7XG4gICAgICAgICAgICAgICAgZWxlbWVudF9pbmZvLnNyYyA9IGVsZW1lbnRfaW5mby5iZ19zZXRbMF0uc3JjO1xuICAgICAgICAgICAgICAgIGlmIChlbGVtZW50X2luZm8udHlwZSA9PT0gXCJiZy1pbWctc2V0XCIpIHtcbiAgICAgICAgICAgICAgICAgICAgZWxlbWVudF9pbmZvLnNyYyA9IGVsZW1lbnRfaW5mby5iZ19zZXQ7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIGVsZW1lbnRfaW5mbztcbiAgICB9XG5cbiAgICBfaW5pdFdpdGhGaXJzdEVsZW1lbnRXaXRoSW5mbyhlbGVtZW50cykge1xuICAgICAgICBjb25zdCBmaXJzdEVsZW1lbnRXaXRoSW5mbyA9IGVsZW1lbnRzLmZpbmQoaXRlbSA9PiBpdGVtLmVsZW1lbnRJbmZvICE9PSBudWxsKTtcblxuICAgICAgICBpZiAoICEgZmlyc3RFbGVtZW50V2l0aEluZm8gKSB7XG4gICAgICAgICAgICB0aGlzLl9sb2dNZXNzYWdlKFwiTm8gTENQIGNhbmRpZGF0ZSBmb3VuZC5cIik7XG4gICAgICAgICAgICB0aGlzLnBlcmZvcm1hbmNlSW1hZ2VzID0gW107XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLnBlcmZvcm1hbmNlSW1hZ2VzID0gW3tcbiAgICAgICAgICAgIC4uLmZpcnN0RWxlbWVudFdpdGhJbmZvLmVsZW1lbnRJbmZvLFxuICAgICAgICAgICAgbGFiZWw6IFwibGNwXCIsXG4gICAgICAgIH1dO1xuICAgIH1cblxuICAgIF9maWxsQVRGV2l0aG91dER1cGxpY2F0aW9ucyhlbGVtZW50cykge1xuICAgICAgICBlbGVtZW50cy5mb3JFYWNoKCh7IGVsZW1lbnQsIGVsZW1lbnRJbmZvIH0pID0+IHtcbiAgICAgICAgICAgIGlmICggdGhpcy5faXNEdXBsaWNhdGVJbWFnZShlbGVtZW50KSB8fCAhZWxlbWVudEluZm8gKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICB0aGlzLnBlcmZvcm1hbmNlSW1hZ2VzLnB1c2goeyAuLi5lbGVtZW50SW5mbywgbGFiZWw6IFwiYWJvdmUtdGhlLWZvbGRcIiB9KTtcbiAgICAgICAgfSk7XG4gICAgfVxuXG4gICAgX2lzRHVwbGljYXRlSW1hZ2UoaW1hZ2UpIHtcbiAgICAgICAgY29uc3QgZWxlbWVudEluZm8gPSB0aGlzLl9nZXRFbGVtZW50SW5mbyhpbWFnZSk7XG5cbiAgICAgICAgaWYgKGVsZW1lbnRJbmZvID09PSBudWxsKSB7XG4gICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgIH1cblxuICAgICAgICBjb25zdCBpc0ltYWdlT3JWaWRlbyA9XG4gICAgICAgICAgICBlbGVtZW50SW5mby50eXBlID09PSBcImltZ1wiIHx8XG4gICAgICAgICAgICBlbGVtZW50SW5mby50eXBlID09PSBcImltZy1zcmNzZXRcIiB8fFxuICAgICAgICAgICAgZWxlbWVudEluZm8udHlwZSA9PT0gXCJ2aWRlb1wiO1xuXG4gICAgICAgIGNvbnN0IGlzQmdJbWFnZU9yUGljdHVyZSA9XG4gICAgICAgICAgICBlbGVtZW50SW5mby50eXBlID09PSBcImJnLWltZ1wiIHx8XG4gICAgICAgICAgICBlbGVtZW50SW5mby50eXBlID09PSBcImJnLWltZy1zZXRcIiB8fFxuICAgICAgICAgICAgZWxlbWVudEluZm8udHlwZSA9PT0gXCJwaWN0dXJlXCI7XG5cbiAgICAgICAgcmV0dXJuIChpc0ltYWdlT3JWaWRlbyB8fCBpc0JnSW1hZ2VPclBpY3R1cmUpXG4gICAgICAgICAgICAmJlxuICAgICAgICAgICAgdGhpcy5wZXJmb3JtYW5jZUltYWdlcy5zb21lKGl0ZW0gPT4gaXRlbS5zcmMgPT09IGVsZW1lbnRJbmZvLnNyYyk7XG4gICAgfVxuXG4gICAgX2dldEZpbmFsU3RhdHVzKCkge1xuICAgICAgICBpZiAoICcnICE9PSB0aGlzLmVycm9yQ29kZSApIHtcbiAgICAgICAgICAgIHJldHVybiB0aGlzLmVycm9yQ29kZTtcbiAgICAgICAgfVxuXG4gICAgICAgIGNvbnN0IHNjcmlwdFRpbWUgPSAoIG5ldyBEYXRlKCkgLSB0aGlzLnNjcmlwdFRpbWVyICkgLyAxMDAwO1xuICAgICAgICBpZiAoIDEwIDw9IHNjcmlwdFRpbWUgKSB7XG4gICAgICAgICAgICByZXR1cm4gJ3RpbWVvdXQnO1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuICdzdWNjZXNzJztcbiAgICB9XG5cbiAgICBfc2F2ZUZpbmFsUmVzdWx0SW50b0RCKCkge1xuICAgICAgICBjb25zdCBkYXRhID0gbmV3IEZvcm1EYXRhKCk7XG4gICAgICAgIGRhdGEuYXBwZW5kKCdhY3Rpb24nLCAncm9ja2V0X2xjcCcpO1xuICAgICAgICBkYXRhLmFwcGVuZCgncm9ja2V0X2xjcF9ub25jZScsIHRoaXMuY29uZmlnLm5vbmNlKTtcbiAgICAgICAgZGF0YS5hcHBlbmQoJ3VybCcsIHRoaXMuY29uZmlnLnVybCk7XG4gICAgICAgIGRhdGEuYXBwZW5kKCdpc19tb2JpbGUnLCB0aGlzLmNvbmZpZy5pc19tb2JpbGUpO1xuICAgICAgICBkYXRhLmFwcGVuZCgnaW1hZ2VzJywgSlNPTi5zdHJpbmdpZnkodGhpcy5wZXJmb3JtYW5jZUltYWdlcykpO1xuICAgICAgICBkYXRhLmFwcGVuZCgnc3RhdHVzJywgdGhpcy5fZ2V0RmluYWxTdGF0dXMoKSk7XG5cbiAgICAgICAgZmV0Y2godGhpcy5jb25maWcuYWpheF91cmwsIHtcbiAgICAgICAgICAgIG1ldGhvZDogXCJQT1NUXCIsXG4gICAgICAgICAgICBjcmVkZW50aWFsczogJ3NhbWUtb3JpZ2luJyxcbiAgICAgICAgICAgIGJvZHk6IGRhdGEsXG4gICAgICAgICAgICBoZWFkZXJzOiB7XG4gICAgICAgICAgICAgICAgJ3dwci1zYWFzLW5vLWludGVyY2VwdCc6ICB0cnVlXG4gICAgICAgICAgICB9XG4gICAgICAgIH0pXG4gICAgICAgICAgICAudGhlbigocmVzcG9uc2UpID0+IHJlc3BvbnNlLmpzb24oKSlcbiAgICAgICAgICAgIC50aGVuKChkYXRhKSA9PiB7XG4gICAgICAgICAgICAgICAgdGhpcy5fbG9nTWVzc2FnZShkYXRhKTtcbiAgICAgICAgICAgIH0pXG4gICAgICAgICAgICAuY2F0Y2goKGVycm9yKSA9PiB7XG4gICAgICAgICAgICAgICAgdGhpcy5fbG9nTWVzc2FnZShlcnJvcik7XG4gICAgICAgICAgICB9KVxuICAgICAgICAgICAgLmZpbmFsbHkoKCkgPT4ge1xuICAgICAgICAgICAgICAgIHRoaXMuX2ZpbmFsaXplKCk7XG4gICAgICAgICAgICB9KTtcbiAgICB9XG5cbiAgICBfaGFuZGxlSW5maW5pdGVMb29wKCkge1xuICAgICAgICB0aGlzLl9zYXZlRmluYWxSZXN1bHRJbnRvREIoKTtcbiAgICB9XG5cbiAgICBfZmluYWxpemUoKSB7XG4gICAgICAgIGNvbnN0IGJlYWNvbnNjcmlwdCA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJ1tkYXRhLW5hbWU9XCJ3cHItbGNwLWJlYWNvblwiXScpO1xuICAgICAgICBiZWFjb25zY3JpcHQuc2V0QXR0cmlidXRlKCdiZWFjb24tY29tcGxldGVkJywgJ3RydWUnKTtcbiAgICAgICAgY2xlYXJUaW1lb3V0KCB0aGlzLmluZmluaXRlTG9vcElkICk7XG4gICAgfVxuXG4gICAgX2xvZ01lc3NhZ2UoIG1zZyApIHtcbiAgICAgICAgaWYgKCAhIHRoaXMuY29uZmlnLmRlYnVnICkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG4gICAgICAgIGNvbnNvbGUubG9nKCBtc2cgKTtcbiAgICB9XG59XG5cbm1vZHVsZS5leHBvcnRzID0gTGNwQmVhY29uO1xuIiwiaW1wb3J0IExjcEJlYWNvbiBmcm9tICdyb2NrZXQtc2NyaXB0cyc7XG5cbiggcm9ja2V0X2xjcF9kYXRhID0+IHtcblx0aWYgKCAhcm9ja2V0X2xjcF9kYXRhICkge1xuXHRcdHJldHVybjtcblx0fVxuXG5cdGNvbnN0IGluc3RhbmNlID0gbmV3IExjcEJlYWNvbiggcm9ja2V0X2xjcF9kYXRhICk7XG5cblx0aWYgKGRvY3VtZW50LnJlYWR5U3RhdGUgIT09ICdsb2FkaW5nJykge1xuXHRcdHNldFRpbWVvdXQoKCkgPT4ge1xuXHRcdFx0aW5zdGFuY2UuaW5pdCgpO1xuXHRcdH0sIHJvY2tldF9sY3BfZGF0YS5kZWxheSk7XG5cdFx0cmV0dXJuO1xuXHR9XG5cblx0ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcihcIkRPTUNvbnRlbnRMb2FkZWRcIiwgKCkgPT4ge1xuXHRcdHNldFRpbWVvdXQoKCkgPT4ge1xuXHRcdFx0aW5zdGFuY2UuaW5pdCgpO1xuXHRcdH0sIHJvY2tldF9sY3BfZGF0YS5kZWxheSk7XG5cdH0pO1xufSApKCB3aW5kb3cucm9ja2V0X2xjcF9kYXRhICk7Il19
