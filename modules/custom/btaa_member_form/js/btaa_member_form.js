(function ($, Drupal, window, document, undefined) {

    $('.js-form-item-field-mailed-newsletter-value').appendTo($('#edit-field-mailchimp-subscription-0 .panel-body'));
    $('.js-form-item-field-ibta-enews-value').appendTo($('#edit-field-mailchimp-subscription-0 .panel-body'));
    $('.js-form-item-field-ibta-magazine-value').appendTo($('#edit-field-mailchimp-subscription-0 .panel-body'));
    
    $('#edit-field-mailchimp-subscription-0 .panel-title').html('Regular Publications');
    
    //On Live Site please use correct labels
    var html = $('.form-item-field-mailchimp-subscription-0-subscribe label').html();
    $('.form-item-field-mailchimp-subscription-0-subscribe label').html(html.replace('e-Newsletter','BTAA e-News (monthly)'));
    
    $('<div id="js-form-item-field-mailchimp-subscription-0-subscribe--description" class="description help-block"><p>Electronic only (PDF document - emailed)</p></div>').appendTo($('.form-item-field-mailchimp-subscription-0-subscribe'));
    /*Show/Hide Functions for checkboxes*/
    $('.child-details').addClass("hidden");
    $('.address').addClass("hidden");
    $('.js-form-item-field-other-0-value').addClass("hidden");

    $('#edit-field-paediatric-handbook-value').change(function(){
        if ($(this).is(':checked')){
            $('.child-details').removeClass("hidden").addClass("visible");
            $('.address').removeClass("hidden").addClass("visible");
        }
        else {
            $('.child-details').removeClass("visible").addClass("hidden");
            $('.address').removeClass("visible").addClass("hidden");
        }
    })
    $('#edit-field-mailed-newsletter-value').change(function(){
        if ($(this).is(':checked')){
            $('.address').removeClass("hidden").addClass("visible");
        }
        else {
            $('.address').removeClass("visible").addClass("hidden");
        }
    })
    $('#edit-field-adult-brain-tumour-primer-value').change(function(){
        if ($(this).is(':checked')){
            $('.address').removeClass("hidden").addClass("visible");
        }
        else {
            $('.address').removeClass("visible").addClass("hidden");
        }
    })
    $('#edit-field-ibta-magazine-value').change(function(){
        if ($(this).is(':checked')){
            $('.address').removeClass("hidden").addClass("visible");
        }
        else {
            $('.address').removeClass("visible").addClass("hidden");
        }
    })
    $('#edit-field-requester-category').change(function(){
        if ($(this).val()=='Other') {
            $('.js-form-item-field-other-0-value').removeClass("hidden").addClass("visible");
        }
        else {
            $('.js-form-item-field-other-0-value').removeClass("visible").addClass("hidden");
        }
    })

    $('.form-item-field-user-home-phone-0-value').removeClass("form-inline");
    $('.form-item-field-mobile-phone-0-value').removeClass("form-inline");
    
    

})(jQuery, Drupal, this, this.document);