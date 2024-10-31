jQuery(document).ready(function() {
    jQuery(".pvb-users-tooltip-container").on("click", ".pvb-users-tooltip-icon", function(e) {
      e.stopPropagation();
      jQuery(this).siblings(".pvb-users-tooltip-content").toggle();
    });
  
    jQuery(document).on("click", function(e) {
      if (!jQuery(e.target).closest(".pvb-users-tooltip-container").length) {
        jQuery(".pvb-users-tooltip-content").hide();
      }
    });
  });