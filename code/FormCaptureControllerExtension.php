<?php

class FormCaptureControllerExtension extends Extension
{
	/**
	 * Event handler called before initialisation.
	 */
	public function onBeforeInit()
	{
		Requirements::css(CAPTURED_FORM_SUBMISSIONS_DIR . '/css/tweak.css');
	}
}
