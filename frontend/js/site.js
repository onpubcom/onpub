/* Onpub (TM)
 * Copyright (C) 2010 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

YUI(
{
  base: (onpub_dir_yui == null) ? "http://yui.yahooapis.com/combo?" + onpub_yui_version + "/build/" : onpub_dir_yui
}).use("node-menunav", "transition", function(Y)
{
  var menu = Y.one("#onpub-menubar");

  if (menu) {
    menu.plug(Y.Plugin.NodeMenuNav);

    // Load custom CSS for YUI menu.
    Y.Get.css(onpub_inc_css_menu);

    menu.setStyle('height', '0px');
    Y.all('.yui3-menu-content').setStyle('opacity', '0');

    menu.get("ownerDocument").get("documentElement").removeClass("yui3-loading");

    menu.transition({
      easing: 'ease-out',
      duration: .2,
      height: '27px',
    });

    Y.all('.yui3-menu-content').transition({
      easing: 'ease-out',
      duration: .2,
      opacity: '1',
    });
  }
});
