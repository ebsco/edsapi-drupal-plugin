/**
 * @file
 * The EBSCO module javascript.
 *
 * Copyright [2017] [EBSCO Information Services]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
(function ($) {
    $(document).ready(function () {

    var updatePublishDateSlider = function () {
        var from = parseInt($('#DT1').val());
        var min = 1000;

        if (!from || from < min) {
            from = min;
        }

        // and keep the max at 1 years from now
        var max = (new Date()).getFullYear() + 1;
        var to = max;

        // update the slider with the new min/max/values
        $('#ebsco-advanced-search-sliderDT1').slider('option', {
            min: min, max: max, values: [from, to]
        });
    };

    /**
     * Self executing function
     */
    var onLoad = function () {
        // EBSCO/Search : Expand limiters
        $('._more_limiters').live('click', function (event) {
            $("#moreLimiters").hide();
            $("#limitersHidden").removeClass("offscreen");
        });

        // Search : Collapse limiters
        $('._less_limiters').live('click', function (event) {
            $("#moreLimiters").show();
            $("#limitersHidden").addClass("offscreen");
        });

        // EBSCO/Search : Collapse / expand facets
        $('.expandable').live('click', function (event) {
            var span = $(this).find('dt span'),
                id = $(this).attr('id').replace('facet-','');
            if (span.length > 0) {
                if (span.hasClass('collapsed')) {
                    $('#narrowGroupHidden_' + id).show();
                    span.removeClass('collapsed');
                    span.addClass('expanded');
                } else if (span.hasClass('expanded')) {
                    $('#narrowGroupHidden_' + id).hide();
                    span.removeClass('expanded');
                    span.addClass('collapsed');
                }
            } else if ($(this).attr('href')) {
                var dl = $(this).parents('dl'),
                    id = dl.attr('id').replace('narrowGroupHidden_', ''),
                    span = $('#facet-' + id).find('dt span');
                dl.hide();
                span.removeClass('expanded');
                span.addClass('collapsed');
            }
        });

        // EBSCO/Search : Less facets
        $('._less_facets').live('click', function (event) {
            var id = $(this).attr('id').replace('less-facets-','');
            var dl = $('#facet-' + id);
            dl.trigger('click');
        });

        // Search : Ajax request the Record action
        $('._record_link').live('click', function (event) {
            var element = $(this);
            var position = element.position();
            event.preventDefault();
            $('#spinner').show();
            $("#spinner").offset({left:event.pageX - 18,top:event.pageY - 18});

            $.get(element.attr('href'), function (data) {
                $('#main').html(data);
                $('#spinner').hide();
            });
        });

        // Advanced Search : Add a new search term
        $('._add_row').live('click', function (event) {
            event.preventDefault();
            var newSearch = $('#advanced-row-template').html();
            var rows = $('._advanced-row');
            if (rows) {
                // Find the index of the next row
                var index = rows.length - 1; // one row is the template itself, so don't count it
                // Replace NN string with the index number
                newSearch = newSearch.replace(/NN/g, index);
                lastSearch = $('#edit-add-row');
                lastSearch.before(newSearch);
            }
        });

        // Advanced Search : Delete an advanced search row
        $('._delete_row').live('click', function (event) {
            event.preventDefault();
            $(this).parents('._advanced-row').remove();
        });

        // Advanced Search : Reset the form fields to default values
        $('.ebsco-advanced input[name="reset"]').live('click', function (event) {
            event.preventDefault();
            $('#ebsco-advanced-search-form').find('input, select').each(function (index) {
                var type = this.type;
                switch(type) {
                    case 'text':
                        $(this).val('');
                        break;
                    case 'checkbox':
                        $(this).attr('checked', '');
                        break;
                    case 'select-multiple':
                        $(this).children('option').each(function (index) {
                            $(this).attr('selected', '');
                        });
                        break;
                    case 'select-one':
                        $(this).children('option').each(function (index) {
                            $(this).attr('selected', '');
                        });
                        // for IE
                        $(this).children('option:first').attr('selected', 'selected');
                        break;
                    case 'radio':
                        $(this).attr('checked', '');
                        $(this).parent().siblings().first().children('input:first').attr('checked', 'checked');
                        break;
                }
            });
        });

        // Auto submit the seelct boxes with '_jump_menu' class
        $('._jump_menu').live('change', function (event) {
            var name = $(this).attr('id').replace('ebsco-', ''),
                value = $(this).attr('value'),
                url = $('#ebsco-sort-form').attr('action');
            url += "&" + name + "=" + value;
            window.location.href = url;
        });

        // Retain search filters checkbox functionality
        $('#edit-remember').live('click', function (event) {
            $("#ebsco-basic-search-form :input[type='checkbox'][name^='filter[']").attr('checked', $(this).attr('checked'));
        });

        // Advanced Search : handle 'Date Published from' limiter
        // Create the UI slider (if slider function is defined)
        if(typeof $("#ebsco-advanced-search-sliderDT1").slider == 'function') {

            $('#ebsco-advanced-search-sliderDT1').slider({
                range: true,
                min: 0, max: 9999, values: [0, 9999],
                slide: function (event, ui) {
                    $('#DT1').val(ui.values[0]);
                    if(ui.values[0] == 1000) {
                        $('#ebsco-advanced-search-limiterDT1').val('');
                    } else {
                        $('#ebsco-advanced-search-limiterDT1').val('addlimiter(DT1:' + ui.values[0] + '-1/2013-1)');
                    }
                }
            });

            // initialize the slider with the original values
            // in the text boxes
            updatePublishDateSlider();

            // when user enters values into the boxes
            // the slider needs to be updated too
            $('#DT1').change(function(){
                updatePublishDateSlider();
            });
        }
    }();


});
})(jQuery);
