(function(w, d){

   function getWindowHeight(){
      var winH = 0;

      if(d.body && d.body.offsetWidth){
         winH = d.body.offsetHeight;
      }
      if(d.compatMode =='CSS1Compat' && d.documentElement && d.documentElement.offsetWidth){
         winH = d.documentElement.offsetHeight;
      }
      if(w.innerWidth && w.innerHeight){
         winH = w.innerHeight;
      }

      return winH;
   }

   function findTotalOffset(obj) {
	  var ol = ot = 0;
	  if (obj.offsetParent) {
	    do {
	      ol += obj.offsetLeft;
	      ot += obj.offsetTop;
	    }while (obj = obj.offsetParent);
	  }
	  return {left : ol, top : ot};
   }

   function lazyLoad(){
      var imgTags = d.querySelectorAll('[data-lazy-original]');

      var winScrollTop = w.pageYOffset || d.documentElement.scrollTop || d.body.scrollTop;
      var winHeight = getWindowHeight();

      for(var i = 0; i < imgTags.length; i++){
         var img = imgTags[i];
         var imgOTop = findTotalOffset(img).top;

         if(imgOTop < (winHeight + winScrollTop)){

            img.src = img.getAttribute('data-lazy-original');

            img.removeAttribute('data-lazy-original');

         }
      }
   }

   if(w.addEventListener){
      w.addEventListener('DOMContentLoaded', lazyLoad, false);
      w.addEventListener('scroll', lazyLoad, false);
   }else{
      w.attachEvent('onload', lazyLoad);
      w.attachEvent('onscroll', lazyLoad);
   }

})(window, document);