<?php
$upload_dir = wp_upload_dir();
delete_directory(trailingslashit($upload_dir['basedir']) . 'wc_sa_uploads');
function delete_directory($dirname)
{
    $dir_handle = false;
    if (is_dir($dirname))
        $dir_handle = opendir($dirname);
    if (!$dir_handle)
        return false;
    while ($file = readdir($dir_handle)) {
        if ($file != "." && $file != "..") {
            if (!is_dir($dirname . "/" . $file))
                unlink($dirname . "/" . $file);
            else
                delete_directory($dirname . '/' . $file);
        }
    }
    closedir($dir_handle);
    rmdir($dirname);
    return true;
}
