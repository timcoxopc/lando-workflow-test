jQuery( document ).ready(function() {

  jQuery('#edit-full-name').keyup(function() {
    jQuery('.customerName').html( jQuery(this).val() );
  });

  jQuery('#edit-option-1').click(function(e) {
    if (jQuery('#edit-option-2').prop('checked')) {
      e.preventDefault();
    }
    if (jQuery('#edit-option-3').prop('checked')) {
      e.preventDefault();
    }
  });

  jQuery('#edit-option-2').click(function(e) {
    if (jQuery('#edit-option-1').prop('checked')) {
      e.preventDefault();
    }
    if (jQuery('#edit-option-3').prop('checked')) {
      e.preventDefault();
    }
  });

  jQuery('#edit-option-3').click(function(e) {
    if (jQuery('#edit-option-1').prop('checked')) {
      e.preventDefault();
    }
    if (jQuery('#edit-option-2').prop('checked')) {
      e.preventDefault();
    }
  });

});