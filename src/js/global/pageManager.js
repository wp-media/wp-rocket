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
    this.$sidebar = document.querySelector('.wpr-Sidebar');
    this.$content = document.querySelector('.wpr-Content');
    this.$tips = document.querySelector('.wpr-Content-tips');
    this.$links = document.querySelectorAll('.wpr-body a');
    this.$menuItem = null;
    this.$page = null;
    this.pageId = null;
    this.bodyTop = 0;
    this.buttonText = this.$submitButton.value;

    refThis.getBodyTop();

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

    // Expand / collapse the navigation groups
    for (var j = 0; j < this.$menuGroups.length; j++) {
        this.$menuGroups[j].querySelector('.wpr-menuGroup-trigger').addEventListener('click', function() {
            refThis.toggleGroup(this.closest('.wpr-menuGroup'));
        });
    }

    // Click link same hash
    for (var i = 0; i < this.$links.length; i++) {
        this.$links[i].onclick = function() {
            refThis.getBodyTop();
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
    localStorage.setItem('wpr-hash', this.pageId);

    this.$page = document.querySelector('.wpr-Page#' + this.pageId);

    this.$menuItem = document.getElementById('wpr-nav-' + this.pageId);

    this.change();
}



/*
* Get body top position
*/
PageManager.prototype.getBodyTop = function() {
    var bodyPos = this.$body.getBoundingClientRect();
    this.bodyTop = bodyPos.top + window.pageYOffset - 47; // #wpadminbar + padding-top .wpr-wrap - 1 - 47
}



/*
* Page change
*/
PageManager.prototype.change = function() {

    var refThis = this;
    document.documentElement.scrollTop = refThis.bodyTop;

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

    if ( null === localStorage.getItem( 'wpr-show-sidebar' ) ) {
        localStorage.setItem( 'wpr-show-sidebar', 'on' );
    }

    if ( 'on' === localStorage.getItem('wpr-show-sidebar') ) {
        this.$sidebar.style.display = 'flex';
    } else if ( 'off' === localStorage.getItem('wpr-show-sidebar') ) {
        this.$sidebar.style.display = 'none';
        document.querySelector('#wpr-js-tips').removeAttribute( 'checked' );
    }

    this.$tips.style.display = 'block';
    this.$menuItem.classList.add('isActive');
    this.updateGroups();
    this.$submitButton.value = this.buttonText;
    this.$content.classList.add('isNotFull');

    const pagesWithoutSubmit = [
        'dashboard',
        'addons',
        'database',
        'tools',
        'addons',
        'imagify',
        'tutorials',
        'plugins',
        'account',
    ];

    const pagesWithoutSidebarToggle = [
        'dashboard',
        'imagify',
        'page_cdn',
    ];

    // Exception for dashboard
    if(this.pageId == "dashboard"){
        this.$sidebar.style.display = 'none';
        this.$content.classList.remove('isNotFull');
    }

    if (this.pageId == "imagify") {
        this.$sidebar.style.display = 'none';
    }

    if (pagesWithoutSidebarToggle.includes(this.pageId)) {
        this.$tips.style.display = 'none';
    }

    if (pagesWithoutSubmit.includes(this.pageId)) {
        this.$submitButton.style.display = 'none';
    }

    this.updateSubmitDisabledState();

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
* Toggle a navigation group. Only one group is open at a time, so opening one closes the others.
* On narrow screens the menu only shows icons, so the group opens its first page instead of expanding.
*/
PageManager.prototype.toggleGroup = function($group) {
	if (window.matchMedia('(max-width: 783px)').matches) {
		var $firstPage = $group.querySelector('.wpr-menuItem[href^="#"]');

		if ($firstPage) {
			window.location.hash = $firstPage.getAttribute('href');
		}

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
