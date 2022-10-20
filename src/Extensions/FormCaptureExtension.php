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
	public function captureForm(string $formDescription, array $excludedFields = [], array $inDetails = []): array
    {
		// Create a blank form submission
		$submission = CapturedFormSubmission::create(['Type' => $formDescription]);

        // Push default exclusions
		$excludedFields = array_merge($excludedFields, $this->config()->get('default_excluded_fields'));

        /** @var Form $form */
        $form = $this->getOwner();
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
        $val = CapturedField::create();

        $field->performReadonlyTransformation();
        $val->Name = $field->getName();
        $val->Title = $field->Title() ?: $field->getName;
        $val->IsInDetails = $showInDetails;

        // todo - make this logic extensible
        switch (true) {
            case $field instanceof CheckboxField:
                $val->Value = $field->dataValue() === 1 ? 'Yes' : 'No';
                break;
            case $field instanceof GroupedDropdownField:
                $selected = $field->dataValue();

                // Loop through all source keys, if we find an array search it for the field value
                $parentGroupTitle = null;
                foreach ($field->getSource() as $groupTitle => $groupOptions) {
                    if (is_array($groupOptions) && array_search($selected, $groupOptions)) {
                        $parentGroupTitle = $groupTitle;
                    }
                }

                $val->Value = $parentGroupTitle ? "[{$parentGroupTitle}] {$selected}" : $selected;
                break;
            case $field instanceof CheckboxSetField:
                $val->Value = implode(', ', $field->getValueArray());
                break;
            default:
                $val->Value = $field->dataValue();
                break;
        }

        return $val;
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
