// var $j = jQuery.noConflict();
jQuery(document).ready(function() {
    jQuery(".dgw-article").css({ "display": "none" });

    ChangSelect(".title-event", ".content-event");

    jQuery(".dgw-title-text").on("click", function() {
        var title = jQuery(this).attr("id-title");
        var content = jQuery(this).attr("id-content");
        ChangSelect(title, content);
    });

    jQuery(".dgw-link").on("click", function() {
        jQuery('.dgw-article').slideUp();
        jQuery(this).siblings('.dgw-article').slideDown('slow');
    });
});




function ChangSelect(titleSelect, contentSelect) {
    jQuery(titleSelect)
        .siblings()
        .removeClass("title-select")
        // .css({ "background-color": "#fff", color: "#333" });

    jQuery(titleSelect)
        .addClass("title-select")
        // .css({ "background-color": "#f0f0f0", color: "#d45113" });

    jQuery(".dgw-content").css("display", "none");
    // jQuery('.dgw-article').hide();

    jQuery('.dgw-content-list .dgw-content-item:first-child .dgw-article').slideDown();

    jQuery(contentSelect).show();
}