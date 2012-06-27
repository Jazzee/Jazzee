<?php

/**
 * applicants_single pdf view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * Create a blank canvas to draw the applicant on
 */
header("Content-type: application/pdf");
header('Content-Disposition: attachment; filename=' . $filename);
print $blob;
exit();