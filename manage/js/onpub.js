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
  base: (onpub_dir_yui == null) ? "http://yui.yahooapis.com/combo?3.1.2/build/" : onpub_dir_yui,
  timeout: 10000
}
).use("node", "node-menunav", function(Y)
{
  // Load and display the menubar.
  var menu = Y.one("#onpub-menubar");
  menu.plug(Y.Plugin.NodeMenuNav);
  menu.get("ownerDocument").get("documentElement").removeClass("yui3-loading");

  Y.Get.css("css/onpub-menu.css");

  // Event handler functions.
  function moveUp(e, list)
  {
    var options = list.get("options");
    var index = list.get("selectedIndex");
    var node;

    if (index == -1) {
      alert('Select an item to move up');
      return;
    }

    if (index == 0) {
      return;
    }

    node = options.item(index).remove();
    list.insertBefore(node, options.item(index - 1));
  }

  function moveDown(e, list)
  {
    var options = list.get("options");
    var index  = list.get("selectedIndex");
    var node;

    if ((index == -1)) {
      alert('Select an item to move down');
      return;
    }

    node = options.item(index).remove();
    list.insertBefore(node, options.item(index + 2));
  }

  function remove(e, list)
  {
    var options = list.get("options");
    var index  = list.get("selectedIndex");
    var option;

    if (index == -1) {
      alert('Select an item to remove');
      return;
    }

    for (var i = 0; i < options.size(); i++) {
      option = options.item(i);

      if (option.get("selected")) {
        option.remove();

        // Also remove this option from global articles array if present.
        if (window.onpub_articles) {
          var new_onpub_articles = Array();

          for (var j = 0; j < window.onpub_articles.length; j++) {
            if (option.get('value') != window.onpub_articles[j].ID) {
              new_onpub_articles.push(window.onpub_articles[j]);
            }
          }

          window.onpub_articles = new_onpub_articles;
        }
      }
    }

    if (list.get("length") == 0) {
      list.insert(Y.Node.create('<option value="">None</option>'), 0);
    }
  }

  function sortByDateAsc(a1, a2)
  {
    if (a1.created > a2.created) return 1;
    if (a1.created < a2.created) return -1;
    return 0;
  }

  function sortByDateDesc(a1, a2)
  {
    if (a1.created > a2.created) return -1;
    if (a1.created < a2.created) return 1;
    return 0;
  }

  var sortOrder = 'desc';

  function sortByDate(e, list)
  {
    var options = list.get("options");

    if (sortOrder == 'desc') {
      window.onpub_articles.sort(sortByDateDesc);
      sortOrder = 'asc';
    }
    else {
      window.onpub_articles.sort(sortByDateAsc);
      sortOrder = 'desc';
    }

    for (var i = 0; i < options.size(); i++) {
      option = options.item(i);

      option.set('value', window.onpub_articles[i].ID);
      option.set('text', window.onpub_articles[i].title);
    }
  }

  function add(e, options, list)
  {
    var option;
    var listOptions;
    var selected = false;

    for (var i = 0; i < options.size(); i++) {
      option = options.item(i);

      if (option.get("selected")) {
        listOptions = list.get("options");

        if (listOptions.item(0).get("text") == "None") {
          listOptions.item(0).remove();
        }

        option = option.cloneNode(true);
        option.set("selected", true);
        list.append(option);
        selected = true;
      }
    }

    if (!selected) {
      alert('Select an item to add');
    }
  }

  function selectAll(e, list)
  {
    var options = list.get("options");
    var option;

    for (var i = 0; i < options.size(); i++) {
      option = options.item(i);
      option.set("selected", true);
    }
  }

  function confirmDelete(e)
  {
    var result = false;
    result = confirm( "Are you sure?");

    if (!result) {
      e.preventDefault();
    }
  }

  function confirmNewPage(e, action)
  {
    var result = false;
    result = confirm( "Are you sure?");

    if (result) {
      action();
    }
  }

  function deleteArticle(e, articleID)
  {
    window.location.replace("index.php?onpub=DeleteArticle&articleIDs[]=" + articleID.get("value"));
  }

  function performAction(e, articleIDs)
  {
    var action = "index.php?onpub=" + e.target.get("value");
    var item;

    for (var i = 0; i < articleIDs.size(); i++) {
      item = articleIDs.item(i);

      if (item.get("checked")) {
        action += "&articleIDs[]=" + item.get("value");
      }
    }

    window.location.replace(action);
  }

  // Register event handlers.
  if (Y.one("#articles")) {
    Y.on("click", moveUp, "#moveUp", null, Y.one("#articles"));
    Y.on("click", moveDown, "#moveDown", null, Y.one("#articles"));
    Y.on("click", remove, "#remove", null, Y.one("#articles"));
    Y.on("click", sortByDate, "#sortByDate", null, Y.one("#articles"));
    Y.on("click", selectAll, "#selectAll", null, Y.one("#articles"));
  }

  if (Y.one("#existing") && Y.one("#sections")) {
    Y.on("click", add, "#add", null, Y.all("#existing option"), Y.one("#sections"));
    Y.on("click", moveUp, "#moveUp", null, Y.one("#sections"));
    Y.on("click", moveDown, "#moveDown", null, Y.one("#sections"));
    Y.on("click", remove, "#remove", null, Y.one("#sections"));
    Y.on("click", selectAll, "#selectAll", null, Y.one("#sections"));
  }

  if (Y.one("#confirmDelete")) {
    Y.on("click", confirmDelete, "#confirmDelete");
  }

  if (Y.one("#deleteArticle")) {
    Y.on("click", deleteArticle, "#deleteArticle", null, Y.one("#articleID"));
  }

  if (Y.one("#actions")) {
    Y.on("change", performAction, "#actions", null, Y.all("#articleIDs"));
  }

  if (Y.one("a.cke_button_newpage")) {
    var node = Y.one("a.cke_button_newpage");
    var action = node.get("onclick");

    node.set("onclick", null);

    Y.on("click", confirmNewPage, node, null, action);
  }
}
);

function deleteImage()
{
    var result = false;

    result = confirm( "Are you sure?");

    if ( result ) {
        document.forms[0].onpub.value = "DeleteImageProcess";
        document.forms[0].submit();
    }
}

function deleteSection()
{
    var result = false;

    result = confirm( "Are you sure?");

    if ( result ) {
        document.forms[0].onpub.value = "DeleteSectionProcess";
        document.forms[0].submit();
    }
}

function deleteWebsite()
{
    var result = false;

    result = confirm( "Are you sure?");

    if ( result ) {
        document.forms[0].onpub.value = "DeleteWebsiteProcess";
        document.forms[0].submit();
    }
}
