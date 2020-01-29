<?php

namespace SSFormCapture\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Security\Permission;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Forms\HeaderField;
use SSFormCapture\Model\CapturedField;

class CapturedFormSubmission extends DataObject implements PermissionProvider
{

	private static $table_name = 'FormCapture_FormSubmission';

	private static $singular_name = 'Form Submission';

	private static $plural_name = 'Form Submissions';

	private static $summary_fields = ['Type', 'Created.Nice', 'Details'];

	private static $searchable_fields = ['Type'];

	private static $field_labels = ['Created.Nice' => 'Submitted on'];

	private static $default_sort = 'Created DESC';

	private static $db =
	[
		'Type' => 'Text'
	];

	private static $has_many =
	[
		'CapturedFields' => CapturedField::class
	];

	/**
	 * @return array
	 */
	public function providePermissions()
	{

		return [
			'VIEW_FORM_SUBMISSIONS' => 'View Submissions',
			'DELETE_FORM_SUBMISSIONS' => 'Delete Submissions'
		];
	}

	public function canView($member = null) {
		return Permission::check('VIEW_FORM_SUBMISSIONS');
	}

	public function canDelete($member= null) {
		return Permission::check('DELETE_FORM_SUBMISSIONS');
	}

	public function canEdit($member = null) {
		return Permission::check('VIEW_FORM_SUBMISSIONS');
	}

	public function canCreate($member = null, $context = []) {
		return false;
	}

	/**
	 * CMS Fields
	 * @return FieldList
	 */
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();

		$fields->removeByName(['CapturedFields', 'Type']);

		$fields->addFieldsToTab("Root.Main", [
            HeaderField::create('SubmissionName', $this->Type),
            HeaderField::create('SubmissionDate', $this->dbObject('Created')->format('dd/MM/yyyy hh:mm'), 3)
        ]);

		$submittedFields = GridField::create('CapturedFields', 'Form Fields', $this->CapturedFields()->sort('Created', 'ASC'));

		$conf = GridFieldConfig::create();
		$conf->addComponent(new GridFieldDataColumns());
        $conf->addComponent(new GridFieldExportButton());
        $conf->addComponent(new GridFieldPrintButton());

		$submittedFields->setConfig($conf);

		$fields->addFieldToTab("Root.Main", $submittedFields);

		$fields->fieldByName('Root.Main')->setTitle($this->Type);

		return $fields;
	}

	public function Details() {
		$html = DBHTMLText::create();
		$toAdd = [];

		// Loop through all fields marked for inclusion in the details tab
		foreach($this->CapturedFields()->filter(['IsInDetails' => '1']) as $field) {

			if(!$field->Value) continue;

			$htmlEnt = '<strong>'. $field->Title .'</strong>: '. $field->Value;
			array_push($toAdd, $htmlEnt);

		}

		$html->setValue(join('<br />', $toAdd));

        return $html;
    }

    /**
     * Ensure that all linked fields are deleted
     * so we don't leave any stale data behind
     */
     public function onBeforeDelete()
    {

        if($this->CapturedFields()) {
            foreach($this->CapturedFields() as $field) {
                $field->delete();
            }
        }

        parent::onBeforeDelete();
    }

    /**
     * @param string $fieldName
     * @return mixed
     */
    public function relField($fieldName)
    {
        // If we're exporting, the field will be prefixed with export__ - works around issues with getTitle()
        if (strpos($fieldName, 'export__') === 0) {
            // Check for a submitted form field with the given name
            $fieldName = substr($fieldName, 8);
            $formField = CapturedField::get()->filter([
                'SubmissionID' => $this->ID,
                'Name' => $fieldName
            ])->first();

            if (!$formField) {
                return null;
            }

            return $formField->dbObject('Value');
        }

        // Default case for fields on this model
        return parent::relField($fieldName);
    }
}
