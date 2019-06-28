(function ($) {
  $(document).ready(function() {

    // Custom input
    $("input.other")
      .keyup(function(){  //Only number allowed
      parent_form = $(this).closest("FORM");
      $(this).val($(this).val().replace(/[^0-9]/g,""));
      if($(this).val() != ''){
        $('.donation-amount',parent_form).filter(':checked').removeAttr('checked');
        //Refresh amount value
        $('input[type=hidden].amount-holder',parent_form).val($(this).val());
        $('input[type=hidden].donation-amount',parent_form).val($(this).val());
      }
    })
      .on('click focus', function(){  //Checked radio handling
      $(this).parent().prev('input:radio').prop("checked",true);
    });

    //Checkbox checking
    $('.donation-form input[type="radio"]').click(function() {
      parent_form = $(this).closest("FORM");
      $(this).prop("checked",true);

      //Refresh amount value
      if ($(this).hasClass('pre-defined')) {
        $('input[type="text"]',parent_form).val('');
      }

      if($('input:radio:checked', parent_form).length > 0){
        $('input[type=hidden].donation-amount',parent_form).val($(this).val());
        $('input[type=hidden].amount-holder',parent_form).val($(this).val());
      }
      else{
        $('input[type=hidden].amount-holder',parent_form).val('');
        $('input[type=hidden].amount-holder',parent_form).val('');
      }
    });

    //User click Donate now
    $(".donation-submit-button").click(function(e) {
      var post_form = false;
      //If pre-defined radio is checked set true
      if ($('input.pre-defined:radio:checked', $(this).parent()).length) {
        post_form = true;
      }

      //If Other radio is checked & has value set true
      if ($('input:radio:checked + label input:text', $(this).parent()).val()) {
        post_form = true;
      }

      if (post_form){
        
        // Modify form elements based on if recurring or not
        if($('#recurring_yes').prop("checked")) {
          $("[recurring='no']").remove();
        }
        else {
          $("[recurring='yes']").remove();
        }
        
        // Submit form
        $(this).parent().submit();  
      } 
      else {
        e.preventDefault();
        alert(Drupal.t('Please enter your donation amount'));
      }

    });


  })
})(jQuery);