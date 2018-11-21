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
;(function(window, $, undefined) {
$( function() {
  $('#docubot_use_popup').on('change', function() {
    if ( $(this).is(':checked') ) {
      $('.docubot_use_side').show();
    } else {
      $('.docubot_use_side').hide();
    }
  })
  $('.delete-doc-button').on('click', function() {
    var docNumber = $(this).data('docnumber');
    $('#doc_name_' + docNumber).text('');
    $('#docubot_doctree_' + docNumber).val(undefined);
    $('#docubot_document_' + docNumber).val(undefined);
    $(this).hide();
  });
  $('#docubot_use_files').on('change', function() {
    if ( $(this).is(':checked') ) {
      $('.docubot-files-row').show();
    } else {
      $('.docubot-files-row').hide();
    }
  });
  $('.docubot-file-picker-button').on('click', function() {
    var fileInput = $(this).find('input[type=file]');
    fileInput[0].click();
  });
  $('.docubot-file-picker-input').on('change', function(e) {
    var file = this.files[0];
    var docNumber = $(this).data('docnumber');
    if (!file) {
      return;
    }
    var fileExtension = file.name.split('.').slice(-1)[0].toLowerCase();
    var validFileExtensions = ['docubot', 'botineer', 'zip'];
    if (validFileExtensions.indexOf(fileExtension) === -1) {
      console.error("Invalid file extension: " + fileExtension + ". Expected file with extension .docubot, .botineer or .zip.");
      alert("Invalid file extension: " + fileExtension + ". Expected file with extension .docubot, .botineer or .zip.");
      return;
    }
    JSZip.loadAsync(file)
      .then(function(zip) {
        zip.forEach(function(path, zipEntry) {
          if (path == 'doc-tree.json') {
            zipEntry.async('string').then(function(content) {
              try {
                var docTreeJson = JSON.parse(content);
                $('#doc_name_' + docNumber).text(docTreeJson.documentName);
                $('#doc_name_' + docNumber).siblings('.delete-doc-button').show();
                $('#docubot_doctree_' + docNumber).val(content);
              } catch (e) {
                alert('This file contains an unreadable format!');
              }
            });
          } else if (path == 'document.json') {
            zipEntry.async('string').then(function(content) {
              $('#docubot_document_' + docNumber).val(content);
            });
          }
        });
      }, function() {
        alert('This file contains an unreadable format!');
      });
  });
});
})(window, jQuery);
