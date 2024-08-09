(() => {
  // src/Utils.js
  var BeaconUtils = class {
    static isNotValidScreensize(is_mobile, threshold) {
      const screenWidth = window.innerWidth || document.documentElement.clientWidth;
      const screenHeight = window.innerHeight || document.documentElement.clientHeight;
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
  };
  var Logger_default = Logger;

  // src/BeaconManager.js
  var BeaconManager = class {
    constructor(config) {
      this.config = config;
      this.lcpBeacon = null;
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
      const shouldGenerateLcp = this.config.status.atf && isGeneratedBefore === false;
      if (shouldGenerateLcp) {
        this.lcpBeacon = new BeaconLcp_default(this.config, this.logger);
        await this.lcpBeacon.run();
      } else {
        this.logger.logMessage("Not running BeaconLcp because data is already available");
      }
      this._saveFinalResultIntoDB();
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
      return beacon_data_response.success;
    }
    _saveFinalResultIntoDB() {
      const results = {
        lcp: this.lcpBeacon ? this.lcpBeacon.getResults() : null
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
        this.logger.logMessage(data2);
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
})();
