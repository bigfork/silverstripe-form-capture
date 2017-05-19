<?php

class CapturedField extends DataObject
{
	private static $singular_name = 'CapturedField';

	private static $plural_name = 'Captured Fields';

	private static $summary_fields = ['Title', 'Value'];

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

	private static $db =
	[
		'Name' => 'Text',
		'Title' => 'Text',
		'Value' => 'Text'
	];

	private static $has_one =
	[
		'Submission' => 'CapturedFormSubmission'
	];
}
