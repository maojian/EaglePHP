/*
 JavaScript Document, James' jQuery plugin set by 梁达俊2011-10-15
 for jquery 1.7+
 JS Compressor URL:http://javascriptcompressor.com/, please compress this code before pub_lishing.
 */
(function ($) {
	$.fn.extend({
		//Menu---->ul>li*n>a+ul>li*n...
		menu: function (param, arg) {
			var data = this.data(),
				defaults = data.settings || {
					openOnHover: true,
					hlClass: "hl",
					delay: 200,
					timeout: 500
				},
				subs = this.find("li>div").hide(),
				lis = this.find("li"),
				tops = this.children("li"),
				to = -1,
				cpath = $(),
				action, listener;
			//<event handler
			function onMouseEnter() {
				if(to >= 0) {
					clearTimeout(to);
					to = -1;
				}
			}

			function onMouseLeave() {
				if(data.settings.openOnHover && to < 0) to = setTimeout(reset, data.settings.timeout);
				else if(!data.expanded) reset();
			}

			function topItemMouseenter(e) {
				data.expanded = true;
			}

			function topItemClicked(e) {
				if(data.expanded) reset();
				else data.expanded = true;
				onItemAction(e);
				e.cancelBubble = true;
				e.stopPropagation();
			}

			function onItemAction(e) {
				var li = $(e.data);
				var hpath = li.parentsUntil(data.obj, "li").add(li);
				if(cpath != hpath) {
					if(data.expanded) {
						if(data.settings.delay > 0) li.parent().find("div").not(li.children("div").stop(true, true).fadeIn(data.settings.delay)).stop(true, true).fadeOut(data.settings.delay);
						else li.parent().find("div").not(li.children("div").show()).hide();
					}
					cpath.removeClass(data.settings.hlClass);
					hpath.addClass(data.settings.hlClass);
					cpath = hpath;
				}
			}

			function documentClicked() {
				reset();
			};
			//end event handler>
			function reset() {
				if(data.expanded) {
					subs.stop(true, false).fadeOut(data.settings.delay);
					lis.removeClass(data.settings.hlClass);
					cpath = $();
					data.expanded = false;
				} else tops.removeClass(data.settings.hlClass);
			}

			function init() {
				action = data.settings.openOnHover ? "mouseenter" : "click";
				listener = data.settings.openOnHover ? topItemMouseenter : topItemClicked;
				data.expanded = false;
				tops.each(function () {
					$(this).children().first().bind(action, this, listener);
				});
				lis.each(function () {
					$(this).children().first().bind("mouseenter", this, onItemAction);
				});
				data.obj.bind({
					mouseleave: onMouseLeave,
					mouseenter: onMouseEnter
				});
				$(document).bind("click", documentClicked);
			}

			function destory() {
				tops.each(function () {
					$(this).children().first().unbind(action, listener);
				});
				lis.each(function () {
					$(this).children().first().unbind("mouseenter", onItemAction);
				});
				data.obj.unbind({
					mouseleave: onMouseLeave,
					mouseenter: onMouseEnter
				});
			}
			if(this.length > 1) {
				this.each(function () {
					$(this).menu(param, arg);
				});
			} else {
				switch(param) {
				case "destory":
					destory();
					break;
				case "reset":
					reset();
					break;
				default:
					$.extend(defaults, param);
					this.data("settings", defaults).data("obj", this);
					init();
					break;
				}
			}
			return this;
		}
	})
})(jQuery);