/* Onpub (TM)
 * Copyright (C) 2012 Onpub.com <http://onpub.com/>
 * Author: Corey H.M. Taylor <corey@onpub.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2.
 */

YUI(
{
  base: (onpub_dir_yui == null) ? "http://yui.yahooapis.com/combo?" + onpub_yui_version + "/build/" : onpub_dir_yui,
  fetchCSS: false // Don't fetch CSS dependencies since we load them in <head>
}
).use("node-menunav", "io-form", "overlay", "anim-base", "json-parse", function(Y)
{
  // Render the nav menu.
  Y.on("contentready", function () {
    this.plug(Y.Plugin.NodeMenuNav);
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
      alert('Select an item to hide');
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
    overlay.set("headerContent", '<div class="onpub-save-overlay">Saving..</div>');
  }

  function saveArticleComplete(tid, response, overlay)
  {
  }

  function updateArticleTitle(articleID, oldTitle, newTitle)
  {
    oldTitle.set("innerHTML", "Article " + articleID.get("value") + " - " + newTitle.get("value"));
  }

  function saveArticleSuccess(tid, response, overlay)
  {
    if (response.responseText) {
      // There was an error.
      try {
        var error = Y.JSON.parse(response.responseText);
        overlay.set("headerContent", '<div class="onpub-save-overlay"><span style="color: red;">Save error: ' + error.message + '.</span></div>');
      }
      catch (e) {
        Y.log(e);
      }
    }
    else {
      overlay.set("headerContent", '<div class="onpub-save-overlay">Saved.</div>');
      updateArticleTitle(Y.one("#articleID"), Y.one("#onpub-body h1"), Y.one("input[name='title']"));
    }
  }

  function saveArticleFailure(tid, response, overlay)
  {
    overlay.set("headerContent", '<div class="onpub-save-overlay"><span style="color: red;">Save error. Try again..</span></div>');
  }

  function saveArticleEnd(tid, overlay)
  {
    var anim = new Y.Anim({
      node: "#" + overlay.getAttrs().id,
      to: {opacity: 0},
      duration: 1
    });

    Y.later(2000, anim, "run", null, false);
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

    var request = Y.io("index.php", cfg);
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

  // Replaces Windows and Mac new lines with unix style: \r or \r\n with \n.
  // Borrowed from:
  // https://closure-library.googlecode.com/svn/docs/closure_goog_string_string.js.source.html#line226
  function canonicalizeNewlines(str) {
    return str.replace(/(\r\n|\r|\n)/g, '\n');
  };

  function checkUnsavedChanges(e)
  {
    // Code partially borrowed from:
    // https://developer.mozilla.org/en/DOM/window.onbeforeunload
    var e = e || window.event;
    var textarea = Y.one("textarea[name='content']");
    var m = "You have unsaved changes. Your changes will be lost if you leave this page.";
    var ckdata = canonicalizeNewlines(Y.Lang.trim(CKEDITOR.instances["content"].getData()));
    var tareadata = canonicalizeNewlines(Y.Lang.trim(textarea.get("value")));

    if (ckdata != tareadata) {
      if (e) {
        e.returnValue = m;
      }

      return m;
    }
  }

  function previewImage(e, widgetImage, widgetImageName, onpubThumbURLs, overlay)
  {
    var currentTarget, options, selectedIndex, option, parent;

    if (e.currentTarget) {
      currentTarget = e.currentTarget;
    }
    else {
      currentTarget = e;
    }

    if (currentTarget.get("type") == "select-one") {
      options = currentTarget.get("options");
      selectedIndex = currentTarget.get("selectedIndex");
      option = options.item(selectedIndex);
      options.setStyles({"backgroundColor":"#ffffff", "color":"#525861"});
      widgetImageName.set("textContent", option.get("text"));
    }
    else {
      option = currentTarget;
      parent = option.get("parentNode");
      options = parent.get("options");
      options.setStyles({"backgroundColor":"#ffffff", "color":"#525861"});
      option.setStyles({"backgroundColor":"#0C7CCF", "color":"#ffffff"});
      widgetImageName.set("textContent", option.get("text"));
    }

    var thumbIndex = option.get("index") - 1;
    var thumbURL;

    if (thumbIndex == -1) {
      widgetImage.setStyle("display", "none");
      widgetImageName.set("textContent", "");
    }
    else {
      thumbURL = onpubThumbURLs[thumbIndex];
      widgetImage.set("src", thumbURL);
      widgetImage.setStyle("display", "inline");
      parent = widgetImage.get("parentNode");
      parent.set("href", "index.php?onpub=EditImage&imageID=" + option.get("value"));
      overlay.set("visible", true);
    }
  }

  // Register event handlers.
  if (Y.one("#articles")) {
    Y.on("click", moveUp, "#moveUp", null, Y.one("#articles"));
    Y.on("click", moveDown, "#moveDown", null, Y.one("#articles"));
    Y.on("click", remove, "#hide", null, Y.one("#articles"));
    Y.on("click", sortByDate, "#sortByDate", null, Y.one("#articles"));
    Y.on("click", selectAll, "#selectAll", null, Y.one("#articles"));
  }

  if (Y.one("#existing") && Y.one("#sections")) {
    Y.on("click", add, "#add", null, Y.all("#existing option"), Y.one("#sections"));
    Y.on("click", moveUp, "#moveUp", null, Y.one("#sections"));
    Y.on("click", moveDown, "#moveDown", null, Y.one("#sections"));
    Y.on("click", remove, "#hide", null, Y.one("#sections"));
    Y.on("click", selectAll, "#selectAll", null, Y.one("#sections"));
  }

  if (Y.one("#sections") && Y.one("input[name='onpub']").get("value") == "EditArticles") {
    Y.on("change", function (e) {document.forms[0].submit();}, "#sections");
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
      var node = this;
      node.set("onclick", null);
      node.set("onmouseup", null);

      // Setup the AJAX status overlay
      var overlay = new Y.Overlay({
        headerContent: '<div class="onpub-save-overlay"></div>',
        visible: false,
        align: {
          node: "#cke_content",
          points: [Y.WidgetPositionAlign.TR, Y.WidgetPositionAlign.TR]
        }
      });

      overlay.render("#cke_content");

      // Setup the AJAX event handlers
      Y.on("io:start", saveArticleStart, Y, overlay);
      Y.on("io:complete", saveArticleComplete, Y, overlay);
      Y.on("io:success", saveArticleSuccess, Y, overlay);
      Y.on("io:failure", saveArticleFailure, Y, overlay);
      Y.on("io:end", saveArticleEnd, Y, overlay);

      // Setup the new CKEditor Save button click handler
      Y.on("click", saveArticle, node, null, Y.one("textarea[name='content']"), overlay);
    }

    // Define unload handler to warn user of unsaved changes.
    // YUI onbeforeunload handler does not work properly in all browsers.
    // User native DOM handler instead for better browser compatibility.
    window.onbeforeunload = checkUnsavedChanges;
  }, ".cke_button_save");

  Y.on("contentready", function () {
    if (Y.one(".cke_button_newpage")) {
      // Hide the New Page button since it's to easy to click and lose your
      // edits by mistake!
      this.hide();
    }
  }, ".cke_button_newpage");

  if (Y.one("#overwriteImage")) {
    Y.on("click", confirmOverwrite, "#overwriteImage", null, Y.one("input[name='overwrite']"));
  }

  if (Y.one("#widgetimagepreview")) {
    // Setup the image preview overlay.
    var overlay = new Y.Overlay({
      headerContent: '<a href=""><img id="widgetimage" src="" alt="Edit" title="Edit" class="onpub-image-preview"></a>' +
                     '<p id="widgetimagename" class="onpub-image-preview-name"></p>',
      visible: false,
      align: {
        node: "#widgetimagepreview",
        points: [Y.WidgetPositionAlign.TL, Y.WidgetPositionAlign.TR]
      }
    });

    overlay.render("#widgetimagepreview");

    Y.on("mouseover", previewImage, "#widgetimages option", null,
         Y.one("#widgetimage"), Y.one("#widgetimagename"), onpubThumbURLs, overlay);
    Y.on("mouseout", previewImage, "#widgetimages", null, Y.one("#widgetimage"),
         Y.one("#widgetimagename"), onpubThumbURLs, overlay);

    previewImage(Y.one("#widgetimages"), Y.one("#widgetimage"),
                 Y.one("#widgetimagename"), onpubThumbURLs, overlay);
  }
}
);

