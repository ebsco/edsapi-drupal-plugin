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
//(function (jQuery) {
	
try
{

	jQuery(document).ready(function () {
		//alert("after document ready");

		var updatePublishDateSlider = function () {
			var from = parseInt(jQuery('#DT1').val());
			var min = 1000;

			if (!from || from < min) {
				from = min;
			}

			// and keep the max at 1 years from now
			var max = (new Date()).getFullYear() + 1;
			var to = max;

			// update the slider with the new min/max/values
			jQuery('#ebsco-advanced-search-sliderDT1').slider('option', {
				min: min, max: max, values: [from, to]
			});
		};

		/**
		 * Self executing function
		 */
			// EBSCO/Search : Expand limiters
			jQuery('._more_limiters').on('click', function (event) {
				jQuery("#moreLimiters").hide();
				jQuery("#limitersHidden").removeClass("offscreen");
			});

			// Search : Collapse limiters
			jQuery('._less_limiters').on('click', function (event) {
				jQuery("#moreLimiters").show();
				jQuery("#limitersHidden").addClass("offscreen");
			});

			// EBSCO/Search : Collapse / expand facets
			jQuery('.expandable').on('click', function (event) {
				var span = jQuery(this).find('dt span');
				var id = jQuery(this).attr('id').replace('facet-','');
				
				if (span.length > 0) {
					if (span.hasClass('collapsed')) {
						jQuery('#narrowGroupHidden_' + id).show();
						span.removeClass('collapsed');
						span.addClass('expanded');
					} else if (span.hasClass('expanded')) {
						jQuery('#narrowGroupHidden_' + id).hide();
						span.removeClass('expanded');
						span.addClass('collapsed');
					}
				} else if (jQuery(this).attr('href')) {
					var dl = jQuery(this).parents('dl'),
						id = dl.attr('id').replace('narrowGroupHidden_', ''),
						span = jQuery('#facet-' + id).find('dt span');
					dl.hide();
					span.removeClass('expanded');
					span.addClass('collapsed');
				}
			});

			// EBSCO/Search : Less facets
			jQuery('._less_facets').on('click', function (event) {
				var id = jQuery(this).attr('id').replace('less-facets-','');
				var dl = jQuery('#facet-' + id);
				dl.trigger('click');
			});


			// Advanced Search : Add a new search term
			jQuery('._add_row').on('click', function (event) {
				event.preventDefault();
				var newSearch = jQuery('#advanced-row-template').html();
				var rows = jQuery('._advanced-row');
				if (rows) {
					// Find the index of the next row
					var index = rows.length - 1; // one row is the template itself, so don't count it
					// Replace NN string with the index number
					newSearch = newSearch.replace(/NN/g, index);
					lastSearch = jQuery('#edit-add-row');
					lastSearch.before(newSearch);
				}
			});

			// Advanced Search : Delete an advanced search row
			jQuery('._delete_row').on('click', function (event) {
				event.preventDefault();
				jQuery(this).parents('._advanced-row').remove();
			});

			// Advanced Search : Reset the form fields to default values
			jQuery('.ebsco-advanced input[name="reset"]').on('click', function (event) {
				event.preventDefault();
				jQuery('#ebsco-advanced-search-form').find('input, select').each(function (index) {
					var type = this.type;
					switch(type) {
						case 'text':
							jQuery(this).val('');
							break;
						case 'checkbox':
							jQuery(this).attr('checked', '');
							break;
						case 'select-multiple':
							jQuery(this).children('option').each(function (index) {
								jQuery(this).attr('selected', '');
							});
							break;
						case 'select-one':
							jQuery(this).children('option').each(function (index) {
								jQuery(this).attr('selected', '');
							});
							// for IE
							jQuery(this).children('option:first').attr('selected', 'selected');
							break;
						case 'radio':
							jQuery(this).attr('checked', '');
							jQuery(this).parent().siblings().first().children('input:first').attr('checked', 'checked');
							break;
					}
				});
			});

			// Auto submit the seelct boxes with '_jump_menu' class
			jQuery('._jump_menu').on('change', function (event) {
				var name = jQuery(this).attr('id').replace('ebsco-', '');
				var value = jQuery(this).val();
				var url = window.location.href;
				url += "&" + name + "=" + value;
				window.location.href = url;
			});

			// Retain search filters checkbox functionality
			jQuery('#edit-remember').on('click', function (event) {
				jQuery("#ebsco-basic-search-form :input[type='checkbox'][name^='filter[']").attr('checked', jQuery(this).attr('checked'));
			});

			// Advanced Search : handle 'Date Published from' limiter
			// Create the UI slider (if slider function is defined)
			if(typeof jQuery("#ebsco-advanced-search-sliderDT1").slider == 'function') {

				jQuery('#ebsco-advanced-search-sliderDT1').slider({
					range: true,
					min: 0, max: 9999, values: [0, 9999],
					slide: function (event, ui) {
						jQuery('#DT1').val(ui.values[0]);
						if(ui.values[0] == 1000) {
							jQuery('#ebsco-advanced-search-limiterDT1').val('');
						} else {
							jQuery('#ebsco-advanced-search-limiterDT1').val('addlimiter(DT1:' + ui.values[0] + '-1/2013-1)');
						}
					}
				});

				// initialize the slider with the original values
				// in the text boxes
				updatePublishDateSlider();

				// when user enters values into the boxes
				// the slider needs to be updated too
				jQuery('#DT1').change(function(){
					updatePublishDateSlider();
				});
			}


	});

	
}
catch(e)
{
	alert(e);
}





