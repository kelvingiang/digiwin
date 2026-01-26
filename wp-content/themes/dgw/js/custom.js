function handle(delta) {
    var time = 1000;
    var distance = 300;

    jQuery("html, body")
        .stop()
        .animate({
                scrollTop: jQuery(window).scrollTop() - distance * delta,
            },
            time
        );
}

function FunctionDelete($mess, $page, $action, $id) {
    if (confirm($mess)) {
        location.href =
            "admin.php?page=" + $page + "&action=" + $action + "&id=" + $id;
    } else {
        window.stop();
    }
}

function formatNumber(val) {
    //  val = val.toString().replace(',', '');
    while (/(\d+)(\d{3})/.test(val.toString())) {
        val = val.toString().replace(/(\d+)(\d{3})/, "$1" + "," + "$2");
    }
    //  console.log(val);
    return val;
}

jQuery(document).ready(function() {
    jQuery("h1.entry-title").css("display", "none");

    //  jQuery(".selectmenu").selectmenu({});


    jQuery(".email").focusout(function(e) {
        var email = document.getElementById("txt_email");
        var filter =
            /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        if (!filter.test(email.value)) {
            jQuery("#error-email").text("請輸入正確 E-mail 地址 ! ");
            email.focus;
        } else {
            jQuery("#error-email").text("");
        }
    });

    jQuery(".type-phone-more").keypress(function(event) {
        return isPhone(event, this);
    });

    function isPhone(evt, element) {
        var charCode = evt.which ? evt.which : event.keyCode;
        if (
            //(charCode != 45 || jQuery(element).val().indexOf('-') != -1) && // “-” CHECK MINUS, AND ONLY ONE.
            charCode != 45 && // “-” CHECK MINUS, AND MORE.
            (charCode != 46 || jQuery(element).val().indexOf(".") != -1) && // “.” CHECK DOT, AND ONLY ONE.
            charCode != 8 && // “.” CHECK DOT, AND ONLY ONE.
            (charCode < 48 || charCode > 57)
        )
            return false;
        return true;
    }

    // gioi han ly tu nhap vao   THE SCRIPT THAT CHECKS IF THE KEY PRESSED IS A NUMERIC OR DECIMAL VALUE.
    jQuery(".type-phone").keypress(function(event) {
        return isPhone(event, this);
    });

    function isPhone(evt, element) {
        var charCode = evt.which ? evt.which : event.keyCode;
        if (
            (charCode != 45 || jQuery(element).val().indexOf("-") != -1) && // “-” CHECK MINUS, AND ONLY ONE.
            (charCode != 46 || jQuery(element).val().indexOf(".") != -1) && // “.” CHECK DOT, AND ONLY ONE.
            charCode != 8 && // “.” CHECK DOT, AND ONLY ONE.
            (charCode < 48 || charCode > 57)
        )
            return false;
        return true;
    }

    jQuery(".type-money").keypress(function(event) {
        return isMoney(event, this);
    });

    function isMoney(evt, element) {
        var charCode = evt.which ? evt.which : event.keyCode;
        console.log(charCode);
        if (
            charCode !== 44 && // “.” CHECK DOT, AND ONLY ONE.
            (charCode !== 46 || jQuery(element).val().indexOf(".") !== -1) && // “.” CHECK DOT, AND ONLY ONE.
            charCode !== 8 && // “.” CHECK DOT, AND ONLY ONE.
            (charCode < 48 || charCode > 57)
        )
            return false;
        return true;
    }

    jQuery(".type-number").keypress(function(event) {
        return isOnlyNumber(event, this);
    });

    function isOnlyNumber(evt) {
        var charCode = evt.which ? evt.which : event.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) return false;
        return true;
    }

    // waiting function
    function showwaiting() {
        jQuery("#waiting-img").css("display", "block");
    }

    function hidewaiting() {
        jQuery("#waiting-img").css("display", "none");
    }

 
    function fnpopup() {
        //   var error = '<?php echo $e_error ?>';
        //   var post ='<?php echo $e_branch ?>';
        //   if(error==='' && post !==''){
        jQuery("#div-popup").fadeIn("slow");
        jQuery("#div-alertInfo").css("top", "150px");
        setTimeout(closePopup, 5000);
        // }
    }

    function closePopup() {
        jQuery("#div-popup").fadeOut("slow");
        jQuery("#div-alertInfo").css("top", "0px");
        jQuery("#div-alertInfo").css("opacity", "0");
        //  window.location.reload();
        //  window.location='<?php echo home_url('events') ?>';
    }

    function fnOpenNormalDialog() {
        jQuery("#dialog-confirm").html("Confirm Dialog Box");

        // Define the Dialog and its properties.
        jQuery("#dialog-confirm").dialog({
            resizable: false,
            modal: true,
            title: "Modal",
            height: 250,
            width: 400,
            buttons: {
                Yes: function() {
                    jQuery(this).dialog("close");
                    callback(true);
                },
                No: function() {
                    jQuery(this).dialog("close");
                    callback(false);
                },
            },
        });
    }

    //<![CDATA[

    // Set cookie
    function setCookie(name, value, expires, path, domain, secure) {
        document.cookie =
            name +
            "=" +
            escape(value) +
            (expires == null ? "" : "; expires=" + expires.toGMTString()) +
            (path == null ? "" : "; path=" + path) +
            (domain == null ? "" : "; domain=" + domain) +
            (secure == null ? "" : "; secure");
    }

    // Read cookie
    function getCookie(name) {
        var cname = name + "=";
        var dc = document.cookie;
        if (dc.length > 0) {
            begin = dc.indexOf(cname);
            if (begin != -1) {
                begin += cname.length;
                end = dc.indexOf(";", begin);
                if (end == -1) end = dc.length;
                return unescape(dc.substring(begin, end));
            }
        }
        return null;
    }

    //delete cookie
    function eraseCookie(name, path, domain) {
        if (getCookie(name)) {
            document.cookie =
                name +
                "=" +
                (path == null ? "" : "; path=" + path) +
                (domain == null ? "" : "; domain=" + domain) +
                "; expires=Thu, 01-Jan-70 00:00:01 GMT";
        }
    }
});