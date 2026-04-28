<?php
namespace Spectrum\Evidence\Services;

if (!defined('ABSPATH')) exit;

final class UploadService {

  public static function maybeUploadAndReplace($file_field, $old_attachment_id = 0) {
    if (empty($_FILES[$file_field]) || empty($_FILES[$file_field]['name'])) {
      return (int)$old_attachment_id; // tidak upload baru => keep
    }

    // WP upload helpers
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // Upload baru
    $new_id = media_handle_upload($file_field, 0);
    if (is_wp_error($new_id)) {
      return $new_id; // caller handle error
    }

    // Hapus yang lama (hard delete) kalau ada
    if ($old_attachment_id) {
      wp_delete_attachment((int)$old_attachment_id, true);
    }

    return (int)$new_id;
  }
}