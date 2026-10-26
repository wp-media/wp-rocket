document.addEventListener( 'DOMContentLoaded', function () {

    var $pageManager = document.querySelector(".wpr-Content");
    if($pageManager){
        new PageManager($pageManager);
    }

});


/*-----------------------------------------------*\
		CLASS PAGEMANAGER
\*-----------------------------------------------*/
/**
 * Manages the display of pages / section for WP Rocket plugin
 *
 * Public method :
     detectID - Detect ID with hash
     getBodyTop - Get body top position
	 change - Displays the corresponding page
 *
 */

function PageManager(aElem) {

    var refThis = this;

    this.$body = document.querySelector('.wpr-body');
    this.$menuItems = document.querySelectorAll('.wpr-menuItem');
    this.$menuGroups = document.querySelectorAll('.wpr-menuGroup');
    this.$submitButton = document.querySelector('.wpr-Content > form > #wpr-options-submit');
    this.$pages = document.querySelectorAll('.wpr-Page');
    this.$content = document.querySelector('.wpr-Content');
    this.$links = document.querySelectorAll('.wpr-body a');
    this.$menuItem = null;
    this.$page = null;
    this.pageId = null;
    this.bodyTop = 0;
    this.isReady = false;
    this.buttonText = this.$submitButton.value;

    // Clean the preference stored by the former sidebar toggle, once.
    localStorage.removeItem( 'wpr-show-sidebar' );

    // If url page change
    window.onhashchange = function() {
        refThis.detectID();
    }

    // If hash already exist (after refresh page for example)
    if(window.location.hash){
        this.bodyTop = 0;
        this.detectID();
    }
    else{
        var session = localStorage.getItem('wpr-hash');
        this.bodyTop = 0;

        if(session){
            window.location.hash = session;
            this.detectID();
        }
        else{
            this.$menuItems[0].classList.add('isActive');
            localStorage.setItem('wpr-hash', 'dashboard');
            window.location.hash = '#dashboard';
        }
    }

    // Page changes after the first one scroll to the start of the body. Restoring the page from the
    // hash or the saved session is not a navigation, so the hashchange it triggers must not scroll.
    setTimeout(function() { refThis.isReady = true; }, 0);

    // Expand / collapse the navigation groups
    for (var j = 0; j < this.$menuGroups.length; j++) {
        this.$menuGroups[j].querySelector('.wpr-menuGroup-trigger').addEventListener('click', function() {
            refThis.toggleGroup(this.closest('.wpr-menuGroup'));
        });
    }

    // Close the flyout panels when tapping outside the menu or pressing Escape
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.wpr-menuGroup')) {
            refThis.closeFlyouts();
        }
    });
    document.addEventListener('keydown', function(event) {
        if ('Escape' === event.key) {
            refThis.closeFlyouts();
        }
    });

    // Click link same hash
    for (var i = 0; i < this.$links.length; i++) {
        this.$links[i].onclick = function() {
            var hrefSplit = this.href.split('#')[1];
            if(hrefSplit == refThis.pageId && hrefSplit != undefined){
                refThis.detectID();
                return false;
            }
        };
    }

    // Click links not WP rocket to reset hash
    var $otherlinks = document.querySelectorAll('#adminmenumain a, #wpadminbar a');
    for (var i = 0; i < $otherlinks.length; i++) {
        $otherlinks[i].onclick = function() {
            localStorage.setItem('wpr-hash', '');
        };
    }

    document.addEventListener( 'wpr-cdn-state-change', function() {
        refThis.updateSubmitDisabledState();
    } );

}


/*
* Page detect ID
*/
PageManager.prototype.detectID = function() {
    this.pageId = window.location.hash.split('#')[1];
    this.pageId = this.pageId.includes('=') ? this.pageId.split('=')[0] : this.pageId;

    // Pages that were merged into another one keep working from old links and saved hashes.
    // Keep in sync with hashAliases in mixpanel.js and the first-paint script in views/settings/page.php.
    var movedPages = { imagify: 'media' };
    if (movedPages.hasOwnProperty(this.pageId)) {
        this.pageId = movedPages[this.pageId];
        history.replaceState(null, '', '#' + this.pageId);
    }

    localStorage.setItem('wpr-hash', this.pageId);

    this.$page = document.querySelector('.wpr-Page#' + this.pageId);

    this.$menuItem = document.getElementById('wpr-nav-' + this.pageId);

    this.change();
}



