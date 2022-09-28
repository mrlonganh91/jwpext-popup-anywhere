/*!
  Popup - jQuery Lightbox & Popup Plugin
  @name jquery.popup.js
  @description jQuery plugin for creating responsive lightboxes & popups with focus on performance and effects
  @version 1.0.0
  @author Mr.LongAnh
  @site https://joomlaweb.site
  @copyright (c) Mr.LongAnh
*/


;(function($, window, document, undefined) {
	'use strict';
	var ITEM_POPUP_ID = 'popup-id',
	ITEM_POPUP_TYPE = 'popup-type',
	ITEM_POPUP_HREF = 'popup-href',
	ITEM_POPUP_TITLE = 'popup-title',
	ITEM_POPUP_WIDTH = 'popup-width',
	ITEM_POPUP_HEIGHT = 'popup-height',
	ITEM_COUNTER = 0,
	ITEM_GROUP_COUNTER = 0,
	_instance = null;
	
	function Popup() {
	}
	Popup.prototype = {
		//=============================================
		// Properties & methods (shared for all instances)
		//=============================================
		itemDefaults: {
			type: 'image', // Available content types: image, inline, ajax, iframe, video
			redirect_link:null,
			timeout:null,
			clickOverlayHid:null,
			title: true, // Show/hide title control
			width: null, // Content width, can be any CSS valid unit - px, %, and etc
			height: null, // Content height, can be any CSS valid unit - px, %, and etc
			gallery: true, // Enable/disable gallery
			galleryLoop: true, // Enable/disable gallery loop
			galleryCounter: true, // Show/hide item counter for the gallery
			galleryPrevNext: true, // Show/hide prev and next navigation flow
			galleryCounterMessage: '[index] / [total]', // Message used in the item counter. If empty, no counter will be displayed
			gallerySwipeDesktop: true, // Enable/disable swipe on desktop
			gallerySwipeMobile: true, // Enable/disable swipe on mobile devices
			gallerySwipeThreshold: 100, // Set swipe threshold in px
			keyboard: true, // Enable/disable keyboard navigation with arrows and close with ESC
			overlayClose: false, // Close lightbox via the close button or overlay click
			themeClass: null, // Additional css classes for customization
			showClass: null, // Additional CSS class applied when a new item is shown
			hideClass: null, // Additional CSS class applied when a new item is hidden
			nextShowClass: null, // Additional CSS class applied when a new item is shown on next event
			nextHideClass: null, // Additional CSS class applied when the current item is hidden on next event
			prevShowClass: null, // Additional CSS class applied when a new item is shown on prev event
			prevHideClass: null, // Additional CSS class applied when the current item is hidden on prev event
			videoAutoPlay: false, // Enable/Disable video autoplay
			loadError: 'Sorry, error while loading the content...' // Error message displayed when a content fails to load
		},
		
		touchMouseEvent: {
			down: 'touchmousedown',
			up:   'touchmouseup',
			move: 'touchmousemove'
		},
		
		$body: null,
		$document: null,
		util: null,
		animationEvent: null,
		
		items: null,
		currItem: null,
		prevItem: null,
		nextItem: null,
		
		def: null, // deffered object, used for content loading
		
		swipe: {
			startY: 0,
			startX: 0,
			endY: 0,
			endX: 0,
			diffY: 0,
			diffX: 0,
			startTouch: false,
			threshold: 100
		},
		
		flow: {
			$overlay: null,
			$container: null,
			$contentOuter: null,
			$contentInner: null,
			$content: null,
			$title: null,
			$numeration: null,
			$next: null,
			$prev: null,
			$close: null,
			$preloader: null
		},
		
		_init: function() {
			this.$body = $('body');
			this.$document = $(document);
			this.util = new Util();
			this.animationEvent = this.util.animationEvent();
			this.items = new jStorage();
		},
		
		_buildDOM: function() {
			this.flow.$overlay = $('<div></div>').addClass('popup-overlay');
			this.flow.$container = $('<div></div>').addClass('popup-container').attr('tabindex', 1); // we add tabindex to work focus properly
			
			this.flow.$title = $('<div></div>').addClass('popup-title');
			this.flow.$numeration = $('<div></div>').addClass('popup-numeration');
			this.flow.$next = $('<div></div>').addClass('popup-next');
			this.flow.$prev = $('<div></div>').addClass('popup-prev');
			this.flow.$close = $('<div></div>').addClass('popup-close');
			this.flow.$preloader = $('<div></div>').addClass('popup-preloader');
			
			this.flow.$overlay.append(this.flow.$container, this.flow.$title, this.flow.$numeration, this.flow.$next, this.flow.$prev, this.flow.$close, this.flow.$preloader);
			
			this.$body.append(this.flow.$overlay);
			this.$body.get(0).offsetHeight; // little hack to force an update
			
			// calculate scrollbar width and hide scrollbar
			var offset = this.flow.$container.get(0).offsetWidth - this.flow.$container.get(0).clientWidth;
			this.flow.$container.css('right', - offset + 'px');
			
			this.$body.addClass('popup-active');
		},
		
		_bind: function(item) {
			if(item.config.overlayClose) {
				this.flow.$overlay.on('click touchend', $.proxy(this._onOverlayClick, this));
			} else {
				this.flow.$close.on('click touchend', $.proxy(this._onCloseClick, this));
			}
			this.flow.$prev.on('click touchend', $.proxy(this._onGalleryNavigationClick, this, 'prev'));
			this.flow.$next.on('click touchend', $.proxy(this._onGalleryNavigationClick, this, 'next'));
			this.$body.on('keydown', $.proxy(this._onKeyboard, this));
			
			if(item.config.gallery) {
				if('ontouchstart' in window) {
					if(item.config.gallerySwipeMobile) {
						this.$document.on('touchstart', $.proxy(this._onTouch, this));
						this.$document.on('touchmove', $.proxy(this._onTouch, this));
						this.$document.on('touchend', $.proxy(this._onTouch, this));
					}
				} else {
					if(item.config.gallerySwipeDesktop) {
						this.$document.on('mousedown', $.proxy(this._onMouse, this));
						this.$document.on('mouseup', $.proxy(this._onMouse, this));
						this.$document.on('mouseout', $.proxy(this._onMouse, this));
						this.$document.on('mousemove', $.proxy(this._onMouse, this));
					}
				}
			}
			
			this.$document.on('webkitfullscreenchange mozfullscreenchange fullscreenchange MSFullscreenChange', $.proxy(this._onFullsceenChange, this));
		},
		
		_unbind: function() {
			this.flow.$overlay.off('click touchend', $.proxy(this._onOverlayClick, this));
			this.flow.$close.off('click touchend', $.proxy(this._onCloseClick, this));
			this.flow.$prev.off('click touchend', $.proxy(this._onGalleryNavigationClick, this, 'prev'));
			this.flow.$next.off('click touchend', $.proxy(this._onGalleryNavigationClick, this, 'next'));
			this.$body.off('keydown', $.proxy(this._onKeyboard, this));
			
			if('ontouchstart' in window) {
				this.$document.off('touchstart', $.proxy(this._onTouch, this));
				this.$document.off('touchmove', $.proxy(this._onTouch, this));
				this.$document.off('touchend', $.proxy(this._onTouch, this));
			} else {
				this.$document.off('mousedown', $.proxy(this._onMouse, this));
				this.$document.off('mouseup', $.proxy(this._onMouse, this));
				this.$document.off('mouseout', $.proxy(this._onMouse, this));
				this.$document.off('mousemove', $.proxy(this._onMouse, this));
			}
			
			this.$document.off('webkitfullscreenchange mozfullscreenchange fullscreenchange MSFullscreenChange', $.proxy(this._onFullsceenChange, this));
		},
		
		_addItem: function($el, group, config) {
			var id = $el.data(ITEM_POPUP_ID),
			item = {
				id: null,
				$el: $el,
				config: config,
				group: group,
				groupIndex: 0,
				groupTotal: 0
			};
			
			if(!id) {
				id = ITEM_COUNTER++;
				
				$el.data(ITEM_POPUP_ID, id);
				$el.on('click touchend', $.proxy(this._onItemClick, this));
			}
			
			item.id = id;
			
			this.items.set(id, item);
		},
		
		_removeItem: function($el) {
			var id = $el.data(ITEM_POPUP_ID);
			if(!id) {
				return;
			}
			
			$el.removeData(ITEM_POPUP_ID);
			$el.off('click touchend', $.proxy(this._onItemClick, this));
			
			this.items.del(id);
		},
		
		_onFullsceenChange: function(e) {
			if(!window.screenTop && !window.screenY) {
				this.flow.$overlay.removeClass('popup-fullscreen');
			} else {
				this.flow.$overlay.addClass('popup-fullscreen');
			}
		},
		
		_onOverlayClick: function(e) {
			var $el = $(e.target);
			if(!$el.closest('.popup-content').hasClass('popup-content')) {
				this._close();
			}
		},
		
		_onCloseClick: function(e) {
			this._close();
		},
		
		_onGalleryNavigationClick: function(type, e) {
			e.preventDefault();
			e.stopPropagation();
			
			this._galleryNavigate(type);
		},
		
		_onKeyboard: function(e) {
			if(this.currItem == null || !this.currItem.config.keyboard) {
				return;
			}
			
			if(e.keyCode === 27) { // ESC
				this._close();
				return;
			}
			
			if(this.currItem.config.gallery) {
				if(e.keyCode == 37 && this.prevItem) { // LEFT
					this._galleryNavigate('prev');
				}
				if(e.keyCode == 39 && this.nextItem) { // RIGHT
					this._galleryNavigate('next');
				}
			}
		},
		
		_onItemClick: function(e) {
			e.preventDefault();
			e.stopPropagation();
			
			this._show($(e.currentTarget));
		},
		
		_onMouse: function(e) {
			var type;
			switch (e.type) {
				case 'mousedown':  type = this.touchMouseEvent.down; break;
				case 'mouseup':    type = this.touchMouseEvent.up;   break;
				case 'mouseout':   type = this.touchMouseEvent.up;   break;
				case 'mousemove':  type = this.touchMouseEvent.move; break;
				default:
					return;
			}
			
			var touchMouseEvent = this._normalizeEvent(type, e, e.pageX, e.pageY);
			$(e.target).trigger(touchMouseEvent);
		},
		
		_onTouch: function(e) {
			var type;
			switch(e.type) {
				case 'touchstart': type = this.touchMouseEvent.down; break;
				case 'touchend':   type = this.touchMouseEvent.up;   break;
				case 'touchmove':  type = this.touchMouseEvent.move; break;
				default:
					return;
			}
			
			var touch = e.originalEvent.touches[0],
			touchMouseEvent;
			if(type == this.touchMouseEvent.up) {
				touchMouseEvent = this._normalizeEvent(type, e, null, null);
			} else {
				touchMouseEvent = this._normalizeEvent(type, e, touch.pageX, touch.pageY);
			}
			$(e.target).trigger(touchMouseEvent);
		},
		
		_onSwipeDown: function(e) {
			this.swipe.startY = this.swipe.endY = e.pageY;
			this.swipe.startX = this.swipe.endX = e.pageX;
			this.swipe.startouch = true;
		},
		
		_onSwipeMove: function(e) {
			if(this.swipe.startouch) {
				this.swipe.endY = e.pageY;
				this.swipe.endX = e.pageX;
				
				this.swipe.diffY = this.swipe.endY - this.swipe.startY;
				this.swipe.diffX = this.swipe.endX - this.swipe.startX;
				
				
				var diffX = Math.abs(this.swipe.diffX),
				diffY = Math.abs(this.swipe.diffY);
				
				if(diffX >= 10 || diffY >= 10) {
					e.preventDefault();
					this.flow.$content.css('transform', 'translate(' + this.swipe.diffX + 'px,' + this.swipe.diffY + 'px)');
				}
			}
		},
		
		_onSwipeUp: function(e) {
			if(this.swipe.startouch) {
				this.swipe.startouch = false;
				
				var direction = null,
				change = false;
				
				this.swipe.diff = this.swipe.endX - this.swipe.startX;
				
				if(this.swipe.diff < 0 && this.nextItem) {
					direction = 'next';
					change = true;
				}
				
				if(this.swipe.diff > 0 && this.prevItem) {
					direction = 'prev';
					change = true;
				}
				
				if(change && Math.abs(this.swipe.diff) >= this.swipe.threshold) {
					this._galleryNavigate(direction);
				} else {
					this.flow.$content.css('transform', '');
				}
			}
		},
		
		_normalizeEvent: function(type, original, x, y) {
			return $.Event(type, {pageX: x, pageY: y, originalEvent: original});
		},
		
		_show: function($el, direction) {
			var key = $el.data(ITEM_POPUP_ID),
			item = this.items.get(key),
			type = $el.data(ITEM_POPUP_TYPE) || item.config.type,
			href = $el.data(ITEM_POPUP_HREF) || $el.attr('href'),
			self = this;
			
			if(this.currItem == null) {
				this._buildDOM();
				this._initGalleryCounter(item);
				this._bind(item);
			}
			
			this._hideContent(this.currItem, direction);
			this.currItem = item;
			this._loadContent(type, item, href).done(function() {
				self._showContent(item, direction);		
				if(item.config.clickOverlayHid){
					jQuery(".popup-active").click(function(){
						self._hideAnimatedClose();
					});
				}
				if(item.config.timeout){
					setTimeout(self._hideAnimatedClose,item.config.timeout);					
				}
			}).fail(function() {
				self.flow.$content.html(item.config.loadError).addClass('popup-error');
				self._showContent(item, direction);
			}).always(function() {
				
				self._updateDOM(item);
				self._hidePreloader();
				self._initSwipe(item);
				
			});
		},
		_hideAnimatedClose:function(){
			jQuery('.popup-content').addClass('animated backOutDown').hide('slow', function(){ 
				jQuery('.popup-close').trigger('click');	
			});
		},
		
		_close: function() {
			this.flow.$overlay.remove();
			this.$body.removeClass('popup-active');
			this._unbind();
			
			if(this.currItem) {
				this.currItem.$el.focus();
			}
			this.currItem = null;
			this.flow.$contentOuter = null;
			this.flow.$contentInner = null;
			this.flow.$content = null;
			
			this._hideGalleryNavigation();
		},
		
		_showPreloader: function() {
			this.flow.$preloader.addClass('popup-show');
		},
		
		_hidePreloader: function() {
			this.flow.$preloader.removeClass('popup-show');
		},
		
		_initSwipe: function(item) {
			this.swipe.threshold = item.config.gallerySwipeThreshold;
			
			this.flow.$contentInner.on(this.touchMouseEvent.down, $.proxy(this._onSwipeDown, this));
			this.flow.$contentInner.on(this.touchMouseEvent.move, $.proxy(this._onSwipeMove, this));
			this.flow.$contentInner.on(this.touchMouseEvent.up, $.proxy(this._onSwipeUp, this));
		},
		
		_hideContent: function(item, direction) {
			if(this.flow.$contentOuter) {
				var $contentOuter = this.flow.$contentOuter;
				this.flow.$contentOuter = null;
				this.flow.$contentInner = null;
				this.flow.$content = null;
				
				var hideClass = item.config.hideClass;
				
				if(direction == 'next' && item.config.nextHideClass) {
					hideClass = item.config.nextHideClass;
				} else if(direction == 'prev' && item.config.prevHideClass) {
					hideClass = item.config.prevHideClass;
				}
				
				if(this.animationEvent && hideClass) {
					$contentOuter.attr('class','popup-content-outer');
					$contentOuter.addClass(hideClass);
					
					$contentOuter.one(this.animationEvent, function(e) {
						$(this).remove();
					});
				} else {
					$contentOuter.remove();
				}
			}
		},
		
		_showContent: function(item, direction) {
			var showClass = item.config.showClass;
			
			if(direction == 'next' && item.config.nextShowClass) {
				showClass = item.config.nextShowClass;
			} else if(direction == 'prev' && item.config.prevShowClass) {
				showClass = item.config.prevShowClass;
			}
			
			if(this.animationEvent && showClass) {
				this.flow.$contentOuter.addClass(showClass);
			}
			
			this.flow.$container.focus().stop().animate({scrollTop:0}, 300);
			this.flow.$content.addClass('popup-show');
			this.flow.$content.on('dragstart', 'img', function(e) { return false; });
			
			var w = item.$el.data(ITEM_POPUP_WIDTH) || item.config.width,
			h = item.$el.data(ITEM_POPUP_HEIGHT) || item.config.height;
			
			// fix reponsive image,youtube...
			var win_width = $(window).width();
			self = this;

			$(window).on('resize', function(){		
				win_width = $(this).width();						
				//seft._reponsiveContent(w,h,win_width);
				//var boll_resive = false;
				//Popup.prototype._reponsiveContent(w, h, win_width);
				
				if(self.flow.$content.size()){
					self._reponsiveContent(w, h, win_width);
				}
				// if(w) { 
				// 	if(win_width>w){
				// 		$('.popup-content').css('width', w); 
				// 	}else{
				// 		boll_resive = true;
				// 		$('.popup-content').css('width', 'auto'); 
				// 	}
				// }
				// if(h) { 
				// 	if(boll_resive==false){
				// 		$('.popup-content').css('height', h); 
				// 	}else{
				// 		$('.popup-content').css('height', 'auto'); 
				// 	}
				// }
			});
			//$(window).resize(this._reponsiveContent(w,h,win_width));
			this._reponsiveContent(w,h,win_width);
		},
		
		_reponsiveContent: function(w, h, win_width) {
			var boll_resive = false;
			
			if(w) { 
				if(win_width>w){
					this.flow.$content.css('width', w); 
				}else{
					boll_resive = true;
					this.flow.$content.css('width', 'auto'); 
				}
			}
			if(h) { 
				if(boll_resive==false){
					this.flow.$content.css('height', h); 					 
				}else{
					this.flow.$content.css('height', 'auto'); 
				}
			}
		},
		_loadContent: function(type, item, href) {
			this._showPreloader();
			this._updateGalleryNavigation(item);
			
			// create new dynamic content containers
			this.flow.$contentOuter = $('<div></div>').addClass('popup-content-outer');
			this.flow.$contentInner = $('<div></div>').addClass('popup-content-inner');
			this.flow.$content = $('<div></div>').addClass('popup-content');
			
			this.flow.$contentInner.append(this.flow.$content);
			this.flow.$contentOuter.append(this.flow.$contentInner);
			this.flow.$container.append(this.flow.$contentOuter);
			
			if(this.def) {
				this.def.reject(); // prevent multiple loading
				this.def = null;
			}
			
			var def = this.def = $.Deferred();
			
			switch(type) {
				case 'image'  : this._loadImage(def, item, href); break;
				case 'inline' : this._loadInline(def, item, href); break;
				case 'ajax'   : this._loadAjax(def, item, href); break;
				case 'iframe' : this._loadIframe(def, item, href); break;
				case 'video'  : this._loadVideo(def, item, href); break;
			}
			
			return def.promise();
		},
		
		_loadImage: function(def, item, href) {
			this.flow.$content.addClass('popup-image');
			if(item.config.redirect_link){
				this.flow.$content.html('<a href="'+item.config.redirect_link+'" target="_blank"><img src="' + href + '"></a>');
			}else{
				this.flow.$content.html('<img src="' + href + '">');
			}
			var $image = this.flow.$content.find('img');
			
			$image.one('load', function() {				
				def.resolve();
			}).one('error', function() {
				def.reject();
			}).each(function() {
				if(this.complete) {					
					$(this).load(); // For jQuery < 3.0 
			       // $(this).trigger('load'); // For jQuery >= 3.0 
			    }
			});
			
			
		},
		
		_loadInline: function(def, item, href) {
			var $inline = $(href);
			if($inline.length) {
				this.flow.$content.addClass('popup-inline');
				this.flow.$content.html($inline.html());
				def.resolve();
			}
			def.reject();
		},
		
		_loadAjax: function(def, item, href) {
			this.flow.$content.addClass('popup-inline');
			
			var self = this;
			$.ajax({
				url: href,
				cache: false
			}).done(function(data) {
				self.flow.$content.html(data);
				def.resolve();
			}).fail(function() {
				def.reject();
			});
		},
		
		_loadIframe: function(def, item, href) {
			this.flow.$content.addClass('popup-iframe');
			this.flow.$content.html('<iframe src="' + href + '"></iframe>');
			def.resolve();
		},
		
		_loadVideo: function(def, item, href) {
			this.flow.$content.addClass('popup-video');
			
			var videoObj = this._parseVideoUrl(href),
			player = null;
			
			// set rel=0 to hide related videos at the end of YT + optional autoplay
			var strAutoplay = (item.config.videoAutoPlay ? '?rel=0&autoplay=1' : '?rel=0'),
			queryvars = strAutoplay + this._getUrlParameter(href); //'&wmode=transparent'
			
			if (videoObj.type == 'vimeo') {
				player = 'https://player.vimeo.com/video/';
			} else if (videoObj.type == 'youtube') {
				player = 'https://www.youtube.com/embed/';
			}
			
			if(player) {
				this.flow.$content.html('<iframe webkitallowfullscreen mozallowfullscreen allowfullscreen frameborder="0" src="' + player + videoObj.id + queryvars + '"></iframe>');
				def.resolve();
			} else {
				def.reject();
			}
		},
		
		_parseVideoUrl: function(url) {
			url.match(/(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/);
			var type;
			if (RegExp.$3.indexOf('youtu') > -1) {
				type = 'youtube';
			} else if (RegExp.$3.indexOf('vimeo') > -1) {
				type = 'vimeo';
			}
			
			return {
				type: type,
				id: RegExp.$6
			};
		},
		
		_getUrlParameter: function(url) {
			var result = '',
			pageUrl = decodeURIComponent(name),
			firstSplit = pageUrl.split('?');
			
			if (firstSplit[1] !== undefined) {
				var urlVariables = firstSplit[1].split('&'),
				parameterName = null;
				
				for(var i=0; i < urlVariables.length; i++) {
					parameterName = urlVariables[i].split('=');
					result = result + '&'+ parameterName[0]+'='+ parameterName[1];
				}
			}
			
			return encodeURI(result);
		},
		
		_galleryNavigate: function(direction) {
			if(this.currItem == null) {
				return;
			}
			
			if(direction == 'next' && this.nextItem) {
				this._show(this.nextItem.$el, direction);
			} else if(direction == 'prev' && this.prevItem) {
				this._show(this.prevItem.$el, direction);
			}
		},
		
		_hideGalleryNavigation: function() {
			this.nextItem = null;
			this.prevItem = null;
			
			this.flow.$next.removeClass('popup-show');
			this.flow.$prev.removeClass('popup-show');
		},
		
		_updateGalleryNavigation: function(item) {
			if(!item.config.gallery) {
				this._hideGalleryNavigation();
				return;
			}
			
			var len = this.items.len(),
			id = null,
			tmpItem = null,
			firstItem = null,
			lastItem = null,
			nextItem = null,
			prevItem = null;
			
			// first item
			for(id=0; id<len; id++) {
				if(this.items.storage.hasOwnProperty(id)) {
					tmpItem = this.items.storage[id];
					
					if(tmpItem.group == item.group) {
						firstItem = tmpItem;
						break;
					}
				}
			}
			
			// last item
			for(id=len-1; id>=0; id--) {
				if(this.items.storage.hasOwnProperty(id)) {
					tmpItem = this.items.storage[id];
					
					if(tmpItem.group == item.group) {
						lastItem = tmpItem;
						break;
					}
				}
			}
			
			// next item
			for(id=item.id+1; id<len; id++) {
				if(this.items.storage.hasOwnProperty(id)) {
					tmpItem = this.items.storage[id];
					
					if(tmpItem.group == item.group) {
						nextItem = tmpItem;
						break;
					}
				}
			}
			
			// prev item
			for(id=item.id-1; id>=0; id--) {
				if(this.items.storage.hasOwnProperty(id)) {
					tmpItem = this.items.storage[id];
					
					if(tmpItem.group == item.group) {
						prevItem = tmpItem;
						break;
					}
				}
			}
			
			if(firstItem && lastItem && firstItem.id == lastItem.id) {
				this._hideGalleryNavigation();
				return;
			}
			
			if(item.config.galleryLoop) {
				if(nextItem == null) {
					nextItem = firstItem;
				}
				
				if(prevItem == null) {
					prevItem = lastItem;
				}
			}
			
			if(nextItem && item.config.galleryPrevNext) {
				this.flow.$next.addClass('popup-show');
			} else {
				this.flow.$next.removeClass('popup-show');
			}
			
			if(prevItem && item.config.galleryPrevNext) {
				this.flow.$prev.addClass('popup-show');
			} else {
				this.flow.$prev.removeClass('popup-show');
			}
			
			this.nextItem = nextItem;
			this.prevItem = prevItem;
		},
		
		_initGalleryCounter: function(item) {
			var groupTotal = 0,
			groupIndex = 0,
			len = this.items.len(),
			id = null;
			
			for(id=0; id<len; id++) {
				if(this.items.storage.hasOwnProperty(id)) {
					if(this.items.storage[id].group == item.group) {
						groupTotal++;
					}
				}
			}
			
			for(id=0; id<len; id++) {
				if(this.items.storage.hasOwnProperty(id)) {
					if(this.items.storage[id].group == item.group) {
						this.items.storage[id].groupIndex = ++groupIndex;
						this.items.storage[id].groupTotal = groupTotal;
					}
				}
			}
		},
		
		_updateGalleryCounter: function(item) {
			if(item.config.gallery && item.config.galleryCounter && item.config.galleryCounterMessage && item.groupTotal > 1) {
				var text = item.config.galleryCounterMessage.replace('[index]', item.groupIndex).replace('[total]', item.groupTotal);
				this.flow.$numeration.addClass('popup-show').text(text);
			} else {
				this.flow.$numeration.removeClass('popup-show');
			}
		},
		
		_updateTitle: function(item) {
			if(item.config.title) {
				var title = item.$el.data(ITEM_POPUP_TITLE) || item.$el.attr('title');
				if(title) {
					this.flow.$title.addClass('popup-show').html(title);
				} else {
					this.flow.$title.removeClass('popup-show');
				}
			} else {
				this.flow.$title.removeClass('popup-show');
			}
		},
		
		_updateDOM: function(item) {
			this._updateTitle(item);
			this._updateGalleryCounter(item);
			
			this.flow.$overlay.attr('class','popup-overlay');
			if(item.config.themeClass) {
				this.flow.$overlay.addClass(item.config.themeClass);
			}
		},
		//=============================================
		// Public Methods
		//=============================================
		show: function($el) {
			this._show($el);
		},
		
		close: function() {
			this._close();
		}
	}
	
	function _getInstance() {
		if(_instance == null) {
			_instance = new Popup();
			_instance._init();
		}
		return _instance;
	}
	function jStorage() {
		this.storage = {};
		
		this.get = function(key) {
			if(key in this.storage) {
				return this.storage[key];
			}
			return null;
		}
		this.set = function(key, item) {
			this.storage[key] = item;
		}
		this.del = function(key) {
			delete this.storage[key];
		}
		this.len = function() {
			return Object.keys(this.storage).length;
		}
	};
	
	function Util() {
		this.isMobile = function(agent) {
			return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(agent);
		};
		
		this.transitionEvent = function() {
			var el = document.createElement('fakeelement');
			
			var transitions = {
				'transition': 'transitionend',
				'OTransition': 'otransitionend',
				'MozTransition': 'transitionend',
				'WebkitTransition': 'webkitTransitionEnd'
			};
			
			for(var i in transitions){
				if (el.style[i] !== undefined){
					return transitions[i];
				}
			}
			
			return null;
		};
		
		this.animationEvent = function() {
			var el = document.createElement('fakeelement');
			
			var animations = {
				'animation'      : 'animationend',
				'MSAnimationEnd' : 'msAnimationEnd',
				'OAnimation'     : 'oAnimationEnd',
				'MozAnimation'   : 'mozAnimationEnd',
				'WebkitAnimation': 'webkitAnimationEnd'
			}
			
			for (var i in animations){
				if (el.style[i] !== undefined){
					return animations[i];
				}
			}
			
			return null;
		};
	}
	
	//=============================================
	// Init jQuery Plugin
	//=============================================
	/**	 * 
	 * List of methods:
	 * $("#popup").popup("instance")
	 */
	$.fn.popup = function(CfgObject, CfgArgs) {
		if (CfgObject == 'instance') {
			return _getInstance();
		}
		
		ITEM_GROUP_COUNTER++;
		
		return this.each(function() {
			var instance = _getInstance(),
			$el = $(this),
			group = ITEM_GROUP_COUNTER,
			options = $.isPlainObject(CfgObject) ? CfgObject : {},
			config = $.extend(true, {}, Popup.prototype.itemDefaults, options);
			
			instance._addItem($el, group, config);
		});
	}
})(jQuery, window, document);