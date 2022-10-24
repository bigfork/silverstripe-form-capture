<?php

namespace Bigfork\SilverstripeFormCapture\Extensions;

use Bigfork\SilverstripeFormCapture\Admin\FormCaptureAdmin;
use Bigfork\SilverstripeFormCapture\Model\CapturedField;
use Bigfork\SilverstripeFormCapture\Model\CapturedFormSubmission;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\GroupedDropdownField;
use SilverStripe\ORM\ValidationException;

class FormCaptureExtension extends Extension
{
    use Configurable;

    private static $default_excluded_fields = [
        'SecurityID',
        'Captcha'
    ];

    /**
     * @param string $formDescription Human-readable description of the type of form being saved
     * @param array $excludedFields Fields to exclude from being stored against this submission
     * @param array $inDetails List of fields to include in the summary "Details" column
     * @return array Tuple containing: ID (submission ID) and Link (link to submission in CMS)
     * @throws ValidationException
     */
	public function captureForm(
        string $formDescription,
        ?string $nameField,
        ?string $emailField,
        array $excludedFields = [],
        array $inDetails = []
    ): array
    {
        /** @var Form $form */
        $form = $this->getOwner();

		// Create a blank form submission
		$submission = CapturedFormSubmission::create(['Type' => $formDescription]);

        if ($nameField && $nameFormField = $form->Fields()->dataFieldByName($nameField)) {
            $submission->Name = $this->extractValueFromFormField($nameFormField);
        }
        if ($emailField && $emailFormField = $form->Fields()->dataFieldByName($emailField)) {
            $submission->Email = $this->extractValueFromFormField($emailFormField);
        }

        // Push default exclusions
		$excludedFields = array_merge($excludedFields, $this->config()->get('default_excluded_fields'));

		// For every field create a CapturedField object and add it to this submission
		foreach ($form->Fields()->dataFields() as $field) {
            // Ignore any excluded fields
            if (in_array($field->getName(), $excludedFields)) {
                continue;
            }

            $showInDetails = in_array($field->getName(), $inDetails);
            $capturedField = $this->createCapturedField($field, $showInDetails);
            $submission->CapturedFields()->add($capturedField);
		}

        $submission->write();

        return [
           'ID' => $submission->ID,
           'Link' => $this->getSubmissionCMSLink($submission->ID)
        ];
	}

    /**
     * @param FormField $field The FormField instance to save to the database
     * @param bool $showInDetails Whether to include the field in the summary "Details" column
     * @return CapturedField
     */
    protected function createCapturedField(FormField $field, bool $showInDetails = false): CapturedField
    {
        $capturedField = CapturedField::create([
            'Name' => $field->getName(),
            'Title' => $field->Title() ?: $field->getName(),
            'IsInDetails' => $showInDetails,
            'Value' => $this->extractValueFromFormField($field)
        ]);

        $this->owner->extend('updateCapturedField', $capturedField);
        return $capturedField;
    }

    protected function extractValueFromFormField(FormField $field)
    {
        switch (true) {
            case $field instanceof CheckboxField:
                return $field->dataValue() === 1 ? 'Yes' : 'No';
            case $field instanceof GroupedDropdownField:
                $selected = $field->dataValue();

                // Loop through all source keys, if we find an array search it for the field value
                $parentGroupTitle = null;
                foreach ($field->getSource() as $groupTitle => $groupOptions) {
                    if (is_array($groupOptions) && array_search($selected, $groupOptions)) {
                        $parentGroupTitle = $groupTitle;
                    }
                }

                return $parentGroupTitle ? "[{$parentGroupTitle}] {$selected}" : $selected;
            case $field instanceof CheckboxSetField:
                return implode(', ', $field->getValueArray());
        }

        return $field->dataValue();
    }

    /**
     * @param int $id Submission ID
     * @return string Link URL
     */
    protected function getSubmissionCMSLink(int $id): string
    {
        /** @var FormCaptureAdmin $admin */
        $admin = Injector::inst()->create(FormCaptureAdmin::class);
        return Controller::join_links(
            Director::absoluteBaseURL(),
            $admin->Link(),
            str_replace('\\', '-', CapturedFormSubmission::class),
            'EditForm/field/Submissions/item',
            $id,
            'edit'
        );
    }
}
