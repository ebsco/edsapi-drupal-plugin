(function ($, Drupal, drupalSettings, doc) {
    "use strict";

    Drupal.behaviors.EBSCOAutocompleteDrupal = {
        attach: function authAutocomplete(context, settings) {

        context.addEventListener("keydown", keyBoard, false);

            function keyBoard(e) {
                var autocompleteToken = drupalSettings.autocomplete.autocompleteToken;
                var autocompleteurl = drupalSettings.autocomplete.autocompleteUrl;
                var autocompleteCustId = drupalSettings.autocomplete.autocompleteCustId;
                var searchvalue = e.target.value;
                var searchData = {
                    token: autocompleteToken,
                    term: searchvalue,
                    idx: "rawqueries",
                    filters: JSON.stringify([{
                        name: "custid",
                        values: [autocompleteCustId],
                        },
                    ]),
                };

                $(doc).ready(function () {
                    $.ajax({
                        type: "get",
                        url: autocompleteurl,
                        data: searchData
                    })
                    .done(startAutocomplete)
                    .fail(function (jqXHR, textStatus, msg) {
                        console.log('Error to call autocomplete.');
                    });
                });
            }

            function startAutocomplete(data) {
                var terms = data.terms.map(startTerm);
                jQuery("#lookfor").autocomplete({
                    source: terms
                    });
                return terms;            
            }

            function startTerm(wrapper) {
                console.log("wrapper.term: ", wrapper.term);

                var result = wrapper.term;
                return result;
            }
        },
    };
})(jQuery, Drupal, drupalSettings, document);
