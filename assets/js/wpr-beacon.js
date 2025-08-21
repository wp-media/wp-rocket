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
    static isPageScrolled() {
      return window.pageYOffset > 0 || document.documentElement.scrollTop > 0;
    }
    /**
     * Checks if an element is visible in the viewport.
     * 
     * This method checks if the provided element is visible in the viewport by
     * considering its display, visibility, opacity, width, and height properties.
     * It also excludes elements with transparent text properties.
     * It returns true if the element is visible, and false otherwise.
     * 
     * @param {Element} element - The element to check for visibility.
     * @returns {boolean} True if the element is visible, false otherwise.
     */
    static isElementVisible(element) {
      const style = window.getComputedStyle(element);
      const rect = element.getBoundingClientRect();
      if (!style) {
        return false;
      }
      if (this.hasTransparentText(element)) {
        return false;
      }
      return !(style.display === "none" || style.visibility === "hidden" || style.opacity === "0" || rect.width === 0 || rect.height === 0);
    }
    /**
     * Checks if an element has transparent text properties.
     *
     * This method checks for specific CSS properties that make text invisible,
     * such as `color: transparent`, `color: rgba(..., 0)`, `color: hsla(..., 0)`,
     * `color: #...00` (8-digit hex with alpha = 0), and `filter: opacity(0)`.
     *
     * @param {Element} element - The element to check.
     * @returns {boolean} True if the element has transparent text properties, false otherwise.
     */
    static hasTransparentText(element) {
      const style = window.getComputedStyle(element);
      if (!style) {
        return false;
      }
      const color = style.color || "";
      const filter = style.filter || "";
      if (color === "transparent") {
        return true;
      }
      const rgbaMatch = color.match(/rgba\(\d+,\s*\d+,\s*\d+,\s*0\)/);
      if (rgbaMatch) {
        return true;
      }
      const hslaMatch = color.match(/hsla\(\d+,\s*\d+%,\s*\d+%,\s*0\)/);
      if (hslaMatch) {
        return true;
      }
      const hexMatch = color.match(/#[0-9a-fA-F]{6}00/);
      if (hexMatch) {
        return true;
      }
      if (filter.includes("opacity(0)")) {
        return true;
      }
      return false;
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
        return item.rect.width > 0 && item.rect.height > 0 && Utils_default.isIntersecting(item.rect) && Utils_default.isElementVisible(item.element);
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
        if (element_info.bg_set.length <= 0) {
          return null;
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
      const firstElementWithInfo = elements.find((item) => {
        return item.elementInfo !== null && (item.elementInfo.src || item.elementInfo.srcset);
      });
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
      const svgUseTargets = this._getSvgUseTargets();
      if (elements.length <= 0) {
        return [];
      }
      const validElements = Array.from(elements).filter((element) => {
        if (this._skipElement(element)) {
          return false;
        }
        if (svgUseTargets.includes(element)) {
          this.logger.logColoredMessage(`Element skipped because of SVG: ${element.tagName}`, "orange");
          return false;
        }
        return true;
      });
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
    _getSvgUseTargets() {
      const useElements = document.querySelectorAll("use");
      const targets = /* @__PURE__ */ new Set();
      useElements.forEach((use) => {
        let parent = use.parentElement;
        while (parent && parent !== document.body) {
          targets.add(parent);
          parent = parent.parentElement;
        }
      });
      return Array.from(targets);
    }
    getResults() {
      return this.lazyRenderElements;
    }
  };
  var BeaconLrc_default = BeaconLrc;

  // src/BeaconPreloadFonts.js
  var BeaconPreloadFonts = class {
    constructor(config, logger) {
      this.config = config;
      this.logger = logger;
      this.aboveTheFoldFonts = [];
      const extensions = (Array.isArray(this.config.processed_extensions) && this.config.processed_extensions.length > 0 ? this.config.processed_extensions : ["woff", "woff2", "ttf"]).map((ext) => ext.replace(/[.*+?^${}()|[\]\\]/g, "\\$&")).join("|");
      this.FONT_FILE_REGEX = new RegExp(`\\.(${extensions})(\\?.*)?$`, "i");
      this.EXCLUDED_TAG_NAMES = /* @__PURE__ */ new Set([
        // Metadata/document head
        "BASE",
        "HEAD",
        "LINK",
        "META",
        "STYLE",
        "TITLE",
        "SCRIPT",
        // Media
        "IMG",
        "VIDEO",
        "AUDIO",
        "EMBED",
        "OBJECT",
        "IFRAME",
        // Templating, wrappers, components, fallback
        "NOSCRIPT",
        "TEMPLATE",
        "SLOT",
        "CANVAS",
        // Resources
        "SOURCE",
        "TRACK",
        "PARAM",
        // SVG references
        "USE",
        "SYMBOL",
        // Layout work
        "BR",
        "HR",
        "WBR",
        // Obsolete/deprecated
        "APPLET",
        "ACRONYM",
        "BGSOUND",
        "BIG",
        "BLINK",
        "CENTER",
        "FONT",
        "FRAME",
        "FRAMESET",
        "MARQUEE",
        "NOFRAMES",
        "STRIKE",
        "TT",
        "U",
        "XMP"
      ]);
    }
    /**
     * Checks if a URL should be excluded from external font processing based on domain exclusions.
     * 
     * @param {string} url - The URL to check.
     * @returns {boolean} True if the URL should be excluded, false otherwise.
     */
    isUrlExcludedFromExternalProcessing(url) {
      if (!url) return false;
      const externalFontExclusions = this.config.external_font_exclusions || [];
      const preloadFontsExclusions = this.config.preload_fonts_exclusions || [];
      const allExclusions = [...externalFontExclusions, ...preloadFontsExclusions];
      return allExclusions.some((exclusion) => url.includes(exclusion));
    }
    /**
     * Checks if a font family or URL should be excluded from preloading.
     * 
     * This method determines if the provided font family or any of its URLs
     * match any exclusion patterns defined in the configuration. It checks for
     * exact matches and substring matches for both the font family and URLs.
     * 
     * @param {string} fontFamily - The font family to check.
     * @param {string[]} urls - Array of font file URLs to check.
     * @returns {boolean} True if the font should be excluded, false otherwise.
     */
    isExcluded(fontFamily, urls) {
      const exclusions = this.config.preload_fonts_exclusions;
      const exclusionsSet = new Set(exclusions);
      if (exclusionsSet.has(fontFamily)) {
        return true;
      }
      if (exclusions.some((exclusion) => fontFamily.includes(exclusion))) {
        return true;
      }
      if (Array.isArray(urls) && urls.length > 0) {
        if (urls.some((url) => exclusionsSet.has(url))) {
          return true;
        }
        if (urls.some(
          (url) => exclusions.some((exclusion) => url.includes(exclusion))
        )) {
          return true;
        }
      }
      return false;
    }
    /**
     * Checks if an element can be styled with font-family.
     * 
     * This method determines if the provided element's tag name is not in the list
     * of excluded tag names that cannot be styled with font-family CSS property.
     * 
     * @param {Element} element - The element to check.
     * @returns {boolean} True if the element can be styled with font-family, false otherwise.
     */
    canElementBeStyledWithFontFamily(element) {
      return !this.EXCLUDED_TAG_NAMES.has(element.tagName);
    }
    /**
     * Checks if an element is visible in the viewport.
     * 
     * This method delegates to BeaconUtils.isElementVisible() for consistent
     * visibility checking across all beacons.
     * 
     * @param {Element} element - The element to check for visibility.
     * @returns {boolean} True if the element is visible, false otherwise.
     */
    isElementVisible(element) {
      return Utils_default.isElementVisible(element);
    }
    /**
     * Cleans a URL by removing query parameters and fragments.
     * 
     * This method takes a URL as input, removes any query parameters and fragments,
     * and returns the cleaned URL.
     * 
     * @param {string} url - The URL to clean.
     * @returns {string} The cleaned URL.
     */
    cleanUrl(url) {
      try {
        url = url.split("?")[0].split("#")[0];
        return new URL(url, window.location.href).href;
      } catch (e) {
        return url;
      }
    }
    /**
     * Fetches external stylesheet links from known font providers, retrieves their CSS,
     * parses them into in-memory CSSStyleSheet objects, and extracts font-family/font-face
     * information into a structured object.
     *
     * @async
     * @function externalStylesheetsDoc
     * @returns {Promise<{styleSheets: CSSStyleSheet[], fontPairs: Object}>} An object containing:
     *   - styleSheets: Array of parsed CSSStyleSheet objects (not attached to the DOM).
     *   - fontPairs: An object mapping font URLs to arrays of font variation objects
     *     ({family, weight, style}).
     *
     * @example
     * const { styleSheets, fontPairs } = await externalStylesheetsDoc();
     * this.logger.logMessage(fontPairs);
     */
    async externalStylesheetsDoc() {
      function generateFontPairsFromStyleSheets(styleSheetsArray) {
        const fontPairs = {};
        function _extractFirstUrlFromSrc(srcValue) {
          if (!srcValue) return null;
          const urlMatch = srcValue.match(/url\s*\(\s*(['"]?)(.+?)\1\s*\)/);
          return urlMatch ? urlMatch[2] : null;
        }
        function _cleanFontFamilyName(fontFamilyValue) {
          if (!fontFamilyValue) return "";
          return fontFamilyValue.replace(/^['"]+|['"]+$/g, "").trim();
        }
        if (!styleSheetsArray || !Array.isArray(styleSheetsArray)) {
          console.warn(
            "generateFontPairsFromStyleSheets: Input is not a valid array. Received:",
            styleSheetsArray
          );
          return fontPairs;
        }
        if (styleSheetsArray.length === 0) {
          return fontPairs;
        }
        styleSheetsArray.forEach((sheet) => {
          if (sheet && sheet.cssRules) {
            try {
              for (const rule of sheet.cssRules) {
                if (rule.type === CSSRule.FONT_FACE_RULE) {
                  const cssFontFaceRule = rule;
                  const fontFamily = _cleanFontFamilyName(
                    cssFontFaceRule.style.getPropertyValue("font-family")
                  );
                  const fontWeight = cssFontFaceRule.style.getPropertyValue("font-weight") || "normal";
                  const fontStyle = cssFontFaceRule.style.getPropertyValue("font-style") || "normal";
                  const src = cssFontFaceRule.style.getPropertyValue("src");
                  const fontUrl = _extractFirstUrlFromSrc(src);
                  if (fontFamily && fontUrl) {
                    const variation = {
                      family: fontFamily,
                      weight: fontWeight,
                      style: fontStyle
                    };
                    if (!fontPairs[fontUrl]) fontPairs[fontUrl] = [];
                    const variationExists = fontPairs[fontUrl].some(
                      (v) => v.family === variation.family && v.weight === variation.weight && v.style === variation.style
                    );
                    if (!variationExists) fontPairs[fontUrl].push(variation);
                  }
                }
              }
            } catch (e) {
              console.warn(
                "Error processing CSS rules from a stylesheet:",
                e,
                sheet
              );
            }
          } else if (sheet && !sheet.cssRules) {
            console.warn(
              "Skipping a stylesheet as its cssRules are not accessible or it is empty:",
              sheet
            );
          }
        });
        return fontPairs;
      }
      const links = [
        ...document.querySelectorAll('link[rel="stylesheet"]')
      ].filter((link) => {
        try {
          const linkUrl = new URL(link.href);
          const currentUrl = new URL(window.location.href);
          if (linkUrl.origin === currentUrl.origin) {
            return false;
          }
          return !this.isUrlExcludedFromExternalProcessing(link.href);
        } catch (e) {
          return false;
        }
      });
      if (links.length === 0) {
        this.logger.logMessage("No external CSS links found to process.");
        return {
          // Consistent return structure
          styleSheets: [],
          // The retrievable CSSStyleSheet objects
          fontPairs: {}
          // Processed data from these sheets
        };
      }
      const fetchedCssPromises = links.map(
        (linkElement) => fetch(linkElement.href, { mode: "cors" }).then((response) => {
          if (response.ok) {
            return response.text();
          }
          console.warn(
            `Failed to fetch external CSS from ${linkElement.href}: ${response.status} ${response.statusText}`
          );
          return null;
        }).catch((error) => {
          console.error(
            `Network error fetching external CSS from ${linkElement.href}:`,
            error
          );
          return null;
        })
      );
      const cssTexts = await Promise.all(fetchedCssPromises);
      const temporaryStyleSheets = [];
      cssTexts.forEach((txt) => {
        if (txt && txt.trim() !== "") {
          try {
            const sheet = new CSSStyleSheet();
            sheet.replaceSync(txt);
            temporaryStyleSheets.push(sheet);
          } catch (error) {
            console.error(
              "Could not parse fetched CSS into a stylesheet:",
              error,
              `
CSS (first 200 chars): ${txt.substring(0, 200)}...`
            );
          }
        }
      });
      if (temporaryStyleSheets.length > 0) {
        this.logger.logMessage(
          `[Beacon] ${temporaryStyleSheets.length} stylesheet(s) fetched and parsed into CSSStyleSheet objects.`
        );
      } else {
        this.logger.logMessage(
          "[Beacon] No stylesheets were successfully parsed from the fetched CSS."
        );
      }
      const processedFontPairs = generateFontPairsFromStyleSheets(temporaryStyleSheets);
      return {
        styleSheets: temporaryStyleSheets,
        fontPairs: processedFontPairs
      };
    }
    /**
     * Asynchronously initializes and parses external font stylesheets.
     * 
     * Fetches external font stylesheets and font pairs using `externalStylesheetsDoc`,
     * then stores the parsed results in `externalParsedSheets` and `externalParsedPairs`.
     * Logs the process and handles errors by resetting `externalParsedSheets` to an empty array.
     * 
     * @async
     * @returns {Promise<void>} Resolves when external font stylesheets have been initialized.
     */
    async _initializeExternalFontSheets() {
      this.logger.logMessage("Initializing external font stylesheets...");
      try {
        const result = await this.externalStylesheetsDoc();
        this.externalParsedSheets = result.styleSheets || [];
        this.externalParsedPairs = result.fontPairs || [];
        this.logger.logMessage(
          `Successfully parsed ${this.externalParsedSheets.length} external font stylesheets.`
        );
      } catch (error) {
        this.logger.logMessage(
          "Error initializing external font stylesheets:",
          error
        );
        this.externalParsedSheets = [];
      }
    }
    /**
     * Retrieves a map of network-loaded fonts.
     * 
     * This method uses the Performance API to get all resource entries, filters out
     * the ones that match the font file regex, and maps them to their cleaned URLs.
     * 
     * @returns {Map} A map where each key is a cleaned URL of a font file and
     *                each value is the original URL of the font file.
     */
    getNetworkLoadedFonts() {
      return new Map(
        window.performance.getEntriesByType("resource").filter((resource) => this.FONT_FILE_REGEX.test(resource.name)).map((resource) => [this.cleanUrl(resource.name), resource.name])
      );
    }
    /**
     * Retrieves font-face rules from stylesheets.
     * 
     * This method scans all stylesheets loaded on the page and collects
     * font-face rules, including their source URLs, font families, weights,
     * and styles. It returns an object containing the collected font data.
     * 
     * @returns {Promise<Object>} An object mapping font families to their respective
     *                  URLs and variations.
     */
    async getFontFaceRules() {
      const stylesheetFonts = {};
      const processedUrls = /* @__PURE__ */ new Set();
      const processFontFaceRule = (rule, baseHref = null) => {
        const src = rule.style.getPropertyValue("src");
        const fontFamily = rule.style.getPropertyValue("font-family").replace(/['"]/g, "").trim();
        const weight = rule.style.getPropertyValue("font-weight") || "400";
        const style = rule.style.getPropertyValue("font-style") || "normal";
        if (!stylesheetFonts[fontFamily]) {
          stylesheetFonts[fontFamily] = { urls: [], variations: /* @__PURE__ */ new Set() };
        }
        const extractFirstUrlFromSrc = (srcValue) => {
          if (!srcValue) return null;
          const urlMatch = srcValue.match(/url\s*\(\s*(['"]?)(.+?)\1\s*\)/);
          return urlMatch ? urlMatch[2] : null;
        };
        const firstUrl = extractFirstUrlFromSrc(src);
        if (firstUrl) {
          let rawUrl = firstUrl;
          if (baseHref) {
            rawUrl = new URL(rawUrl, baseHref).href;
          }
          const normalized = this.cleanUrl(rawUrl);
          if (!stylesheetFonts[fontFamily].urls.includes(normalized)) {
            stylesheetFonts[fontFamily].urls.push(normalized);
            stylesheetFonts[fontFamily].variations.add(
              JSON.stringify({ weight, style })
            );
          }
        }
      };
      const processImportRule = async (rule) => {
        try {
          const importUrl = rule.href;
          if (this.isUrlExcludedFromExternalProcessing(importUrl)) {
            return;
          }
          if (processedUrls.has(importUrl)) {
            return;
          }
          processedUrls.add(importUrl);
          const response = await fetch(importUrl, { mode: "cors" });
          if (!response.ok) {
            this.logger.logMessage(`Failed to fetch @import CSS: ${response.status}`);
            return;
          }
          const cssText = await response.text();
          const tempSheet = new CSSStyleSheet();
          tempSheet.replaceSync(cssText);
          Array.from(tempSheet.cssRules || []).forEach((importedRule) => {
            if (importedRule instanceof CSSFontFaceRule) {
              processFontFaceRule(importedRule, importUrl);
            }
          });
        } catch (error) {
          this.logger.logMessage(`Error processing @import rule: ${error.message}`);
        }
      };
      const processSheet = async (sheet) => {
        try {
          const rules = Array.from(sheet.cssRules || []);
          for (const rule of rules) {
            if (rule instanceof CSSFontFaceRule) {
              processFontFaceRule(rule, sheet.href);
            } else if (rule instanceof CSSImportRule) {
              if (rule.styleSheet) {
                await processSheet(rule.styleSheet);
              } else {
                await processImportRule(rule);
              }
            } else if (rule.styleSheet) {
              await processSheet(rule.styleSheet);
            }
          }
        } catch (e) {
          if (e.name === "SecurityError" && sheet.href) {
            if (this.isUrlExcludedFromExternalProcessing(sheet.href)) {
              return;
            }
            if (processedUrls.has(sheet.href)) {
              return;
            }
            processedUrls.add(sheet.href);
            try {
              const response = await fetch(sheet.href, { mode: "cors" });
              if (response.ok) {
                const cssText = await response.text();
                const tempSheet = new CSSStyleSheet();
                tempSheet.replaceSync(cssText);
                Array.from(tempSheet.cssRules || []).forEach((rule) => {
                  if (rule instanceof CSSFontFaceRule) {
                    processFontFaceRule(rule, sheet.href);
                  }
                });
                const importRegex = /@import\s+url\(['"]?([^'")]+)['"]?\);?/g;
                let importMatch;
                while ((importMatch = importRegex.exec(cssText)) !== null) {
                  const importUrl = new URL(importMatch[1], sheet.href).href;
                  if (this.isUrlExcludedFromExternalProcessing(importUrl)) {
                    continue;
                  }
                  if (processedUrls.has(importUrl)) {
                    continue;
                  }
                  processedUrls.add(importUrl);
                  try {
                    const importResponse = await fetch(importUrl, { mode: "cors" });
                    if (importResponse.ok) {
                      const importCssText = await importResponse.text();
                      const tempImportSheet = new CSSStyleSheet();
                      tempImportSheet.replaceSync(importCssText);
                      Array.from(tempImportSheet.cssRules || []).forEach((importedRule) => {
                        if (importedRule instanceof CSSFontFaceRule) {
                          processFontFaceRule(importedRule, importUrl);
                        }
                      });
                    }
                  } catch (importError) {
                    this.logger.logMessage(`Error fetching @import ${importUrl}: ${importError.message}`);
                  }
                }
              }
            } catch (fetchError) {
              this.logger.logMessage(`Error fetching stylesheet ${sheet.href}: ${fetchError.message}`);
            }
          } else {
            this.logger.logMessage(`Error processing stylesheet: ${e.message}`);
          }
        }
      };
      const sheets = Array.from(document.styleSheets);
      for (const sheet of sheets) {
        await processSheet(sheet);
      }
      const inlineStyleElements = document.querySelectorAll("style");
      for (const styleElement of inlineStyleElements) {
        const cssText = styleElement.textContent || styleElement.innerHTML || "";
        const importRegex = /@import\s+url\s*\(\s*['"]?([^'")]+)['"]?\s*\)\s*;?/g;
        let importMatch;
        while ((importMatch = importRegex.exec(cssText)) !== null) {
          const importUrl = importMatch[1];
          if (this.isUrlExcludedFromExternalProcessing(importUrl)) {
            continue;
          }
          if (processedUrls.has(importUrl)) {
            continue;
          }
          processedUrls.add(importUrl);
          try {
            const response = await fetch(importUrl, { mode: "cors" });
            if (response.ok) {
              const importCssText = await response.text();
              const tempSheet = new CSSStyleSheet();
              tempSheet.replaceSync(importCssText);
              Array.from(tempSheet.cssRules || []).forEach((importedRule) => {
                if (importedRule instanceof CSSFontFaceRule) {
                  processFontFaceRule(importedRule, importUrl);
                }
              });
            }
          } catch (importError) {
            this.logger.logMessage(`Error fetching inline @import ${importUrl}: ${importError.message}`);
          }
        }
      }
      Object.values(stylesheetFonts).forEach((fontData) => {
        fontData.variations = Array.from(fontData.variations).map((v) => JSON.parse(v));
      });
      return stylesheetFonts;
    }
    /**
     * Checks if an element is above the fold (visible in the viewport without scrolling).
     * 
     * @param {Element} element - The element to check.
     * @returns {boolean} True if the element is above the fold, false otherwise.
     */
    isElementAboveFold(element) {
      if (!this.isElementVisible(element)) return false;
      const rect = element.getBoundingClientRect();
      const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
      const elementTop = rect.top + scrollTop;
      const foldPosition = window.innerHeight || document.documentElement.clientHeight;
      return elementTop <= foldPosition;
    }
    /**
     * Checks if an element can be processed for font analysis.
     * 
     * This method combines checks for whether an element can be styled with font-family
     * and whether it is above the fold, providing a single method to determine if an
     * element should be processed during font analysis.
     * 
     * @param {Element} element - The element to check.
     * @returns {boolean} True if the element can be processed, false otherwise.
     */
    canElementBeProcessed(element) {
      return this.canElementBeStyledWithFontFamily(element) && this.isElementAboveFold(element);
    }
    /**
     * Initiates the process of analyzing and summarizing font usage on the page.
     * This method fetches network-loaded fonts, stylesheet fonts, and external font pairs.
     * It then processes each element on the page to determine which fonts are used above the fold.
     * The results are summarized and logged.
     * 
     * @returns {Promise<void>} A promise that resolves when the analysis is complete.
     */
    async run() {
      await document.fonts.ready;
      await this._initializeExternalFontSheets();
      const networkLoadedFonts = this.getNetworkLoadedFonts();
      const stylesheetFonts = await this.getFontFaceRules();
      const hostedFonts = /* @__PURE__ */ new Map();
      const externalFontsResults = await this.processExternalFonts(this.externalParsedPairs);
      const elements = Array.from(document.getElementsByTagName("*")).filter((el) => this.canElementBeProcessed(el));
      elements.forEach((element) => {
        const processElementFont = (style, pseudoElement = null) => {
          if (!style || !this.isElementVisible(element)) return;
          const fontFamily = style.fontFamily.split(",")[0].replace(/['"]+/g, "").trim();
          const hasContent = pseudoElement ? style.content !== "none" && style.content !== '""' : element.textContent.trim();
          if (hasContent && stylesheetFonts[fontFamily]) {
            let urls = stylesheetFonts[fontFamily].urls;
            if (!this.isExcluded(fontFamily, urls) && !hostedFonts.has(fontFamily)) {
              hostedFonts.set(fontFamily, {
                elements: /* @__PURE__ */ new Set(),
                urls,
                variations: stylesheetFonts[fontFamily].variations
              });
              hostedFonts.get(fontFamily).elements.add(element);
            }
          }
        };
        try {
          processElementFont(window.getComputedStyle(element));
          ["::before", "::after"].forEach((pseudo) => {
            processElementFont(window.getComputedStyle(element, pseudo), pseudo);
          });
        } catch (e) {
          this.logger.logMessage("Error processing element:", e);
        }
      });
      const aboveTheFoldFonts = this.summarizeMatches(externalFontsResults, hostedFonts, networkLoadedFonts);
      if (!Object.keys(aboveTheFoldFonts.allFonts).length && !Object.keys(aboveTheFoldFonts.externalFonts).length && !Object.keys(aboveTheFoldFonts.hostedFonts).length) {
        this.logger.logMessage("No fonts found above the fold.");
        return;
      }
      this.logger.logMessage("Above the fold fonts:", aboveTheFoldFonts);
      this.aboveTheFoldFonts = [...new Set(Object.values(aboveTheFoldFonts.allFonts).flatMap((font) => font.variations.map((variation) => variation.url)))];
    }
    /**
     * Summarizes all font matches found on the page
     * Creates a comprehensive object containing font usage data
     *
     * @param {Object} externalFontsResults - Results from External Fonts analysis
     * @param {Map} hostedFonts - Map of hosted (non-External) fonts found
     * @param {Map} networkLoadedFonts - Map of all font files loaded via network
     * @returns {Object} Complete analysis of font usage including locations and counts
     */
    summarizeMatches(externalFontsResults, hostedFonts, networkLoadedFonts) {
      const allFonts = {};
      const hostedFontsResults = {};
      if (hostedFonts.size > 0) {
        hostedFonts.forEach((data, fontFamily) => {
          if (data.variations) {
            const elements = Array.from(data.elements);
            const aboveElements = elements.filter((el) => this.isElementAboveFold(el));
            const belowElements = elements.filter((el) => !this.isElementAboveFold(el));
            data.variations.forEach((variation) => {
              let matchingUrl = null;
              for (const styleUrl of data.urls) {
                const normalizedStyleUrl = this.cleanUrl(styleUrl);
                if (networkLoadedFonts.has(normalizedStyleUrl)) {
                  matchingUrl = networkLoadedFonts.get(normalizedStyleUrl);
                  break;
                }
              }
              if (matchingUrl) {
                if (!allFonts[fontFamily]) {
                  allFonts[fontFamily] = {
                    type: "hosted",
                    variations: [],
                    elementCount: {
                      aboveFold: aboveElements.length,
                      belowFold: belowElements.length,
                      total: elements.length
                    },
                    urlCount: {
                      aboveFold: /* @__PURE__ */ new Set(),
                      belowFold: /* @__PURE__ */ new Set()
                    }
                  };
                }
                allFonts[fontFamily].variations.push({
                  weight: variation.weight,
                  style: variation.style,
                  url: matchingUrl,
                  elementCount: {
                    aboveFold: aboveElements.length,
                    belowFold: belowElements.length,
                    total: elements.length
                  }
                });
                if (aboveElements.length > 0) {
                  allFonts[fontFamily].urlCount.aboveFold.add(matchingUrl);
                }
                if (belowElements.length > 0) {
                  allFonts[fontFamily].urlCount.belowFold.add(matchingUrl);
                }
              }
            });
            if (allFonts[fontFamily]) {
              hostedFontsResults[fontFamily] = {
                variations: allFonts[fontFamily].variations,
                elementCount: { ...allFonts[fontFamily].elementCount },
                urlCount: { ...allFonts[fontFamily].urlCount }
              };
            }
          }
        });
      }
      if (Object.keys(externalFontsResults).length > 0) {
        Object.entries(externalFontsResults).forEach(([url, data]) => {
          const aboveElements = Array.from(data.elements).filter((el) => this.isElementAboveFold(el));
          const belowElements = Array.from(data.elements).filter((el) => !this.isElementAboveFold(el));
          if (data.elementCount.aboveFold > 0 || aboveElements.length > 0) {
            data.variations.forEach((variation) => {
              if (!allFonts[variation.family]) {
                allFonts[variation.family] = {
                  type: "external",
                  variations: [],
                  // Track element counts at font family level
                  elementCount: {
                    aboveFold: 0,
                    belowFold: 0,
                    total: 0
                  },
                  // Track unique URLs used in each fold location
                  urlCount: {
                    aboveFold: /* @__PURE__ */ new Set(),
                    belowFold: /* @__PURE__ */ new Set()
                  }
                };
              }
              allFonts[variation.family].variations.push({
                weight: variation.weight,
                style: variation.style,
                url,
                elementCount: {
                  aboveFold: aboveElements.length,
                  belowFold: belowElements.length,
                  total: data.elements.length
                }
              });
              allFonts[variation.family].elementCount.aboveFold += aboveElements.length;
              allFonts[variation.family].elementCount.belowFold += belowElements.length;
              allFonts[variation.family].elementCount.total += data.elements.length;
              if (aboveElements.length > 0) {
                allFonts[variation.family].urlCount.aboveFold.add(url);
              }
              if (belowElements.length > 0) {
                allFonts[variation.family].urlCount.belowFold.add(url);
              }
            });
          }
        });
      }
      Object.values(allFonts).forEach((font) => {
        font.urlCount = {
          aboveFold: font.urlCount.aboveFold.size,
          belowFold: font.urlCount.belowFold.size,
          total: (/* @__PURE__ */ new Set([...font.urlCount.aboveFold, ...font.urlCount.belowFold])).size
        };
      });
      Object.values(hostedFontsResults).forEach((font) => {
        if (font.urlCount.aboveFold instanceof Set) {
          font.urlCount = {
            aboveFold: font.urlCount.aboveFold.size,
            belowFold: font.urlCount.belowFold.size,
            total: (/* @__PURE__ */ new Set([...font.urlCount.aboveFold, ...font.urlCount.belowFold])).size
          };
        }
      });
      return {
        externalFonts: Object.fromEntries(
          Object.entries(externalFontsResults).filter(
            (entry) => entry[1].elementCount.aboveFold > 0
          )
        ),
        hostedFonts: hostedFontsResults,
        allFonts
      };
    }
    /**
     * Processes external font pairs to identify their usage on the page.
     * 
     * This method iterates through all elements on the page, checks if they are above the fold,
     * and determines the font information for each element. It then matches the font information
     * with the provided external font pairs to identify which fonts are used and where.
     * 
     * @param {Object} fontPairs - An object where each key is a URL and the value is an array of font variations.
     * @returns {Promise<Object>} A promise that resolves to an object where each key is a URL and the value is an object containing information about the elements using that font.
     */
    async processExternalFonts(fontPairs) {
      const matches = /* @__PURE__ */ new Map();
      const elements = Array.from(document.getElementsByTagName("*")).filter((el) => this.canElementBeProcessed(el));
      const fontMap = /* @__PURE__ */ new Map();
      Object.entries(fontPairs).forEach(([url, variations]) => {
        variations.forEach((variation) => {
          const key = `${variation.family}|${variation.weight}|${variation.style}`;
          fontMap.set(key, { url, ...variation });
        });
      });
      const getFontInfoForElement = (style) => {
        const family = style.fontFamily.split(",")[0].replace(/['"]+/g, "").trim();
        const weight = style.fontWeight;
        const fontStyle = style.fontStyle;
        const key = `${family}|${weight}|${fontStyle}`;
        let fontInfo = fontMap.get(key);
        if (!fontInfo && weight !== "400") {
          const fallbackKey = `${family}|400|${fontStyle}`;
          fontInfo = fontMap.get(fallbackKey);
        }
        return fontInfo;
      };
      elements.forEach((element) => {
        if (element.textContent.trim()) {
          const style = window.getComputedStyle(element);
          const fontInfo = getFontInfoForElement(style);
          if (fontInfo) {
            if (!this.isExcluded(fontInfo.family, [fontInfo.url]) && !matches.has(fontInfo.url)) {
              matches.set(fontInfo.url, {
                elements: /* @__PURE__ */ new Set(),
                variations: /* @__PURE__ */ new Set()
              });
              matches.get(fontInfo.url).elements.add(element);
              matches.get(fontInfo.url).variations.add(JSON.stringify({
                family: fontInfo.family,
                weight: fontInfo.weight,
                style: fontInfo.style
              }));
            }
          }
        }
        ["::before", "::after"].forEach((pseudo) => {
          const pseudoStyle = window.getComputedStyle(element, pseudo);
          if (pseudoStyle.content !== "none" && pseudoStyle.content !== '""') {
            const fontInfo = getFontInfoForElement(pseudoStyle);
            if (fontInfo) {
              if (!this.isExcluded(fontInfo.family, [fontInfo.url]) && !matches.has(fontInfo.url)) {
                matches.set(fontInfo.url, {
                  elements: /* @__PURE__ */ new Set(),
                  variations: /* @__PURE__ */ new Set()
                });
                matches.get(fontInfo.url).elements.add(element);
                matches.get(fontInfo.url).variations.add(JSON.stringify({
                  family: fontInfo.family,
                  weight: fontInfo.weight,
                  style: fontInfo.style
                }));
              }
            }
          }
        });
      });
      return Object.fromEntries(
        Array.from(matches.entries()).map(([url, data]) => [
          url,
          {
            elementCount: {
              aboveFold: Array.from(data.elements).filter((el) => this.isElementAboveFold(el)).length,
              total: data.elements.size
            },
            variations: Array.from(data.variations).map((v) => JSON.parse(v)),
            elements: Array.from(data.elements)
          }
        ])
      );
    }
    /**
     * Retrieves the results of the font analysis, specifically the fonts used above the fold.
     * This method returns an array containing the URLs of the fonts used above the fold.
     * 
     * @returns {Array<string>} An array of URLs of the fonts used above the fold.
     */
    getResults() {
      return this.aboveTheFoldFonts;
    }
  };
  var BeaconPreloadFonts_default = BeaconPreloadFonts;

  // src/BeaconPreconnectExternalDomain.js
  var BeaconPreconnectExternalDomain = class {
    constructor(config, logger) {
      this.logger = logger;
      this.result = [];
      this.excludedPatterns = config.preconnect_external_domain_exclusions;
      this.eligibleElements = config.preconnect_external_domain_elements;
      this.matchedItems = /* @__PURE__ */ new Set();
      this.excludedItems = /* @__PURE__ */ new Set();
    }
    /**
     * Initiates the process of identifying and logging external domains that require preconnection.
     * This method queries the document for eligible elements, processes each element to determine
     * if it should be preconnected, and logs the results.
     */
    async run() {
      const elements = document.querySelectorAll(
        `${this.eligibleElements.join(", ")}[src], ${this.eligibleElements.join(", ")}[href], ${this.eligibleElements.join(", ")}[rel], ${this.eligibleElements.join(", ")}[type]`
      );
      elements.forEach((el) => this.processElement(el));
      this.logger.logMessage({ matchedItems: this.getMatchedItems(), excludedItems: Array.from(this.excludedItems) });
    }
    /**
     * Processes a single element to determine if it should be preconnected.
     * 
     * This method checks if the element is excluded based on attribute or domain rules.
     * If not excluded, it checks if the element's URL is an external domain and adds it to the list of matched items.
     * 
     * @param {Element} el - The element to process.
     */
    processElement(el) {
      try {
        const url = new URL(el.src || el.href || "", location.href);
        if (this.isExcluded(el)) {
          this.excludedItems.add(this.createExclusionObject(url, el));
          return;
        }
        if (this.isExternalDomain(url)) {
          this.matchedItems.add(`${url.hostname}-${el.tagName.toLowerCase()}`);
          this.result = [...new Set(this.result.concat(url.origin))];
        }
      } catch (e) {
        this.logger.logMessage(e);
      }
    }
    /**
     * Checks if an element is excluded based on exclusions patterns.
     * 
     * This method iterates through the excludedPatterns array and checks if any pattern matches any of the element's attribute or values.
     * If a match is found, it returns true, indicating the element is excluded.
     * 
     * @param {Element} el - The element to check.
     * @returns {boolean} True if the element is excluded by an attribute rule, false otherwise.
     */
    isExcluded(el) {
      const outerHTML = el.outerHTML.substring(0, el.outerHTML.indexOf(">") + 1);
      return this.excludedPatterns.some(
        (pattern) => outerHTML.includes(pattern)
      );
    }
    /**
     * Checks if a URL is excluded based on domain rules.
     * 
     * This method iterates through the excludedPatterns array and checks if any pattern matches the URL's hostname.
     * If a match is found, it returns true, indicating the URL is excluded.
     * 
     * @param {URL} url - The URL to check.
     * @returns {boolean} True if the URL is excluded by a domain rule, false otherwise.
     */
    isExcludedByDomain(url) {
      return this.excludedPatterns.some(
        (pattern) => pattern.type === "domain" && url.hostname.includes(pattern.value)
      );
    }
    /**
     * Checks if a URL is from an external domain.
     * 
     * This method compares the hostname of the given URL with the hostname of the current location.
     * If they are not the same, it indicates the URL is from an external domain.
     * 
     * @param {URL} url - The URL to check.
     * @returns {boolean} True if the URL is from an external domain, false otherwise.
     */
    isExternalDomain(url) {
      return url.hostname !== location.hostname && url.hostname;
    }
    /**
     * Creates an exclusion object based on the URL, element.
     * 
     * @param {URL} url - The URL to create the exclusion object for.
     * @param {Element} el - The element to create the exclusion object for.
     * @returns {Object} An object with the URL's hostname, the element's tag name, and the reason.
     */
    createExclusionObject(url, el) {
      return { domain: url.hostname, elementType: el.tagName.toLowerCase() };
    }
    /**
     * Returns an array of matched items, each item split into its domain and element type.
     * 
     * This method iterates through the matchedItems set, splits each item into its domain and element type using the last hyphen as a delimiter,
     * and returns an array of these split items.
     * 
     * @returns {Array} An array of arrays, each containing a domain and an element type.
     */
    getMatchedItems() {
      return Array.from(this.matchedItems).map((item) => {
        const lastHyphenIndex = item.lastIndexOf("-");
        return [
          item.substring(0, lastHyphenIndex),
          // Domain
          item.substring(lastHyphenIndex + 1)
          // Element type
        ];
      });
    }
    /**
     * Returns the array of unique domain names that were found to be external.
     * 
     * This method returns the result array, which contains a list of unique domain names that were identified as external during the analysis process.
     * 
     * @returns {Array} An array of unique domain names.
     */
    getResults() {
      return this.result;
    }
  };
  var BeaconPreconnectExternalDomain_default = BeaconPreconnectExternalDomain;

  // src/Logger.js
  var Logger = class {
    constructor(enabled) {
      this.enabled = enabled;
    }
    logMessage(label, msg = "") {
      if (!this.enabled) {
        return;
      }
      if (msg !== "") {
        console.log(label, msg);
        return;
      }
      console.log(label);
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
      this.preloadFontsBeacon = null;
      this.preconnectExternalDomainBeacon = null;
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
      if (Utils_default.isPageScrolled()) {
        this.logger.logMessage("Bailing out because the page has been scrolled");
        this._finalize();
        return;
      }
      this.infiniteLoopId = setTimeout(() => {
        this._handleInfiniteLoop();
      }, 1e4);
      const isGeneratedBefore = await this._getGeneratedBefore();
      const shouldGenerateLcp = this.config.status.atf && (isGeneratedBefore === false || isGeneratedBefore.lcp === false);
      const shouldGeneratelrc = this.config.status.lrc && (isGeneratedBefore === false || isGeneratedBefore.lrc === false);
      const shouldGeneratePreloadFonts = this.config.status.preload_fonts && (isGeneratedBefore === false || isGeneratedBefore.preload_fonts === false);
      const shouldGeneratePreconnectExternalDomain = this.config.status.preconnect_external_domain && (isGeneratedBefore === false || isGeneratedBefore.preconnect_external_domain === false);
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
      if (shouldGeneratePreloadFonts) {
        this.preloadFontsBeacon = new BeaconPreloadFonts_default(this.config, this.logger);
        await this.preloadFontsBeacon.run();
      } else {
        this.logger.logMessage("Not running BeaconPreloadFonts because data is already available or feature is disabled");
      }
      if (shouldGeneratePreconnectExternalDomain) {
        this.preconnectExternalDomainBeacon = new BeaconPreconnectExternalDomain_default(this.config, this.logger);
        await this.preconnectExternalDomainBeacon.run();
      } else {
        this.logger.logMessage("Not running BeaconPreconnectExternalDomain because data is already available or feature is disabled");
      }
      if (shouldGenerateLcp || shouldGeneratelrc || shouldGeneratePreloadFonts || shouldGeneratePreconnectExternalDomain) {
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
        lrc: this.lrcBeacon ? this.lrcBeacon.getResults() : null,
        preload_fonts: this.preloadFontsBeacon ? this.preloadFontsBeacon.getResults() : null,
        preconnect_external_domain: this.preconnectExternalDomainBeacon ? this.preconnectExternalDomainBeacon.getResults() : null
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
