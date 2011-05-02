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
}
).use("node-menunav", "io-form", "overlay", "anim-base", "json-parse", function(Y)
{
  // Render the nav menu.
  Y.on("contentready", function () {
    this.plug(Y.Plugin.NodeMenuNav);
    // Load custom CSS for YUI menu.
    Y.Get.css('css/onpub-menu.css');
    this.get("ownerDocument").get("documentElement").removeClass("yui3-loading");
  }, "#onpub-menubar");

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

  function confirmDeleteArticle(e)
  {
    var result = false;
    result = confirm("Are you sure you want to delete the selected article(s)?");

    if (!result) {
      e.preventDefault();
    }
  }

  function confirmDelete(e, message, onpubForm, onpubAction, onpubActionValue)
  {
    var result = false;
    result = confirm(message);

    if (result) {
      onpubAction.set("value", onpubActionValue);
      onpubForm.submit();
    }
    else {
      e.preventDefault();
    }
  }

  function saveArticleStart(tid, overlay)
  {
    overlay.set("bodyContent", "Saving..");
  }

  function saveArticleComplete(tid, response, overlay)
  {
  }

  function updateArticleTitle(articleID, oldTitle, newTitle)
  {
    oldTitle.set("innerHTML", "Article " + articleID.get("value") + " - " + newTitle.get("value"));
  }

  function saveArticleSuccess(tid, response, overlay, textarea, textareaVal)
  {
    if (response.responseText) {
      // There was an error.
      try {
        var error = Y.JSON.parse(response.responseText);
        overlay.set("bodyContent", '<span style="color: red;">Save error: ' + error.message + '.</span>');
      }
      catch (e) {
        Y.log(e);
      }

      // Set textarea back to value before error.
      textarea.set("value", textareaVal);
    }
    else {
      overlay.set("bodyContent", "Saved.");
      updateArticleTitle(Y.one("#articleID"), Y.one("#onpub-body h1"), Y.one("input[name='title']"));
    }
  }

  function saveArticleFailure(tid, response, overlay, textarea, textareaVal)
  {
    overlay.set("bodyContent", '<span style="color: red;">Save error. Try again..</span>');

    // Set textarea back to value before error.
    textarea.set("value", textareaVal);
  }

  function saveArticleEnd(tid, overlay)
  {
    var anim = new Y.Anim({
      node: "#" + overlay.getAttrs().id,
      to: {opacity: 0},
      duration: 1
    });

    Y.later(1500, anim, "run", null, false);
  }

  function saveArticle(e, textarea, overlay)
  {
    textarea.set("value", CKEDITOR.instances["content"].getData());

    var cfg = {
      method: "POST",
      form: {id: "onpub-form"}
    };

    // Reset overlay opacity.
    Y.one("#" + overlay.getAttrs().id).setStyle("opacity", 1);
    overlay.set("visible", true);

    Y.io("index.php", cfg);
  }

  function confirmNewPage(e, action)
  {
    var result = false;
    result = confirm("Are you sure want to blank this page?");

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

  function confirmOverwrite(e, overwrite)
  {
    var result = false;
    result = confirm("Are you sure want to overwrite this image?");

    if (result) {
      overwrite.set("value", "1");
    }
    else {
      e.preventDefault();
    }
  }

  function checkUnsavedChanges(e)
  {
    // Code partially borrowed from:
    // https://developer.mozilla.org/en/DOM/window.onbeforeunload
    var e = e || window.event;
    var textarea = Y.one("textarea[name='content']");
    var m = "You have unsaved Content changes. Your changes will be lost if you leave this page.";

    if (Y.Lang.trim(CKEDITOR.instances["content"].getData()) != Y.Lang.trim(textarea.get("value"))) {
      if (e) {
        e.returnValue = m;
      }

      return m;
    }
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

  if (Y.one("#deleteArticle")) {
    Y.on("click", deleteArticle, "#deleteArticle", null, Y.one("#articleID"));
  }

  if (Y.one("#confirmDeleteArticle")) {
    Y.on("click", confirmDeleteArticle, "#confirmDeleteArticle");
  }

  if (Y.one("#deleteImage")) {
    Y.on("click", confirmDelete, "#deleteImage", null, "Are you sure you want to delete this image?", Y.one("#onpub-form"), Y.one("input[name='onpub']"), "DeleteImageProcess");
  }

  if (Y.one("#deleteSection")) {
    Y.on("click", confirmDelete, "#deleteSection", null, "Are you sure you want to delete this section?", Y.one("#onpub-form"), Y.one("input[name='onpub']"), "DeleteSectionProcess");
  }

  if (Y.one("#deleteWebsite")) {
    Y.on("click", confirmDelete, "#deleteWebsite", null, "Are you sure you want to delete this website?", Y.one("#onpub-form"), Y.one("input[name='onpub']"), "DeleteWebsiteProcess");
  }

  if (Y.one("#actions")) {
    Y.on("change", performAction, "#actions", null, Y.all("#articleIDs"));
  }

  Y.on("contentready", function () {
    // Override default CKEditor Save action
    if (Y.one("input[name='onpub']").get("value") == "EditArticleProcess") {
      var textarea = Y.one("textarea[name='content']");
      var textareaVal = textarea.get("value");
      var node = this;
      node.set("onclick", null);

      // Setup the AJAX status overlay
      var overlay = new Y.Overlay({
        bodyContent: "",
        visible: false,
        align: {
          node: "#onpub-body",
          points: [Y.WidgetPositionAlign.TR, Y.WidgetPositionAlign.TR]
        }
      });

      overlay.render("#onpub-body");

      // Setup the AJAX event handlers
      Y.on("io:start", saveArticleStart, Y, overlay);
      Y.on("io:complete", saveArticleComplete, Y, overlay);
      Y.on("io:success", saveArticleSuccess, Y, overlay, textarea, textareaVal);
      Y.on("io:failure", saveArticleFailure, Y, overlay, textarea, textareaVal);
      Y.on("io:end", saveArticleEnd, Y, overlay);

      // Setup the new CKEditor Save button click handler
      Y.on("click", saveArticle, node, null, textarea, overlay);
    }

    // Define unload handler to warn user of unsaved changes.
    // YUI onbeforeunload handler does not work properly in all browsers.
    // User native DOM handler instead for better browser compatibility.
    window.onbeforeunload = checkUnsavedChanges;
  }, ".cke_button_save");

  Y.on("contentready", function () {
    // Override default CKEditor New Page action
    if (Y.one(".cke_button_newpage")) {
      var node = this;
      var action = node.get("onclick");

      node.set("onclick", null);

      Y.on("click", confirmNewPage, node, null, action);
    }
  }, ".cke_button_newpage");

  if (Y.one("#overwriteImage")) {
    Y.on("click", confirmOverwrite, "#overwriteImage", null, Y.one("input[name='overwrite']"));
  }
}
);

