<?php

/**
 * Based off of Drupal's common.inc version of format_interval().
 */
function format_interval($interval, $granularity = 2, $langcode = NULL) {

  $units = array(
    'year|years' => 31536000,
    'month|months' => 2592000,
    'week|weeks' => 604800,
    'day|days' => 86400,
    'hour|hours' => 3600,
    'min|min' => 60,
    'sec|sec' => 1
  );
  $output = '';
  foreach ($units as $key => $value) {
    $key = explode('|', $key);
    if ($interval >= $value) {
      $num = floor($interval / $value);
      $index = $num != 1;
      $output .= ($output ? ' ' : '') . $num . ' ' . $key[$index];

      $interval %= $value;
      $granularity--;
    }

    if ($granularity == 0) {
      break;
    }
  }

  return $output ? $output : __('0 sec');
}
