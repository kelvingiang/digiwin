<?php

function uploadFile($name, $File)
{

    if (!empty($File['name'])) {

        $errors = array();
        // $file_name = $File['file_upload']['name'];
        $file_size = $File['size'];
        $file_tmp = $File['tmp_name'];
        // $file_type = $File['file_upload']['type'];

        $file_trim = ((explode('.', $File['name'])));
        //  $trim_name = strtolower($file_trim[0]);
        $trim_type = strtolower($file_trim[1]);

        $cus_name = $name . '.' . $trim_type;
        $extensions = array("jpeg", "jpg", "png", "bmp", "webp", "avif");
        if (in_array($trim_type, $extensions) === false) {
            $errors[] = "上傳照片檔案是 JPEG, PNG, BMP.";
        }
        if ($file_size > 2097152) {
            $errors[] = '上傳檔案容量不可大於 2 MB';
        }
        $path = DIR_IMAGES; /* get function path upload img dc khai bao tai file hepler */

        if (empty($errors) == true) {

            if (is_file($path . $name)) {
                unlink($path . $name);
            }
            move_uploaded_file($file_tmp, ($path . $cus_name));
            return $cus_name;
        } else {
            return $errors;
        }
    }
}


function uploadLogo($name, $File)
{
    if (!empty($File['file-logo']['name'])) {
        $errors = array();
        // $file_name = $File['file_upload']['name'];
        $file_size = $File['file-logo']['size'];
        $file_tmp = $File['file-logo']['tmp_name'];
        // $file_type = $File['file_upload']['type'];
        $file_trim = ((explode('.', $File['file-logo']['name'])));
        //  $trim_name = strtolower($file_trim[0]);
        $trim_type = strtolower($file_trim[1]);
        $cus_name = $name . '.' . $trim_type;
        $extensions = array("jpeg", "jpg", "png", "bmp", "webp", "avif");

        if (in_array($trim_type, $extensions) == false) {
            $errors[] = "上傳照片檔案是 JPEG, PNG, BMP, WEBP, AVIF";
        }

        if ($file_size > 2097152) {
            $errors[] = '上傳檔案容量不可大於 2 MB';
        }

        $path = DIR_IMAGES . 'logo' . DS; /* get function path upload img dc khai bao tai file hepler */
        if (empty($errors) == true) {
            if (is_file($path . $name)) {
                unlink($path . $name);
            }
            move_uploaded_file($file_tmp, ($path . $cus_name));
            return $cus_name;
        } else {
            return $errors;
        }
    }
}



// function uploadImg($name, $File)
// {
//     if (!empty($File['name'])) {
//         $errors = [];

//         $file_size = $File['size'];
//         $file_tmp  = $File['tmp_name'];
//         $file_name = $File['name'];

//         // 副檔名
//         $trim_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

//         // 安全檔名
//         $safe_name = preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $file_name);
//         $safe_title = preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $name);
//         $cus_name  = $safe_title . '-' . $safe_name;

//         // 檔案限制
//         $extensions = ["jpeg", "jpg", "png", "bmp"];
//         if (!in_array($trim_type, $extensions)) {
//             $errors[] = "上傳照片檔案僅限JPEG, PNG, BMP.";
//         }
//         if ($file_size > 2097152) {
//             $errors[] = '上傳檔案容量不可大於2MB';
//         }

//         // 路徑
//         $path = DIR_IMAGES . 'pop-up' . DS;
//         if (!is_dir($path)) {
//             mkdir($path, 0777, true);
//         }

//         // MIME 檢查
//         $allowed_types = ['image/jpeg', 'image/png', 'image/bmp'];
//         if (!in_array(mime_content_type($file_tmp), $allowed_types)) {
//             $errors[] = "檔案類型不正確!";
//         }

//         if (empty($errors)) {
//             if (move_uploaded_file($file_tmp, $path . $cus_name)) {
//                 return $cus_name;
//             } else {
//                 $errors[] = "檔案移動失敗";
//             }
//         }
//         return $errors;
//     }
// }
