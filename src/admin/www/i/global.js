$(document).ready(function(){

    let toastId = 0;
    let selectedIds = {};
    
    $(".table-responsive .selectpicker").each(function(i, el) {
        // Хуй знает зачем я это добавил, уже и не вспомню, поставил пока на true
        let init = true;
        $(el).on('show.bs.select', function (e, clickedIndex, isSelected, previousValue) {
            selectedIds[$(el).attr('id')] = $(el).val();
            $('.table-responsive').css( "overflow", "inherit" );
        });
        $(el).on('hide.bs.select', function (e, clickedIndex, isSelected, previousValue) {
            $('.table-responsive').css( "overflow", "auto" );
            let newIds = $(el).val();
            let find = newIds.length == selectedIds[$(el).attr('id')].length;
            for(i in newIds) {
                find = find && selectedIds[$(el).attr('id')].indexOf(newIds[i]) > -1;
                if (!find) break;
            }
            if (init && !find) { 
                $('#filter-form').submit();
            } else {
                init = true;
            }
        });
    });

    hideToast = function(id) {
        $('#toast-'+id).toast('hide'); $('#toast-'+id).remove();
    }

    showError = function(error) {
        if ($("#toast-cont").length == 0) { $('<div class="fixed-bottom p-4" id="toast-cont"></div>').appendTo('body'); }
        text = 
                '<div class="toast toast-danger mr-auto" id="toast-'+toastId+'" role="alert" data-autohide="false">' +
                '<div class="toast-header"><strong class="mr-auto">Ошибка</strong>' + 
                '<button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close"><span aria-hidden="true">×</span></button>' +
                '</div><div class="toast-body">';
        if (Array.isArray(error)) {
            if (error.length == 1) {
                for(i in error) { text += error[i]; }
            } else {
                text += "<ul class='mb-0'>";
                for(i in error) {
                    text += "<li>"+error[i]+"</li>";
                }
                text += "</ul>";
            }
        } else {
            text += error;
        }
        text += '</div></div>';
        $(text).appendTo("#toast-cont").toast('show');
        setTimeout(hideToast, 5000, toastId);
        toastId++;
    }

    showImageUploaded = function(text, image) {
        if ($("#toast-cont").length == 0) { $('<div class="fixed-bottom p-4" id="toast-cont"></div>').appendTo('body'); }
        text = 
            '<div class="toast mr-auto" id="toast-'+toastId+'" role="alert" data-autohide="false">' +
            '<div class="toast-body"><div class="media">' +
            (image ? '<img src="'+image+'" class="align-self-center mr-3 rounded border border-light" style="width: 48px;" alt="">' : '') +
            '<div class="media-body align-self-center">' + text + '</div></div></div></div>';
        $(text).appendTo("#toast-cont").toast('show');
        setTimeout(hideToast, 2500, toastId);
        toastId++;
    }
    
    var initPhotoSwipeFromDOM = function(gallerySelector) {

        // parse slide data (url, title, size ...) from DOM elements 
        // (children of gallerySelector)
        var parseThumbnailElements = function(el) {
            var thumbElements = el.childNodes,
                numNodes = thumbElements.length,
                items = [],
                figureEl,
                linkEl,
                size,
                item;

            for(var i = 0; i < numNodes; i++) {

                figureEl = thumbElements[i]; // <figure> element

                // include only element nodes 
                if(figureEl.nodeType !== 1) {
                    continue;
                }

                linkEl = figureEl; // <a> element

                size = linkEl.getAttribute('data-size').split('x');

                // create slide object
                item = {
                    src: linkEl.getAttribute('href'),
                    w: parseInt(size[0], 10),
                    h: parseInt(size[1], 10)
                };



                if(figureEl.children.length > 1) {
                    // <figcaption> content
                    item.title = figureEl.children[1].innerHTML; 
                }

                if(linkEl.children.length > 0) {
                    // <img> thumbnail element, retrieving thumbnail url
                    item.msrc = linkEl.children[0].getAttribute('src');
                } 

                item.el = figureEl; // save link to element for getThumbBoundsFn
                items.push(item);
            }

            return items;
        };

        // find nearest parent element
        var closest = function closest(el, fn) {
            return el && ( fn(el) ? el : closest(el.parentNode, fn) );
        };

        // triggers when user clicks on thumbnail
        var onThumbnailsClick = function(e) {
            e = e || window.event;
            e.preventDefault ? e.preventDefault() : e.returnValue = false;

            var eTarget = e.target || e.srcElement;

            // find root element of slide
            var clickedListItem = closest(eTarget, function(el) {
                return (el.tagName && el.tagName.toUpperCase() === 'A');
            });

            if(!clickedListItem) {
                return;
            }

            // find index of clicked item by looping through all child nodes
            // alternatively, you may define index via data- attribute
            var clickedGallery = clickedListItem.parentNode,
                childNodes = clickedListItem.parentNode.childNodes,
                numChildNodes = childNodes.length,
                nodeIndex = 0,
                index;

            for (var i = 0; i < numChildNodes; i++) {
                if(childNodes[i].nodeType !== 1) { 
                    continue; 
                }

                if(childNodes[i] === clickedListItem) {
                    index = nodeIndex;
                    break;
                }
                nodeIndex++;
            }



            if(index >= 0) {
                // open PhotoSwipe if valid index found
                openPhotoSwipe( index, clickedGallery );
            }
            return false;
        };

        // parse picture index and gallery index from URL (#&pid=1&gid=2)
        var photoswipeParseHash = function() {
            var hash = window.location.hash.substring(1),
            params = {};

            if(hash.length < 5) {
                return params;
            }

            var vars = hash.split('&');
            for (var i = 0; i < vars.length; i++) {
                if(!vars[i]) {
                    continue;
                }
                var pair = vars[i].split('=');  
                if(pair.length < 2) {
                    continue;
                }           
                params[pair[0]] = pair[1];
            }

            if(params.gid) {
                params.gid = parseInt(params.gid, 10);
            }

            return params;
        };

        var openPhotoSwipe = function(index, galleryElement, disableAnimation, fromURL) {
            var pswpElement = document.querySelectorAll('.pswp')[0],
                gallery,
                options,
                items;

            items = parseThumbnailElements(galleryElement);

            // define options (if needed)
            options = {

                // define gallery index (for URL)
                galleryUID: galleryElement.getAttribute('data-pswp-uid'),

                shareEl: false,

                getThumbBoundsFn: function(index) {
                    // See Options -> getThumbBoundsFn section of documentation for more info
                    var thumbnail = items[index].el.getElementsByTagName('img')[0], // find thumbnail
                        pageYScroll = window.pageYOffset || document.documentElement.scrollTop,
                        rect = thumbnail.getBoundingClientRect(); 

                    return {x:rect.left, y:rect.top + pageYScroll, w:rect.width};
                }

            };

            // PhotoSwipe opened from URL
            if(fromURL) {
                if(options.galleryPIDs) {
                    // parse real index when custom PIDs are used 
                    // http://photoswipe.com/documentation/faq.html#custom-pid-in-url
                    for(var j = 0; j < items.length; j++) {
                        if(items[j].pid == index) {
                            options.index = j;
                            break;
                        }
                    }
                } else {
                    // in URL indexes start from 1
                    options.index = parseInt(index, 10) - 1;
                }
            } else {
                options.index = parseInt(index, 10);
            }

            // exit if index not found
            if( isNaN(options.index) ) {
                return;
            }

            if(disableAnimation) {
                options.showAnimationDuration = 0;
            }

            // Pass data to PhotoSwipe and initialize it
            gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
            gallery.init();
        };

        // loop through all gallery elements and bind events
        var galleryElements = document.querySelectorAll( gallerySelector );

        for(var i = 0, l = galleryElements.length; i < l; i++) {
            galleryElements[i].setAttribute('data-pswp-uid', i+1);
            galleryElements[i].onclick = onThumbnailsClick;
        }

        // Parse URL and open gallery if it contains #&pid=3&gid=1
        var hashData = photoswipeParseHash();
        if(hashData.pid && hashData.gid) {
            openPhotoSwipe( hashData.pid ,  galleryElements[ hashData.gid - 1 ], true, true );
        }
    };

    // execute above function
    initPhotoSwipeFromDOM('.my-gallery');
});