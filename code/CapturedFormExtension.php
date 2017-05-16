<?php

class CapturedFormExtension extends Extension
{
	/**
	 * Add a method to Form which will capture data when invoked
	 * @param Form $form The form to be captured
	 * @param string $dataName Am optional name for the submission
	 * @param mixed $excludedFields An array or string of fields to be ignored when saving the submission
	 * @return null
	 */
	public function captureForm(Form $form, $dataName = 'Form Submission', $excludedFields = []) {

		// Create a blank form submission and write to database so that we have an ID to work with
		$submission = CapturedFormSubmission::create();
		$submission->Type = $dataName;
		$submission->write();

		// Grab all the fields
		$fieldsToWrite = $form->fields->dataFields();

		// Allow the excluded fields to be a single string
		$excludedFields = is_array($excludedFields) ? $excludedFields : [$excludedFields];

		// Ignore SecurityID by default
		array_push($excludedFields, 'SecurityID');

		// Remove any unwanted fields from the fields array
		foreach($excludedFields as $excludedField) {
			if($form->fields->dataFieldByName($excludedField)) {
				unset($fieldsToWrite[$excludedField]);
			};
		}

		// For every wanted field create a Captured Field object and write it to this submission
		foreach($fieldsToWrite as $field) {
			$val = CapturedField::create();
			$val->SubmissionID = $submission->ID;

			$field->performReadonlyTransformation();
			$val->Name = $field->Name;
			$val->Title = $field->Title();
			$val->Value = $field->dataValue();
			$val->write();
		}
	}
}
