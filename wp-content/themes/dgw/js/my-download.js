var pendingDownload = null;

jQuery(document).ready(function ($) {
  jQuery("#my-load-data").on("click", function (e) {
    e.preventDefault();

    // Lưu lại thông tin trước khi gửi AJAX
    pendingDownload = {
      post_id: jQuery(this).attr("data-post-id"),
      post_title: jQuery(this).attr("data-post-title"),
      // post_source: jQuery(this).attr("data-post-source"),
    };

    // Gửi AJAX lên server
    jQuery.ajax({
      url: MyAjax.ajax_url,
      type: "POST",
      data: {
        action: "my_download_file", // phải khớp với PHP
        nonce: MyAjax.nonce,
        post_id: pendingDownload.post_id,
        post_title: pendingDownload.post_title,
        //post_source: pendingDownload.post_source,
      },

      success: function (res) {
        if (res.success) {
          // cách load file mở trang mới
          //  window.open(res.data.post_source, '_blank');
          var url = res.data.post_source;

          // Tạo iframe ẩn → download mà không rời trang
          var iframe = document.createElement("iframe");
          iframe.style.display = "none";
          iframe.src = url;
          document.body.appendChild(iframe);

          // Xóa iframe sau 5 giây
          setTimeout(function () {
            document.body.removeChild(iframe);
          }, 5000);
        } else {
          if (res.data.code === "not_logged_in") {
            // Chưa đăng nhập → hiện popup
            showLoginPopup();
          } else {
            alert("Không có file để tải!");
          }
        }
      },
      error: function () {
        alert("An error occurred!");
      },
    });
  });

  jQuery(document).on("click", "#btn-forget-password", function (e) {
    e.preventDefault();
    jQuery("#auth-popup-overlay").fadeOut(200);

    // Hiện popup forgot với Flexbox để căn giữa
    jQuery("#popup-forgot-password").css("display", "flex").hide().fadeIn(300);
  });

  // Đóng popup khi click nút X hoặc click ra ngoài vùng trắng
  jQuery(document).on(
    "click",
    ".dwf-close, #popup-forgot-password",
    function (e) {
      if (e.target !== this) return;
      jQuery("#popup-forgot-password").fadeOut(200);
      jQuery("#forgot-password-msg").text("");
    },
  );

  // 2. Đóng popup
  jQuery(document).on("click", ".close-popup, .dw-popup-overlay", function (e) {
    if (e.target !== this) return;
    jQuery("#popup-forgot-password").fadeOut();
  });

  // 3. Xử lý gửi Form bằng AJAX (không load lại trang)
  jQuery(document).on("submit", "#forgot_password_form_ajax", function (e) {
    e.preventDefault();
    var email = jQuery("#user_email").val();
    var nonce = jQuery("#forgot_nonce").val();
    const btn = jQuery("#btn-submit-forgot");
    const currentLang = document.documentElement.lang;
    const sendLang = currentLang === "zh-TW" ? "cn" : "vn";

    const uiText = {
      vn: { loading: "Đang xử lý...", original: "Gửi yêu cầu" },
      cn: { loading: "处理中...", original: "提交" },
    };
    const langSet = uiText[sendLang];
    btn.prop("disabled", true).addClass("is-loading").text(langSet.loading);

    jQuery.ajax({
      url: MyAjax.ajax_url, // dw_params được truyền từ wp_localize_script
      type: "POST",
      data: {
        action: "member_forgot_password",
        user_email: email,
        lang: sendLang,
        nonce: nonce,
      },
      success: function (response) {
        if (response.success) {
          jQuery("#user_email").val("");
          jQuery("#forgot-password-msg")
            .html(response.data.message)
            .css("color", "green");
          btn.text(langSet.original).removeClass("is-loading").prop("disabled", false);
        } else {
          jQuery("#user_email").val("");
          jQuery("#forgot-password-msg")
            .html(response.data.message)
            .css("color", "red");
          btn.text(langSet.original).removeClass("is-loading").prop("disabled", false);
        }
          btn.text(langSet.original).removeClass("is-loading").prop("disabled", false);
        jQuery("#forgot-password-msg").html(response.data.message);
      },
    });
  });
});

// khi nhấn enter sẽ tự submit from
jQuery(document).on("keydown", "#login-email, #login-password", function (e) {
  if (e.key === "Enter") {
    jQuery("#btn-login").click();
  }
});

