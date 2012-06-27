<?php

/**
 * Display Dates in a nice form
 */
if ($date) {
  echo date('m/d/Y', strtotime($date));
}