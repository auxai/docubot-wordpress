/*
Copyright (C)  2017, 1LAW Legal Technologies, LLC

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
; (function (window, $, undefined) {
  var embedurl = undefined;
  var chatUI = undefined;
  var page_loaded = false;
  var iframe_loaded = false;
  if (docubot_documents.use_files) {
    embedurl = docubot_documents.embedurl;
  }

  $(window).on('message', function(e) {
    if (e && e.originalEvent && e.originalEvent.data && e.originalEvent.data.type == 'docubot-embed-loaded') {
      iframe_loaded = true;
      if (iframe_loaded && page_loaded && docubot_documents.use_files) {
        urlQueryParam();
      }
    }
  });

  $(function () {
    page_loaded = true;
    // Docubot Animation Stuff
    setupDocubotAnimation();
    $(".sprite-Docubot").on("click", function (e) {
      e.preventDefault();
      animateDocubot(function () {

        setupDocubotAnimation();

      });
      return false;
    });
    // End Docubot Animation Stuff

    // If on Shortcode page remove popup
    var containers = $(".docubot_container");
    if (containers.length > 1) {
      for (var i = 1; i < containers.length; i++) {
        containers[i].parentNode.removeChild(containers[i]);
      }
      var popup = $(".docubot_popup")[0];
      popup.parentNode.removeChild(popup);
    }

    chatUI = $("#docubot_iframe")[0];
    if (iframe_loaded && page_loaded && docubot_documents.use_files) {
      urlQueryParam();
    }

    //Buttons and document files
    $(".docubot_document_buttons").on("click", function (e) {
      var doc = undefined;
      var docTree = undefined;
      let docNumber = e.target.dataset.value;
      if (docNumber == 'doc1') {
        doc = JSON.parse(docubot_documents.documents.doc1.document);
        docTree = JSON.parse(docubot_documents.documents.doc1.doctree);
      } else if (docNumber == 'doc2') {
        doc = JSON.parse(docubot_documents.documents.doc2.document);
        docTree = JSON.parse(docubot_documents.documents.doc2.doctree);
      } else if (docNumber == 'doc3') {
        doc = JSON.parse(docubot_documents.documents.doc3.document);
        docTree = JSON.parse(docubot_documents.documents.doc3.doctree);
      }

      $(".docubot_popup .docubot_logo_container").hide();
      $(".docubot_popup .sprite-Docubot").hide();
      $(".docubot_document_buttons").hide();
      $(".docubot_message_container").show();
      updateChatUI(doc, docTree);
    });

  });

  // Uses query paramters
  function urlQueryParam() {
    var doc = undefined;
    var docTree = undefined;
    let queryParam = docubot_documents.queryParam;
    if (docubot_documents && docubot_documents.documents && docubot_documents.documents.doc1 && docubot_documents.documents.doc1.doctree && JSON.parse(docubot_documents.documents.doc1.doctree).documentName.toLowerCase() == queryParam.toLowerCase()) {
      doc = JSON.parse(docubot_documents.documents.doc1.document);
      docTree = JSON.parse(docubot_documents.documents.doc1.doctree);
    } else if (docubot_documents && docubot_documents.documents && docubot_documents.documents.doc2 && docubot_documents.documents.doc2.doctree && JSON.parse(docubot_documents.documents.doc2.doctree).documentName.toLowerCase() == queryParam.toLowerCase()) {
      doc = JSON.parse(docubot_documents.documents.doc2.document);
      docTree = JSON.parse(docubot_documents.documents.doc2.doctree);
    } else if (docubot_documents && docubot_documents.documents && docubot_documents.documents.doc3 && docubot_documents.documents.doc3.doctree && JSON.parse(docubot_documents.documents.doc3.doctree).documentName.toLowerCase() == queryParam.toLowerCase()) {
      doc = JSON.parse(docubot_documents.documents.doc3.document);
      docTree = JSON.parse(docubot_documents.documents.doc3.doctree);
    }
    updateChatUI(doc, docTree);
  }

  function updateChatUI(doc, docTree) {
    chatUI.contentWindow.postMessage(
      {
        type: 'docubot-data',
        data: {
          document: doc,
          docTree: docTree,
          variables: {},
          fillablePDF: null,
          showsPreviewDocButton: true
        }
      },
      embedurl
    );
  }

  var docubotAnimationInterval;
  var docubotAnimationTimeout;
  function animateDocubot(cb) {

    if (docubotAnimationInterval) { return; }
    var elm = $(".sprite-Docubot");
    if (!elm.length) { return; }
    var frame = 0;
    docubotAnimationInterval = window.setInterval(function () {

      if (frame === 0) {

        elm.removeClass("sprite-Docubot_Dance_173");

      } else {

        elm.removeClass("sprite-Docubot_Dance_" + (frame - 1));

      }
      elm.addClass("sprite-Docubot_Dance_" + frame);
      frame = frame + 1;
      if (frame > 173) { // there are 174 frames in the animation

        stopDocubotAnimation();
        if (cb) { cb(); }

      }

    }, 33);

  }
  function setupDocubotAnimation() {

    if (docubotAnimationInterval || docubotAnimationTimeout) { return; }
    docubotAnimationTimeout = window.setTimeout(function () {

      docubotAnimationTimeout = null;
      animateDocubot(function () {

        setupDocubotAnimation();

      });

    }, Math.floor(Math.random() * 7000) + 3000);

  }
  function stopDocubotAnimation() {

    if (docubotAnimationInterval) {

      window.clearInterval(docubotAnimationInterval);
      docubotAnimationInterval = null;

    }
    if (docubotAnimationTimeout) {

      window.clearTimeout(docubotAnimationTimeout);
      docubotAnimationTimeout = null;

    }

  }

})(window, jQuery);
