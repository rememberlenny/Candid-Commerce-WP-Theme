/* Ajax-Layerd Nav Widgets 
 * Shopping Cart: WooCommerce
 * File: Frontend JS 
 * License: GPL
 * Copyright: SixtyOneDesigns 
 */

/* Globals
 * Setup variables and id  for areas that are 
 * going to be refreshed
 */
var content 	= ajax_layered_nav.containers;		// new Array;			//Areas to be refreshed
var orderby 	= ajax_layered_nav.orderby;
var triggers 	= ajax_layered_nav.triggers.join(',');

var elements_to_remove = new Array; 	// Pop items in and out of this array so we know they've been refreshed
var DocReadyReload = false;				// Set this to true if your getting sone javascript problems
var isWorking = false;					// Flag to know if we're fetching a refresh
var http = getHTTPObject();				// Http object
var pagination

function checkPagination(){
	if(jQuery('nav.pagination').length > 0){
		pagination = ''
	}
}
/*	Event: document.ready
 * 	Desc: Inititalize the page
 * 			1. Calls function to add Live Handlers to the widget areas, and product area fn= pageLoaderInit()
 * 			2. Build array of ids of widgets that are going to be refresed
 */
jQuery(document).ready(function(){
	pageLoaderInit();
	// jQuery('.widget_layered_nav, .widget_layered_nav_filters, .widget_ajax_layered_nav_filters').each(function(){
		// content.push( this.id );
	// });
	jQuery(document).on('change',orderby + ' select', function() {
		jQuery( orderby ).submit();
		return false;
	})
});

/*	Event: onpopstate
 *  Desc: Reload the page every time the browsers history changes
 */
window.onpopstate = function(event) {
	if (event.state != undefined) {
		loadPage(document.location.toString(),1);
	}
};
/* Function: pageLoaderInit
 * Desc: Add live click handlers to anchors and checkboxes
 * 			1. On Click - load page, prevent broswer
 * 			a. Calls fn = loadPage();
 */
function pageLoaderInit(){

  jQuery(triggers).live('click', function(event){
  		this.blur();
      	var caption = this.title || this.name || "";
      	var group = this.rel || false;
      	loadPage(jQuery(this).data('link'));
      	event.preventDefault();
      	return false;
  });
 }
/* Function: getHTTPObject
 * Returns: xmlhttprequest object
 * Desc: Degrades to ActiveXObject to support older IE browsers
 */
function getHTTPObject() {
  	var xmlhttp;
	if (window.XMLHttpRequest) {
  		xmlhttp = new XMLHttpRequest();
	}
	else
	{
  		if (window.ActiveXObject) {
     		xmlhttp = new ActiveXObject('MSXML2.XMLHTTP.3.0');
  		}
	}
  	return xmlhttp;
}
/* Function: loadPage
 * Params: 
 * 		@url	= url of target page, 
 * 		@push	= whether to update browser history
 * Desc: Reloads content areas
 */
function loadPage(url, push){
	//Make sure wer're not already doing something
	if (!isWorking){							
		//get domain name...
		nohttp = url.replace("http://","").replace("https://","");
		firstsla = nohttp.indexOf("/");
		pathpos = url.indexOf(nohttp);
		path = url.substring(pathpos + firstsla);
		//Only do a history state if clicked on the page.
		if (push != 1) {
			var stateObj = { foo: 1000 + Math.random()*1001 };
			/*Only push history if not IE 
			 * IE doesn't support 
			 */
			if(!jQuery.browser.msie){
				history.pushState(stateObj, "ajax page loaded...", path);	
			}
		}
		/* Loop through each id in the content array()*/
		jQuery.each(content, function(index, value){
			/* Products container
			 * add an img / message to the products container to let user know it's being refreshed
			 */
			if(value ==ajax_layered_nav.product_container){
				var max = 0;
				max = jQuery(ajax_layered_nav.product_container).outerHeight();
				jQuery(value + '').fadeOut("fast", function() {
					jQuery(value).html('<center style="min-height:'+max+'px;"><p>'+ajax_layered_nav.loading_text+'...<br><img src="'+ajax_layered_nav.loading_img+'" alt="loading"></p></center>');
					jQuery(value).css({'height':max}).fadeIn("slow", function() {});
				});
			}
			http.open('GET', url, true);		//Get the new content
			isWorking = true;					//Set the isWorking flag to true so we don't bombard it with a bunch of requests at once
			http.onreadystatechange = showPage; //Call showPage() function
			http.send(null);					//Don't send anything
		})
	}
	return false;
}

/* Function: showPage()
 * desc: replaces the contents of the target div with that of the new http request 
 */
 function showPage(){
	if (http.readyState == 4) {										//Request has completed
		if (http.status == 200) {									//Request was good
			isWorking = false;										//No longer making the request
			elements_to_remove=[];	
			elements_to_remove = content.slice();
			/* Update content areas */
			jQuery.each(content, function(index, value){
				var details = http.responseText; 					//get the ajax response
				jQuery(value).each(function(){
					if( this.id != ''){
						var $id = '#'+this.id;
						if ( jQuery($id, details).size() > 0  ) {
							var depth = 1;
							var output = '';
							jQuery($id).fadeOut("fast", function() {
								jQuery($id).html( jQuery($id, details).html() );
								jQuery($id).fadeIn(1);
								if (DocReadyReload == true) {
									$(document).trigger("ready");
								}
							});
						} else {												//Empty the elements
							jQuery.each(elements_to_remove, function(index,value){
								jQuery(value).empty();	
							});
						}
					}else{
						if ( jQuery(value, details).size() > 0 ) {
							var depth = 1;
							var output = '';
							jQuery(value).fadeOut("fast", function() {
								jQuery(value).html( jQuery(value, details).html() );
								jQuery(value).fadeIn(1);
								if (DocReadyReload == true) {
									$(document).trigger("ready");
								}
							});
						} else {												//Empty the elements
							jQuery.each(elements_to_remove, function(index,value){
								jQuery(value).empty();	
							});
						}
					}
				});
		});
		/* Re-fire the pageLoaderInit() function. This adds the live click handlers to the newly
		 * readded elemets 
		 */
		pageLoaderInit();
		return false
	} else {
	}
}
	return false;
}
/* Function removeByValue
 * params: 
 * 		@val = value of elment to pop out of array 
 * desc: Allows us to remove an element from a javascript array by value
 */
Array.prototype.removeByValue = function(val) {
    for(var i=0; i<this.length; i++) {
        if(this[i] == val) {
            this.splice(i, 1);
            break;
        }
    }
}

