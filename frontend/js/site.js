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
  base: onpub_dir_root + "yui/build/",
  timeout: 10000
} ).use("node-menunav", function(Y)
{
  var menu = Y.one("#onpub-menubar");
  if (menu) {
    menu.plug(Y.Plugin.NodeMenuNav);
    menu.get("ownerDocument").get("documentElement").removeClass("yui3-loading");
    menu.setStyle('border-color', 'white');
  }
});

