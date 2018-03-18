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
	 change - Displays the corresponding page
 *
 */

function PageManager(aElem) {

    var refThis = this;

    this.$menuItems = document.querySelectorAll('.wpr-menuItem');
    this.$submitButton = document.querySelector('.wpr-Content > form > input[type=submit]');
    this.$pages = document.querySelectorAll('.wpr-Page');
    this.$sidebar = document.querySelector('.wpr-Sidebar');
    this.$tips = document.querySelector('.wpr-Content-tips');
    this.$menuItem = null;
    this.$page = null;
    this.pageId = null;
    this.buttonText = this.$submitButton.value;


    // If url page change
    window.onhashchange = function() {
        refThis.detectID();
    }


    // If hash already exist (after refresh page for example)
    if(window.location.hash){
        this.detectID();
    }
    else{
        var session = sessionStorage.getItem('wpr-hash');
        if(session){
            window.location.hash = session;
            this.detectID();
        }
        else{
            this.$menuItems[0].classList.add('isActive');
        }
    }

}


/*
* Page detect ID
*/
PageManager.prototype.detectID = function() {
    this.pageId = window.location.hash.split('#')[1];
    sessionStorage.setItem('wpr-hash', this.pageId);

    this.$page = document.querySelector('.wpr-Page#' + this.pageId);
    this.$menuItem = document.getElementById('wpr-nav-' + this.pageId);

    this.change();
}



/*
* Page change
*/
PageManager.prototype.change = function() {

    // Scroll top
    document.documentElement.scrollTop = 0;

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
    this.$sidebar.style.display = 'block';
    this.$tips.style.display = 'block';
    this.$menuItem.classList.add('isActive');
    this.$submitButton.value = this.buttonText;


    // Exception for dashboard
    if(this.pageId == "dashboard"){
        this.$sidebar.style.display = 'none';
        this.$tips.style.display = 'none';
        this.$submitButton.style.display = 'none';
    }

    // Exception for addons
    if(this.pageId == "addons"){
        this.$submitButton.style.display = 'none';
    }

    // Exception for database
    if(this.pageId == "database"){
        this.$submitButton.value = this.$submitButton.dataset.optimizetext;
    }

    // Exception for tools and addons
    if(this.pageId == "tools" || this.pageId == "addons"){
        this.$submitButton.style.display = 'none';
    }
};
