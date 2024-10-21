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

}


/*
* Page detect ID
*/
PageManager.prototype.detectID = function() {
    this.pageId = window.location.hash.split('#')[1];
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
        this.$sidebar.style.display = 'block';
    } else if ( 'off' === localStorage.getItem('wpr-show-sidebar') ) {
        this.$sidebar.style.display = 'none';
        document.querySelector('#wpr-js-tips').removeAttribute( 'checked' );
    }

    this.$tips.style.display = 'block';
    this.$menuItem.classList.add('isActive');
    this.$submitButton.value = this.buttonText;
    this.$content.classList.add('isNotFull');


    // Exception for dashboard
    if(this.pageId == "dashboard"){
        this.$sidebar.style.display = 'none';
        this.$tips.style.display = 'none';
        this.$submitButton.style.display = 'none';
        this.$content.classList.remove('isNotFull');
    }

    // Exception for addons
    if(this.pageId == "addons"){
        this.$submitButton.style.display = 'none';
    }

    // Exception for database
    if(this.pageId == "database"){
        this.$submitButton.style.display = 'none';
    }

    // Exception for tools and addons
    if(this.pageId == "tools" || this.pageId == "addons"){
        this.$submitButton.style.display = 'none';
    }

    if (this.pageId == "imagify") {
        this.$sidebar.style.display = 'none';
        this.$tips.style.display = 'none';
        this.$submitButton.style.display = 'none';
    }

    if (this.pageId == "tutorials") {
        this.$submitButton.style.display = 'none';
    }

	if (this.pageId == "plugins") {
		this.$submitButton.style.display = 'none';
	}
};
