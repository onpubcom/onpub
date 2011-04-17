/* Onpub (TM)
 * Copyright (C) 2011 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

YUI(
{
  base: (onpub_dir_yui == null) ? "http://yui.yahooapis.com/combo?" + onpub_yui_version + "/build/" : onpub_dir_yui
}).use("node-menunav", function(Y)
{
  if (Y.one("#onpub-menubar")) {
    // Render the nav menu.
    Y.on("contentready", function () {
      this.plug(Y.Plugin.NodeMenuNav);
      // Load custom CSS for YUI menu.
      Y.Get.css(onpub_inc_css_menu);
      if (onpub_inc_css_menu_local) {
        Y.Get.css(onpub_inc_css_menu_local);
      }
      this.get("ownerDocument").get("documentElement").removeClass("yui3-loading");
    }, "#onpub-menubar");
  }
});
