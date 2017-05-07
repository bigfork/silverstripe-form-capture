<?php

class CapturedField extends DataObject
{
	private static $singular_name = 'CapturedField';

	private static $plural_name = 'Captured Fields';

	private static $summary_fields = ['Name', 'Value'];


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
