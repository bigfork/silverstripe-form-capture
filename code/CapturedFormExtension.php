<?php

class CapturedFormExtension extends Extension
{

    /**
    * Add a method to Form which will capture data when invoked
    * @param string $dataName Am optional name for the submission
    * @param mixed $excludedFields An array or string of fields to be ignored when saving the submission
    * @param mixed $inDetails Fields to be included in the 'details' column in the admin area
    * @return null
    */
    public function captureForm($dataName = 'Form Submission', $excludedFields = [], $inDetails = [])
    {

        $form = $this->owner;

        // Create a blank form submission and write to database so that we have an ID to work with
        $submission = CapturedFormSubmission::create();
        $submission->Type = $dataName;
        $submission->write();

        // Grab all the fields
        $fieldsToWrite = $form->fields->dataFields();

        // Allow the excluded fields and details fields to be a single string
        $excludedFields = is_array($excludedFields) ? $excludedFields : [$excludedFields];
        $inDetails = is_array($inDetails) ? $inDetails : [$inDetails];

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

            $showInDetails = in_array($field->Name, $inDetails) ? '1' : '0';

            $capturedField = $this->create_captured_field($field, $showInDetails);

            $capturedField->SubmissionID = $submission->ID;

            $capturedField->write();

        }

        // Return an ID for this submission
        return [
            'ID' => $submission->ID,
            'Link' => $this->get_submission_link($submission->ID)
        ];
    }

    /**
    * Method what returns a captured field constructed from
    * a given value
    *
    * @param FormField $field The field to transform and write to the db
    * @param boolean $showIndetails Controls whether the current field should show in the submission 'Details'
    *
    * @return CapturedField The final field for the submission
    */
    private function create_captured_field($field, $showInDetails = false)
    {
        $val = CapturedField::create();

        $field->performReadonlyTransformation();
        $val->Name = $field->Name;
        $val->Title = $field->Title() ?: $field->Name;
        $val->IsInDetails = $showInDetails;

        // Add to this statement if any future type-based value conversions are required
        switch ($field->Type()) {
            case 'checkbox':

                $val->Value = $field->dataValue() === 1 ? 'Yes' : 'No';

                break;
            case 'groupeddropdown dropdown':

                // Relevent values
                $groupedSrc = $field->getSourceAsArray();
                $selected = $field->dataValue();

                // Loop through all source keys, if we find an array search it for the field value
                foreach ($groupedSrc as $key => $option) {

                    if(is_array($option) && array_search($selected, $option)) {

                        // If there's a match return the key holding the value
                        $catForVal = $key;

                    }
                }

                // Formatted value for CMS Display
                $val->Value = $catForVal ? '[' . $catForVal .'] ' . $selected : $selected;

                break;
            default:

                $val->Value = $field->dataValue();

                break;
        }

        return $val;
    }

    /**
     * Return a link which can be used externally for linking
     * to a specific submission object in the CMS
     *
     * @param int $id The ID of the linked submission
     *
     * @return string
     */
    private function get_submission_link($id)
    {
        $base = Director::AbsoluteBaseURL() . singleton('FormCaptureAdmin')->Link();
        $editorLink = $base . 'CapturedFormSubmission/EditForm/field/CapturedFormSubmission/item/';
        return $editorLink . $id;
    }
}
