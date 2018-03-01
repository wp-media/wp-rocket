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
 * Manages the display of pages / section of the plugin
 *
 * Public method :
	 change - Displays the corresponding page
 *
 */

function PageManager(aElem) {

    var refThis = this;

    this.$menuItems = document.querySelectorAll('.wpr-menuItem');
    this.$submitButton = document.querySelector('.wpr-Content > form > input[type=submit]');
    this.$pages = document.querySelectorAll('.wpr-page');
    this.$sidebar = document.querySelector('.wpr-Sidebar');
    this.$menuItem = null;
    this.$page = null;
    this.pageId = null;

    // Click on menuItem
    for (var i = 0; i < this.$menuItems.length; i++) {
        refThis.$menuItems[i].onclick = function(event) {
            refThis.pageId = this.href.split('#')[1];
            refThis.$page = document.querySelector('.wpr-page#' + refThis.pageId);
            refThis.$menuItem = document.getElementById('wpr-nav-' + refThis.pageId);

            window.location.hash = refThis.pageId;
            refThis.change();

            return false;
        }
    }

    // If hash already exist (after refresh page for example)
    if(window.location.hash){
        this.pageId = window.location.hash.split('#')[1];
        this.$page = document.querySelector('.wpr-page#' + this.pageId);
        this.$menuItem = document.getElementById('wpr-nav-' + this.pageId);

        this.change();
    }
    else{
        this.$menuItems[0].classList.add('isActive');
    }

}


/*
* Page change
*/
PageManager.prototype.change = function() {

    document.documentElement.scrollTop = 0;

    // Hide other pages
    for (var i = 0; i < this.$pages.length; i++) {
        this.$pages[i].style.display = 'none';
    }
    for (var i = 0; i < this.$menuItems.length; i++) {
        this.$menuItems[i].classList.remove('isActive');
    }

    // Show current page
    this.$page.style.display = 'block';
    this.$submitButton.style.display = 'block';
    this.$sidebar.style.display = 'block';
    this.$menuItem.classList.add('isActive');




    // Exception for tools
    if(this.pageId == "dashboard"){
        this.$sidebar.style.display = 'none';
    }

    // Exception for tools
    if(this.pageId == "tools"){
        this.$submitButton.style.display = 'none';
    }
};
