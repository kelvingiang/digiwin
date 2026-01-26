<?php

/* ==============================================================
  THEM CHUC NANG UPLOAD FILE CHO FORM META
  =============================================================== */
add_action('post_edit_form_tag', 'post_edit_form_tag');

function post_edit_form_tag()
{
  echo ' enctype="multipart/form-data"';
}

// phần không tạo ra nhiều kích thước file ảnh upload  khác nhau
function remove_default_image_sizes( $sizes) {
  unset( $sizes['large']);
  unset( $sizes['thumbnail']);
  unset( $sizes['medium']);
  unset( $sizes['medium_large']);
  unset( $sizes['1536x1536']);
  unset( $sizes['2048x2048']);
  return $sizes;
}
add_filter('intermediate_image_sizes_advanced', 'remove_default_image_sizes');



