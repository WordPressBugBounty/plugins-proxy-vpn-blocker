jQuery(document).ready(function($) {
    var range_sliderrange = document.getElementById("sliderrange");
    var range_slidervalue = document.getElementById("sliderrangeoutput");
    if( typeof range_sliderrange !== 'undefined' && range_sliderrange !== null) {
        range_slidervalue.innerHTML = range_sliderrange.value; // Display the default range-slider__range value
        $('#sliderrangeoutput').append('d')

        range_sliderrange.oninput = function() {
            range_slidervalue.innerHTML = this.value;
            $('#sliderrangeoutput').append('d')
        }
    }
    
    var range_sliderrange2 = document.getElementById("sliderrange2");
    var range_slidervalue2 = document.getElementById("sliderrangeoutput2");
    if( typeof range_sliderrange2 !== 'undefined' && range_sliderrange2 !== null) {
        range_slidervalue2.innerHTML = range_sliderrange2.value; // Display the default range-slider__range value
        $('#sliderrangeoutput2').append('>')

        range_sliderrange2.oninput = function() {
            range_slidervalue2.innerHTML = this.value;
            $('#sliderrangeoutput2').append('>')
        }
    }
    
    var range_sliderrange3 = document.getElementById("sliderrange3");
    var range_slidervalue3 = document.getElementById("sliderrangeoutput3");
    if( typeof range_sliderrange3 !== 'undefined' && range_sliderrange3 !== null) {
        range_slidervalue3.innerHTML = range_sliderrange3.value; // Display the default range-slider__range value
        $('#sliderrangeoutput3').append('>')

        range_sliderrange3.oninput = function() {
            range_slidervalue3.innerHTML = this.value;
            $('#sliderrangeoutput3').append('>')
        }
    }

    var range_sliderrange4 = document.getElementById("sliderrange4");
    var range_slidervalue4 = document.getElementById("sliderrangeoutput4");
    if( typeof range_sliderrange4 !== 'undefined' && range_sliderrange4 !== null) {
        range_slidervalue4.innerHTML = range_sliderrange4.value; // Display the default range-slider__range value
        $('#sliderrangeoutput4').append('m')

        range_sliderrange4.oninput = function() {
            range_slidervalue4.innerHTML = this.value;
            $('#sliderrangeoutput4').append('m')
        }
    }

    var range_sliderrange5 = document.getElementById("sliderrange5");
    var range_slidervalue5 = document.getElementById("sliderrangeoutput5");
    if( typeof range_sliderrange5 !== 'undefined' && range_sliderrange5 !== null) {
        range_slidervalue5.innerHTML = range_sliderrange5.value; // Display the default range-slider__range value
        $('#sliderrangeoutput5').append('>')

        range_sliderrange4.oninput = function() {
            range_slidervalue4.innerHTML = this.value;
            $('#sliderrangeoutput5').append('>')
        }
    }

    var range_sliderrange6 = document.getElementById("sliderrange6");
    var range_slidervalue6 = document.getElementById("sliderrangeoutput6");
    if( typeof range_sliderrange6 !== 'undefined' && range_sliderrange6 !== null) {
        range_slidervalue6.innerHTML = range_sliderrange6.value; // Display the default range-slider__range value
        $('#sliderrangeoutput6').append('>')

        range_sliderrange6.oninput = function() {
            range_slidervalue6.innerHTML = this.value;
            $('#sliderrangeoutput6').append('>')
        }
    }

    if (typeof Cookies.get('pvb-hide-donation-div') !== 'undefinied') {
        $("#pvbdonationhide").show();
    }

    $("#pvbdonationclosebutton").on('click', function() {
        $("#pvbdonationhide").remove();
        Cookies.set('pvb-hide-donate-div', true, { expires: 365 });
    });

    if (typeof Cookies.get('pvb-hide-info-div') !== 'undefinied') {
        $(".pvbinfowrap").show();
    }

    $("#pvbinfoclosebutton").on('click', function() {
        $(".pvbinfowrap").remove();
        Cookies.set('pvb-hide-info-div', true, { expires: 365 });
    });

    setTimeout(function() {
        $("#pvbshow").hide()
    }, 2000);
    
    // JS for nav tabs on options page.
    if(localStorage.getItem("PVBSettingsSelectedTab")===null) {
        $('.nav-tab-wrapper li:first-child').addClass('active');
        $('.pvboptionswrap:first').addClass('active');
    }

    $('ul.nav-tab-wrapper li').on('click', function(){
        var tab_id = $(this).attr('data-tab');

        $('ul.nav-tab-wrapper li').removeClass('active');
        $('.pvboptionswrap').removeClass('active');

        $(this).addClass('active');
        $("#"+tab_id).addClass('active');
        localStorage.PVBSettingsSelectedTab = tab_id;
    });

	if (localStorage.PVBSettingsSelectedTab) {
    	var tb = localStorage.PVBSettingsSelectedTab;
        var tab = "li.pvbsettingstabs[data-tab='" + tb + "']";
        $('li.active').removeClass('active');
        var tab_id = localStorage.PVBSettingsSelectedTab;
        $("#"+tab_id).addClass('active');
        $(tab).addClass('active');
    }

    $(".js-select2pvb-header-custom").select2pvb({
        tags: true,
        width: 'resolve'
      });


      function matchStart(params, data) {
        // If there are no search terms, return all of the data
        if ($.trim(params.term) === '') {
          return data;
        }
    
        // Do not display the item if there is no 'text' property
        if (typeof data.text === 'undefined') {
          return null;
        }
    
        // `params.term` should be the term that is used for searching
        // `data.text` is the text that is displayed for the data object
        if (data.text.indexOf(params.term) > -1) {
          var modifiedData = $.extend({}, data, true);
          modifiedData.text += ' (matched)';
    
          // You can return modified objects from here
          // This includes matching the `children` how you want in nested data sets
          return modifiedData;
        }
    
        // Return `null` if the term should not be displayed
        return null;
    }

    $(".js-select2pvb-list").select2pvb({
        matcher: matchStart,
        width: 'resolve'
      });

    $(".js-select2pvb-placeholder-default").select2pvb({
        allowClear: true,
        width: 'resolve'
    });

    $(".js-select2pvb-tags").select2pvb({
        tags: true,
        width: 'resolve',
        tokenSeparators: [',', ' ']
    });


    if (typeof Cookies.get('pvb-hide-rvw-div') !== 'undefinied') {
        $(".pvbrvwwrap").show();
    }

    $(".pvbdonatedismiss").on('click', function(){
        $(".pvbrvwwrap").remove();
        Cookies.set('pvb-hide-rvw-div', true, { expires: 365 });
    });

});
