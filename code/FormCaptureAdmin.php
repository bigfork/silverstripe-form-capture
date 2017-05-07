<?php

class FormCaptureAdmin extends ModelAdmin
{
	private static $menu_title = 'Form Submissions';

	private static $url_segment = 'captured-form-submissions';

	private static $managed_models = ['CapturedFormSubmission'];

	private static $menu_icon ='formcapture/icon/captured-form-submissions.png';

	/**
	 * @param Int $id
	 * @param FieldList $fields
	 * @return Form
	 */
	public function getEditForm($id = null, $fields = null)
	{
		$form = parent::getEditForm($id, $fields);
		$config = $form->Fields()->dataFieldByName('CapturedFormSubmission')->getConfig();
		$config->removeComponentsByType('GridFieldAddNewButton');
		return $form;
	}
}
