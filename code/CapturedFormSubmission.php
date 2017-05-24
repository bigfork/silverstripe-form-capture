<?php

class CapturedFormSubmission extends DataObject implements PermissionProvider
{
	private static $singular_name = 'Form Submission';

	private static $plural_name = 'Form Submissions';

	private static $summary_fields = ['Type', 'Created.Nice', 'Details'];

	private static $field_labels = ['Created.Nice' => 'Submitted on'];

	private static $default_sort = 'Created DESC';

	private static $db =
	[
		'Type' => 'Text'
	];

	private static $has_many =
	[
		'CapturedFields' => 'CapturedField'
	];

	public function providePermissions() {
		return [
			'VIEW_FORM_SUBMISSIONS' => [
				'name' => 'View Submissions',
				'category' => 'Form Submissions'
			],
			'DELETE_FORM_SUBMISSIONS' => [
				'name' => 'Delete Submissions',
				'category' => 'Form Submissions'
			]
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

	public function canCreate($member = null) {
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

		$fields->addFieldToTab("Root.Main", LiteralField::create('SubmissionName', '<h2>'. $this->Type . '</h2>'));

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
		$html = HTMLText::create();
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
}
