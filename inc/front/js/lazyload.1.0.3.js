/*=================================================
=            WP Rocket Lazyload System            =
=================================================*/

(function(){
	'use strict';

	var docElem = document.documentElement;

	/*==========  Get computed style  ==========*/
	
	var getStyle = function (elem, style){
		return getComputedStyle(elem, null)[style];
	};


	/*==========  Get window scrolls values  ==========*/

	var getWindowScroll = function(){
		var scrollTop, scrollLeft;

		if(document.compatMode === 'CSS1Compat'){
			scrollTop  = docElem.scrollTop;
			scrollLeft = docElem.scrollTop;
		}else{
			scrollTop  = window.pageYOffset;
			scrollLeft = window.pageXOffset;
		}

		return {'left': scrollLeft, 'top': scrollTop};
	}


	/*==========  Get window Size  ==========*/

	var getWindowSize = function(){
		return {
			'height': window.innerHeight || docElem.clientHeight,
			'width' : window.innerWidth || docElem.clientWidth
		}
	}


	/*==========  Check if element is visible  ==========*/
	
	var isVisible = function(elem, winSizes, winScroll){
		var
			visible   = getStyle(elem, 'visibility') != 'hidden',
			outerRect = elem.getBoundingClientRect();

		return visible ? (outerRect.top < (winSizes.height + winScroll.top)) && (outerRect.left < (winSizes.width + winScroll.left)) : false;
	};


	/*==========  Get elements  ==========*/
	
	var getElems = function(){
		return document.querySelectorAll('[data-lazy-src],[data-lazy-original]');
	}


	/*==========  Main function  ==========*/
	
	var lazyLoad = function(){
		var
			elems     = getElems(),
			winSizes  = getWindowSize(),
			winScroll = getWindowScroll();

		for(var i = 0; i < elems.length; i++){
			var elem    = elems[i];

			if( isVisible(elem, winSizes, winScroll) ){
				var
					lazyAttr = elem.getAttribute('data-lazy-original') ? 'data-lazy-original' : 'data-lazy-src',
					lazySrc  = elem.getAttribute(lazyAttr),
					tagName  = elem.tagName.toLowerCase();
				
				switch(tagName){
					case 'img':
						var img = new Image;
						
						img.onload = new function(){
							elem.src = lazySrc;
							//triggerLoaded();
						};

						img.src = lazySrc;
					break;

					case 'iframe':
						elem.src = lazySrc;
						//triggerLoaded();
					break;
				}
				
				elem.removeAttribute(lazyAttr);
			}
		}
	}


	/*==========  Launch window.onLazyLoaded function once image loaded  ==========*/
	
	/*function triggerLoaded(){
		if(typeof window.onLazyLoaded == 'function'){
			window.onLazyLoaded.call(null);
		}
	}*/


	/*==========  Set events  ==========*/

	if(window.MutationObserver){
		new MutationObserver( lazyLoad ).observe( docElem, {childList: true, subtree: true, attributes: true} );
	}else{
		docElem.addEventListener('DOMNodeInserted', lazyLoad, true);
		docElem.addEventListener('DOMAttrModified', lazyLoad, true);
		setInterval(lazyLoad, 999);
	}

	['focus', 'mouseover', 'click', 'load', 'transitionend', 'animationend', 'webkitAnimationEnd'].forEach(function(name){
		document.addEventListener(name, lazyLoad, true);
	});

	['scroll', 'resize', 'hashchange'].forEach(function(name){
		window.addEventListener(name, lazyLoad, true);
	});

})();