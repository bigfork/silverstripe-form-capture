<?php

class FormCaptureAdmin extends ModelAdmin
{
	private static $menu_title = 'Form Submissions';

	private static $url_segment = 'captured-form-submissions';

	private static $managed_models = ['CapturedFormSubmission'];

	private static $menu_icon ='silverstripe-form-capture/icon/captured-form-submissions.png';
}
