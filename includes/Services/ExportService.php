<?php
namespace Spectrum\Evidence\Services;

if (!defined('ABSPATH')) exit;

final class ExportService {

  public static function outputCsv($filename, $headers, $rows) {
    if (ob_get_length()) ob_end_clean();
    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . sanitize_file_name($filename) . '"');

    $out = fopen('php://output', 'w');
    if (!$out) wp_die('Gagal membuat file CSV.');
    fputcsv($out, array_values((array)$headers));
    foreach ((array)$rows as $row) {
      fputcsv($out, array_values((array)$row));
    }
    fclose($out);
    exit;
  }

  public static function outputXlsx($filename, $headers, $rows) {
    if (ob_get_length()) {
      ob_end_clean();
    }

    if (!class_exists('ZipArchive')) {
      wp_die('Ekspor XLSX membutuhkan ekstensi ZipArchive pada server.');
    }

    $headers = array_values((array)$headers);
    $rows = array_values((array)$rows);

    $sheet_rows = array();
    $sheet_rows[] = $headers;
    foreach ($rows as $row) {
      $sheet_rows[] = array_values((array)$row);
    }

    $shared = array();
    $shared_index = array();
    $sheet_xml_rows = array();

    foreach ($sheet_rows as $r_idx => $cols) {
      $cells_xml = '';
      foreach ($cols as $c_idx => $value) {
        $val = (string)$value;
        if (!isset($shared_index[$val])) {
          $shared_index[$val] = count($shared);
          $shared[] = $val;
        }
        $col = self::columnName($c_idx + 1);
        $row_num = $r_idx + 1;
        $cells_xml .= '<c r="' . $col . $row_num . '" t="s"><v>' . $shared_index[$val] . '</v></c>';
      }
      $sheet_xml_rows[] = '<row r="' . ($r_idx + 1) . '">' . $cells_xml . '</row>';
    }

    $sheet_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
      . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
      . '<sheetData>' . implode('', $sheet_xml_rows) . '</sheetData>'
      . '</worksheet>';

    $shared_si = '';
    foreach ($shared as $txt) {
      $shared_si .= '<si><t>' . self::xmlEscape($txt) . '</t></si>';
    }
    $shared_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
      . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($shared) . '" uniqueCount="' . count($shared) . '">'
      . $shared_si
      . '</sst>';

    $workbook_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
      . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
      . '<sheets><sheet name="Data" sheetId="1" r:id="rId1"/></sheets>'
      . '</workbook>';

    $rels_root = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
      . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
      . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
      . '</Relationships>';

    $rels_wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
      . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
      . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
      . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
      . '</Relationships>';

    $content_types = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
      . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
      . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
      . '<Default Extension="xml" ContentType="application/xml"/>'
      . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
      . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
      . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
      . '</Types>';

    $tmp = wp_tempnam($filename);
    if (!$tmp) {
      wp_die('Gagal membuat file sementara untuk export.');
    }

    $zip = new \ZipArchive();
    if ($zip->open($tmp, \ZipArchive::OVERWRITE) !== true) {
      wp_die('Gagal membuat file XLSX.');
    }
    $zip->addFromString('[Content_Types].xml', $content_types);
    $zip->addFromString('_rels/.rels', $rels_root);
    $zip->addFromString('xl/workbook.xml', $workbook_xml);
    $zip->addFromString('xl/_rels/workbook.xml.rels', $rels_wb);
    $zip->addFromString('xl/worksheets/sheet1.xml', $sheet_xml);
    $zip->addFromString('xl/sharedStrings.xml', $shared_xml);
    $zip->close();

    nocache_headers();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . sanitize_file_name($filename) . '"');
    header('Content-Length: ' . filesize($tmp));

    readfile($tmp);
    @unlink($tmp);
    exit;
  }

  private static function xmlEscape($txt) {
    return htmlspecialchars((string)$txt, ENT_XML1 | ENT_QUOTES, 'UTF-8');
  }

  private static function columnName($index) {
    $name = '';
    while ($index > 0) {
      $index--;
      $name = chr(65 + ($index % 26)) . $name;
      $index = (int)floor($index / 26);
    }
    return $name;
  }
}