// ===== ĐĂNG NHẬP ==================================
jQuery(document).on("click", "#btn-login", function () {
  const $this = jQuery(this);
  const currentLang = document.documentElement.lang;
  const sendLang = currentLang === "zh-TW" ? "cn" : "vn";

  // Cấu hình ngôn ngữ hiển thị tại client
  const uiText = {
    vn: { loading: "Đang xử lý...", original: "Đăng Nhập" },
    cn: { loading: "处理中...", original: "登入" },
  };
  const langSet = uiText[sendLang];
  $this.prop("disabled", true).addClass("is-loading").text(langSet.loading);

  jQuery.ajax({
    url: MyAjax.ajax_url,
    type: "POST",
    data: {
      action: "download_member_login",
      nonce: MyAjax.nonce,
      email: jQuery("#login-email").val(),
      password: jQuery("#login-password").val(),
      lang: sendLang,
    },
    success: function (res) {
      if (res.success) {
        console.log("DEBUG:", res.data.debug);
        jQuery("#login-msg").css("color", "green").text(res.data.message);
        setTimeout(function () {
          // ✅ 判斷在哪個頁面
          const page = jQuery("#tab-login").data("page");

          if (page === "member") {
            window.location.href = window.location.href;
            // window.location.href = "/"; // ← member page 轉首頁
            return;
          }
          $this
            .prop("disabled", false)
            .removeClass("is-loading")
            .text(langSet.original);
          jQuery("#auth-popup-overlay").remove();
          if (pendingDownload) {
            jQuery.ajax({
              url: MyAjax.ajax_url,
              type: "POST",
              data: {
                action: "my_download_file",
                nonce: MyAjax.nonce,
                post_id: pendingDownload.post_id,
                post_title: pendingDownload.post_title,
                post_source: pendingDownload.post_source,
              },
              success: function (res) {
                if (res.success) {
                  window.location.href = res.data.post_source;
                }
              },
            });
          }
        }, 1000);
      } else {
        jQuery("#login-msg").css("color", "red").text(res.data.message);
        $this
          .prop("disabled", false)
          .removeClass("is-loading")
          .text(langSet.original);
      }
    },
  });
});

// ===== ĐĂNG KÝ =====
jQuery(document).on("click", "#btn-register", function () {
  const $this = jQuery(this);
  const currentLang = document.documentElement.lang;
  const sendLang = currentLang === "zh-TW" ? "cn" : "vn";

  // Cấu hình ngôn ngữ hiển thị tại client
  const uiText = {
    vn: { loading: "Đang xử lý...", original: "Đăng ký" },
    cn: { loading: "处理中...", original: "注册" },
  };
  const langSet = uiText[sendLang];
  // 1. Vô hiệu hóa button để tránh spam request
  $this.prop("disabled", true).addClass("is-loading").text(langSet.loading);

  jQuery.ajax({
    url: MyAjax.ajax_url,
    type: "POST",
    data: {
      action: "download_member_register",
      nonce: MyAjax.nonce,
      username: jQuery("#reg-username").val(),
      email: jQuery("#reg-email").val(),
      password: jQuery("#reg-password").val(),
      company: jQuery("#reg-company").val(),
      phone: jQuery("#reg-phone").val(),
      tax: jQuery("#reg-tax").val(),
      industry: jQuery("#reg-industry").val(),
      position: jQuery("#reg-position").val(),
      department: jQuery("#reg-department").val(),
      lang: sendLang,
    },
    success: function (res) {
      if (res.success) {
        jQuery("#register-msg").css("color", "green").text(res.data.message);
        jQuery("#reg-username").val("");
        jQuery("#reg-email").val("");
        jQuery("#reg-password").val("");
        jQuery("#reg-company").val("");
        jQuery("#reg-phone").val("");
        jQuery("#reg-tax").val("");
        jQuery("#reg-industry").val("");
        jQuery("#reg-position").val("");
        jQuery("#reg-department").val("");
        setTimeout(function () {
          jQuery('.tab-btn[data-tab="login"]').click();
          jQuery("#register-msg").text("");
          $this
            .prop("disabled", false)
            .removeClass("is-loading")
            .text(langSet.original);
        }, 5000);
      } else {
        jQuery("#register-msg").css("color", "red").text(res.data.message);
        $this
          .prop("disabled", false)
          .removeClass("is-loading")
          .text(langSet.original);
      }
    },
  });
});

// ===== HIỆN POPUP =====
function showLoginPopup() {
  jQuery("#login-email").val("");
  jQuery("#login-password").val("");
  jQuery("#login-msg").text("");

  jQuery("#auth-popup-overlay").addClass("show");
}

// Đóng popup
jQuery(document).on("click", "#auth-popup-close", function () {
  jQuery("#auth-popup-overlay").removeClass("show");
});

jQuery(document).on("click", "#auth-popup-overlay", function (e) {
  if (e.target === this) {
    jQuery("#auth-popup-overlay").removeClass("show");
  }
});

jQuery(document).on("click", ".tab-btn", function () {
  jQuery(".tab-btn").removeClass("active");
  jQuery(this).addClass("active");
  jQuery(".tab-content").hide();
  jQuery("#tab-" + jQuery(this).data("tab")).show();
});
