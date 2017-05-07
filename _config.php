<?php

/** Include our custom controller extension */
Controller::add_extension('FormCaptureControllerExtension');

define('CAPTURED_FORM_SUBMISSIONS_DIR', basename(dirname(__FILE__)));
