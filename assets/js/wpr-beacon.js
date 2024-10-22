(() => {
  // src/Utils.js
  var BeaconUtils = class {
    static getScreenWidth() {
      return window.innerWidth || document.documentElement.clientWidth;
    }
    static getScreenHeight() {
      return window.innerHeight || document.documentElement.clientHeight;
    }
    static isNotValidScreensize(is_mobile, threshold) {
      const screenWidth = this.getScreenWidth();
      const screenHeight = this.getScreenHeight();
      const isNotValidForMobile = is_mobile && (screenWidth > threshold.width || screenHeight > threshold.height);
      const isNotValidForDesktop = !is_mobile && (screenWidth < threshold.width || screenHeight < threshold.height);
      return isNotValidForMobile || isNotValidForDesktop;
    }
    static isPageCached() {
      const signature = document.documentElement.nextSibling && document.documentElement.nextSibling.data ? document.documentElement.nextSibling.data : "";
      return signature && signature.includes("Debug: cached");
    }
    static isIntersecting(rect) {
      return rect.bottom >= 0 && rect.right >= 0 && rect.top <= (window.innerHeight || document.documentElement.clientHeight) && rect.left <= (window.innerWidth || document.documentElement.clientWidth);
    }
  };
  var Utils_default = BeaconUtils;

  // src/BeaconLcp.js
  var BeaconLcp = class {
    constructor(config, logger) {
      this.config = config;
      this.performanceImages = [];
      this.logger = logger;
    }
    async run() {
      try {
        const above_the_fold_images = this._generateLcpCandidates(Infinity);
        if (above_the_fold_images) {
          this._initWithFirstElementWithInfo(above_the_fold_images);
          this._fillATFWithoutDuplications(above_the_fold_images);
        }
      } catch (err) {
        this.errorCode = "script_error";
        this.logger.logMessage("Script Error: " + err);
      }
    }
    _generateLcpCandidates(count) {
      const lcpElements = document.querySelectorAll(this.config.elements);
      if (lcpElements.length <= 0) {
        return [];
      }
      const potentialCandidates = Array.from(lcpElements);
      const topCandidates = potentialCandidates.map((element) => {
        if ("img" === element.nodeName.toLowerCase() && "picture" === element.parentElement.nodeName.toLowerCase()) {
          return null;
        }
        let rect;
        if ("picture" === element.nodeName.toLowerCase()) {
          const imgElement = element.querySelector("img");
          if (imgElement) {
            rect = imgElement.getBoundingClientRect();
          } else {
            return null;
          }
        } else {
          rect = element.getBoundingClientRect();
        }
        return {
          element,
          rect
        };
      }).filter((item) => item !== null).filter((item) => {
        return item.rect.width > 0 && item.rect.height > 0 && Utils_default.isIntersecting(item.rect);
      }).map((item) => ({
        item,
        area: this._getElementArea(item.rect),
        elementInfo: this._getElementInfo(item.element)
      })).sort((a, b) => b.area - a.area).slice(0, count);
      return topCandidates.map((candidate) => ({
        element: candidate.item.element,
        elementInfo: candidate.elementInfo
      }));
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
        element_info.srcset = element.srcset;
        element_info.sizes = element.sizes;
        element_info.current_src = element.currentSrc;
      } else if (nodeName === "img") {
        element_info.type = "img";
        element_info.src = element.src;
        element_info.current_src = element.currentSrc;
      } else if (nodeName === "video") {
        element_info.type = "img";
        const source = element.querySelector("source");
        element_info.src = element.poster || (source ? source.src : "");
        element_info.current_src = element_info.src;
      } else if (nodeName === "svg") {
        const imageElement = element.querySelector("image");
        if (imageElement) {
          element_info.type = "img";
          element_info.src = imageElement.getAttribute("href") || "";
          element_info.current_src = element_info.src;
        }
      } else if (nodeName === "picture") {
        element_info.type = "picture";
        const img = element.querySelector("img");
        element_info.src = img ? img.src : "";
        element_info.sources = Array.from(element.querySelectorAll("source")).map((source) => ({
          srcset: source.srcset || "",
          media: source.media || "",
          type: source.type || "",
          sizes: source.sizes || ""
        }));
      } else {
        const computed_style = window.getComputedStyle(element, null);
        const bg_props = [
          computed_style.getPropertyValue("background-image"),
          getComputedStyle(element, ":after").getPropertyValue("background-image"),
          getComputedStyle(element, ":before").getPropertyValue("background-image")
        ].filter((prop) => prop !== "none");
        if (bg_props.length === 0) {
          return null;
        }
        const full_bg_prop = bg_props[0];
        element_info.type = "bg-img";
        if (full_bg_prop.includes("image-set(")) {
          element_info.type = "bg-img-set";
        }
        if (!full_bg_prop || full_bg_prop === "" || full_bg_prop.includes("data:image")) {
          return null;
        }
        const matches = [...full_bg_prop.matchAll(css_bg_url_rgx)];
        element_info.bg_set = matches.map((m) => m[1] ? { src: m[1].trim() + (m[2] ? " " + m[2].trim() : "") } : {});
        if (element_info.bg_set.every((item) => item.src === "")) {
          element_info.bg_set = matches.map((m) => m[1] ? { src: m[1].trim() } : {});
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
      const firstElementWithInfo = elements.find((item) => item.elementInfo !== null);
      if (!firstElementWithInfo) {
        this.logger.logMessage("No LCP candidate found.");
        this.performanceImages = [];
        return;
      }
      this.performanceImages = [{
        ...firstElementWithInfo.elementInfo,
        label: "lcp"
      }];
    }
    _fillATFWithoutDuplications(elements) {
      elements.forEach(({ element, elementInfo }) => {
        if (this._isDuplicateImage(element) || !elementInfo) {
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
      const isImageOrVideo = elementInfo.type === "img" || elementInfo.type === "img-srcset" || elementInfo.type === "video";
      const isBgImageOrPicture = elementInfo.type === "bg-img" || elementInfo.type === "bg-img-set" || elementInfo.type === "picture";
      return (isImageOrVideo || isBgImageOrPicture) && this.performanceImages.some((item) => item.src === elementInfo.src);
    }
    getResults() {
      return this.performanceImages;
    }
  };
  var BeaconLcp_default = BeaconLcp;

  // src/BeaconLrc.js
  var BeaconLrc = class {
    constructor(config, logger) {
      this.config = config;
      this.logger = logger;
      this.lazyRenderElements = [];
    }
    async run() {
      try {
        const elementsInView = this._getLazyRenderElements();
        if (elementsInView) {
          this._processElements(elementsInView);
        }
      } catch (err) {
        this.errorCode = "script_error";
        this.logger.logMessage("Script Error: " + err);
      }
    }
    _getLazyRenderElements() {
      const elements = document.querySelectorAll("[data-rocket-location-hash]");
      if (elements.length <= 0) {
        return [];
      }
      const validElements = Array.from(elements).filter((element) => !this._skipElement(element));
      return validElements.map((element) => ({
        element,
        depth: this._getElementDepth(element),
        distance: this._getElementDistance(element),
        hash: this._getLocationHash(element)
      }));
    }
    _getElementDepth(element) {
      let depth = 0;
      let parent = element.parentElement;
      while (parent) {
        depth++;
        parent = parent.parentElement;
      }
      return depth;
    }
    _getElementDistance(element) {
      const rect = element.getBoundingClientRect();
      const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
      return Math.max(0, rect.top + scrollTop - Utils_default.getScreenHeight());
    }
    _skipElement(element) {
      const skipStrings = this.config.skipStrings || ["memex"];
      if (!element || !element.id) return false;
      return skipStrings.some((str) => element.id.toLowerCase().includes(str.toLowerCase()));
    }
    _shouldSkipElement(element, exclusions) {
      if (!element) return false;
      for (let i = 0; i < exclusions.length; i++) {
        const [attribute, pattern] = exclusions[i];
        const attributeValue = element.getAttribute(attribute);
        if (attributeValue && new RegExp(pattern, "i").test(attributeValue)) {
          return true;
        }
      }
      return false;
    }
    _checkLcrConflict(element) {
      const conflictingElements = [];
      const computedStyle = window.getComputedStyle(element);
      const validMargins = ["marginTop", "marginRight", "marginBottom", "marginLeft"];
      const negativeMargins = validMargins.some((margin) => parseFloat(computedStyle[margin]) < 0);
      const currentElementConflicts = negativeMargins || computedStyle.contentVisibility === "auto" || computedStyle.contentVisibility === "hidden";
      if (currentElementConflicts) {
        conflictingElements.push({
          element,
          conflicts: [
            negativeMargins && "negative margin",
            computedStyle.contentVisibility === "auto" && "content-visibility:auto",
            computedStyle.contentVisibility === "hidden" && "content-visibility:hidden"
          ].filter(Boolean)
        });
      }
      Array.from(element.children).forEach((child) => {
        const childStyle = window.getComputedStyle(child);
        const validMargins2 = ["marginTop", "marginRight", "marginBottom", "marginLeft"];
        const childNegativeMargins = validMargins2.some((margin) => parseFloat(childStyle[margin]) < 0);
        const childConflicts = childNegativeMargins || childStyle.position === "absolute" || childStyle.position === "fixed";
        if (childConflicts) {
          conflictingElements.push({
            element: child,
            conflicts: [
              childNegativeMargins && "negative margin",
              childStyle.position === "absolute" && "position:absolute",
              childStyle.position === "fixed" && "position:fixed"
            ].filter(Boolean)
          });
        }
      });
      return conflictingElements;
    }
    _processElements(elements) {
      elements.forEach(({ element, depth, distance, hash }) => {
        if (this._shouldSkipElement(element, this.config.exclusions || [])) {
          return;
        }
        if ("No hash detected" === hash) {
          return;
        }
        const conflicts = this._checkLcrConflict(element);
        if (conflicts.length > 0) {
          this.logger.logMessage("Skipping element due to conflicts:", conflicts);
          return;
        }
        const can_push_hash = element.parentElement && this._getElementDistance(element.parentElement) < this.config.lrc_threshold && distance >= this.config.lrc_threshold;
        const color = can_push_hash ? "green" : distance === 0 ? "red" : "";
        this.logger.logColoredMessage(`${"	".repeat(depth)}${element.tagName} (Depth: ${depth}, Distance from viewport bottom: ${distance}px)`, color);
        this.logger.logColoredMessage(`${"	".repeat(depth)}Location hash: ${hash}`, color);
        this.logger.logColoredMessage(`${"	".repeat(depth)}Dimensions Client Height: ${element.clientHeight}`, color);
        if (can_push_hash) {
          this.lazyRenderElements.push(hash);
          this.logger.logMessage(`Element pushed with hash: ${hash}`);
        }
      });
    }
    _getXPath(element) {
      if (element && element.id !== "") {
        return `//*[@id="${element.id}"]`;
      }
      return this._getElementXPath(element);
    }
    _getElementXPath(element) {
      if (element === document.body) {
        return "/html/body";
      }
      const position = this._getElementPosition(element);
      return `${this._getElementXPath(element.parentNode)}/${element.nodeName.toLowerCase()}[${position}]`;
    }
    _getElementPosition(element) {
      let pos = 1;
      let sibling = element.previousElementSibling;
      while (sibling) {
        if (sibling.nodeName === element.nodeName) {
          pos++;
        }
        sibling = sibling.previousElementSibling;
      }
      return pos;
    }
    _getLocationHash(element) {
      return element.hasAttribute("data-rocket-location-hash") ? element.getAttribute("data-rocket-location-hash") : "No hash detected";
    }
    getResults() {
      return this.lazyRenderElements;
    }
  };
  var BeaconLrc_default = BeaconLrc;

  // src/Logger.js
  var Logger = class {
    constructor(enabled) {
      this.enabled = enabled;
    }
    logMessage(msg) {
      if (!this.enabled) {
        return;
      }
      console.log(msg);
    }
    logColoredMessage(msg, color = "green") {
      if (!this.enabled) {
        return;
      }
      console.log(`%c${msg}`, `color: ${color};`);
    }
  };
  var Logger_default = Logger;

  // src/BeaconManager.js
  var BeaconManager = class {
    constructor(config) {
      this.config = config;
      this.lcpBeacon = null;
      this.lrcBeacon = null;
      this.infiniteLoopId = null;
      this.errorCode = "";
      this.logger = new Logger_default(this.config.debug);
    }
    async init() {
      this.scriptTimer = /* @__PURE__ */ new Date();
      if (!await this._isValidPreconditions()) {
        this._finalize();
        return;
      }
      this.infiniteLoopId = setTimeout(() => {
        this._handleInfiniteLoop();
      }, 1e4);
      const isGeneratedBefore = await this._getGeneratedBefore();
      const shouldGenerateLcp = this.config.status.atf && (isGeneratedBefore === false || isGeneratedBefore.lcp === false);
      const shouldGeneratelrc = this.config.status.lrc && (isGeneratedBefore === false || isGeneratedBefore.lrc === false);
      if (shouldGenerateLcp) {
        this.lcpBeacon = new BeaconLcp_default(this.config, this.logger);
        await this.lcpBeacon.run();
      } else {
        this.logger.logMessage("Not running BeaconLcp because data is already available or feature is disabled");
      }
      if (shouldGeneratelrc) {
        this.lrcBeacon = new BeaconLrc_default(this.config, this.logger);
        await this.lrcBeacon.run();
      } else {
        this.logger.logMessage("Not running BeaconLrc because data is already available or feature is disabled");
      }
      if (shouldGenerateLcp || shouldGeneratelrc) {
        this._saveFinalResultIntoDB();
      } else {
        this.logger.logMessage("Not saving results into DB as no beacon features ran.");
        this._finalize();
      }
    }
    async _isValidPreconditions() {
      const threshold = {
        width: this.config.width_threshold,
        height: this.config.height_threshold
      };
      if (Utils_default.isNotValidScreensize(this.config.is_mobile, threshold)) {
        this.logger.logMessage("Bailing out because screen size is not acceptable");
        return false;
      }
      return true;
    }
    async _getGeneratedBefore() {
      if (!Utils_default.isPageCached()) {
        return false;
      }
      let data_check = new FormData();
      data_check.append("action", "rocket_check_beacon");
      data_check.append("rocket_beacon_nonce", this.config.nonce);
      data_check.append("url", this.config.url);
      data_check.append("is_mobile", this.config.is_mobile);
      const beacon_data_response = await fetch(this.config.ajax_url, {
        method: "POST",
        credentials: "same-origin",
        body: data_check
      }).then((data) => data.json());
      return beacon_data_response.data;
    }
    _saveFinalResultIntoDB() {
      const results = {
        lcp: this.lcpBeacon ? this.lcpBeacon.getResults() : null,
        lrc: this.lrcBeacon ? this.lrcBeacon.getResults() : null
      };
      const data = new FormData();
      data.append("action", "rocket_beacon");
      data.append("rocket_beacon_nonce", this.config.nonce);
      data.append("url", this.config.url);
      data.append("is_mobile", this.config.is_mobile);
      data.append("status", this._getFinalStatus());
      data.append("results", JSON.stringify(results));
      fetch(this.config.ajax_url, {
        method: "POST",
        credentials: "same-origin",
        body: data,
        headers: {
          "wpr-saas-no-intercept": true
        }
      }).then((response) => response.json()).then((data2) => {
        this.logger.logMessage(data2.data.lcp);
      }).catch((error) => {
        this.logger.logMessage(error);
      }).finally(() => {
        this._finalize();
      });
    }
    _getFinalStatus() {
      if ("" !== this.errorCode) {
        return this.errorCode;
      }
      const scriptTime = (/* @__PURE__ */ new Date() - this.scriptTimer) / 1e3;
      if (10 <= scriptTime) {
        return "timeout";
      }
      return "success";
    }
    _handleInfiniteLoop() {
      this._saveFinalResultIntoDB();
    }
    _finalize() {
      const beaconscript = document.querySelector('[data-name="wpr-wpr-beacon"]');
      beaconscript.setAttribute("beacon-completed", "true");
      clearTimeout(this.infiniteLoopId);
    }
  };
  var BeaconManager_default = BeaconManager;

  // src/BeaconEntryPoint.js
  ((rocket_beacon_data) => {
    if (!rocket_beacon_data) {
      return;
    }
    const instance = new BeaconManager_default(rocket_beacon_data);
    if (document.readyState !== "loading") {
      setTimeout(() => {
        instance.init();
      }, rocket_beacon_data.delay);
      return;
    }
    document.addEventListener("DOMContentLoaded", () => {
      setTimeout(() => {
        instance.init();
      }, rocket_beacon_data.delay);
    });
  })(window.rocket_beacon_data);
  var BeaconEntryPoint_default = BeaconManager_default;
})();