/*
* Get body top position
*/
PageManager.prototype.getBodyTop = function() {
    var adminBar = document.getElementById('wpadminbar');
    var adminBarHeight = adminBar ? adminBar.offsetHeight : 0;
    var wrapPadding = parseFloat(window.getComputedStyle(this.$body.parentElement).paddingTop) || 0;
    var bodyPos = this.$body.getBoundingClientRect();

    // Scroll distance that leaves the body start right under the admin bar and the wrapper padding
    this.bodyTop = Math.max(0, bodyPos.top + window.pageYOffset - adminBarHeight - wrapPadding);
}



/*
* Page change
*/
PageManager.prototype.change = function() {

    var refThis = this;

    // The inline style of the page template only covers the first paint. The inline display styles set
    // below take over in the same task, so nothing is painted in between, even if this method throws.
    var firstPaint = document.getElementById('wpr-first-paint');
    if (firstPaint) {
        firstPaint.remove();
    }

    // Hide other pages
    for (var i = 0; i < this.$pages.length; i++) {
        this.$pages[i].style.display = 'none';
    }
    for (var i = 0; i < this.$menuItems.length; i++) {
        this.$menuItems[i].classList.remove('isActive');
    }

    // Show current default page
    this.$page.style.display = 'block';
    this.$submitButton.style.display = 'block';

    this.$menuItem.classList.add('isActive');
    this.updateGroups();
    this.$submitButton.value = this.buttonText;
    this.$content.classList.add('isNotFull');

    const pagesWithoutSubmit = [
        'dashboard',
        'account',
        'addons',
        'database',
        'tools',
        'addons',
        'tutorials',
        'plugins',
        'account',
    ];

    // The CSS shows or hides the sidebar (and styles the Dashboard container) from this attribute.
    this.$content.dataset.page = this.pageId;

    if (pagesWithoutSubmit.includes(this.pageId)) {
        this.$submitButton.style.display = 'none';
    }

    this.updateSubmitDisabledState();

    // Scroll once the new page is displayed, measured on the final layout. Measuring before the swap
    // used the previous page's height and pending admin notices, so the result changed between tabs.
    if (this.isReady) {
        this.getBodyTop();
        document.documentElement.scrollTop = this.bodyTop;
    }

	// Dispatch custom event after page navigation for other scripts to hook into.
	document.dispatchEvent(new CustomEvent('rocketJsAfterPageNavigation', {
		detail: {
			pageId: this.pageId,
			submitButton: this.$submitButton,
		}
	} ) );
};


/*
* Expand or collapse a navigation group
*/
PageManager.prototype.setGroupOpen = function($group, isOpen) {
	$group.classList.toggle('is-open', isOpen);
	$group.querySelector('.wpr-menuGroup-trigger').setAttribute('aria-expanded', isOpen ? 'true' : 'false');
};


/*
* Close the flyout panels of the icon-only menu
*/
PageManager.prototype.closeFlyouts = function() {
	for (var i = 0; i < this.$menuGroups.length; i++) {
		this.$menuGroups[i].classList.remove('is-flyout-open');
	}
};


/*
* Toggle a navigation group. Only one group is open at a time, so opening one closes the others.
* On narrow screens the menu only shows icons, so the group opens its sub-items in a panel beside the icon (tap).
*/
PageManager.prototype.toggleGroup = function($group) {
	if (window.matchMedia('(max-width: 783px)').matches) {
		var willFlyout = !$group.classList.contains('is-flyout-open');

		this.closeFlyouts();
		$group.classList.toggle('is-flyout-open', willFlyout);

		return;
	}

	var willOpen = !$group.classList.contains('is-open');

	for (var i = 0; i < this.$menuGroups.length; i++) {
		this.setGroupOpen(this.$menuGroups[i], willOpen && this.$menuGroups[i] === $group);
	}
};


/*
* Open the group holding the current page and close the others
*/
PageManager.prototype.updateGroups = function() {
	this.closeFlyouts();

	for (var i = 0; i < this.$menuGroups.length; i++) {
		var $group = this.$menuGroups[i];
		var hasActive = null !== $group.querySelector('.wpr-menuItem.isActive');

		$group.classList.toggle('has-active', hasActive);
		this.setGroupOpen($group, hasActive);
	}

	for (var j = 0; j < this.$menuItems.length; j++) {
		this.$menuItems[j].removeAttribute('aria-current');
	}

	this.$menuItem.setAttribute('aria-current', 'page');
};


/*
* Update submit button disabled state
*/
PageManager.prototype.updateSubmitDisabledState = function() {
	if (!this.$submitButton || 'none' === this.$submitButton.style.display) {
		return;
	}

	var isCdnPage = 'page_cdn' === this.pageId;
	var pausedRocketCdnBlock = document.querySelector(
		'.wpr-Page#page_cdn .wpr-notice.wpr-ri-notice.wpr-cdn-expired__notice'
	);

	this.$submitButton.disabled = isCdnPage && !!pausedRocketCdnBlock;
};
