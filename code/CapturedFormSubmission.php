<?php

class CapturedFormSubmission extends DataObject
{
	private static $singular_name = 'Form Submission';

	private static $plural_name = 'Form Submissions';

	private static $summary_fields = ['Type', 'Created.Nice'];

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
}
