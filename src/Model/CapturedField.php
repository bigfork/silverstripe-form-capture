<?php

namespace Bigfork\SilverstripeFormCapture\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;

class CapturedField extends DataObject
{
	private static $table_name = 'FormCapture_CapturedField';

    private static $db = [
        'Name' => 'Text',
        'Title' => 'Text',
        'Value' => 'Text',
        'IsInDetails' => 'Boolean'
    ];

    private static $has_one = [
        'Submission' => CapturedFormSubmission::class
    ];

	private static $singular_name = 'CapturedField';

	private static $plural_name = 'Captured Fields';

	private static $summary_fields = [
        'Title',
        'Value'
    ];

	public function canView($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

		return Permission::check('VIEW_FORM_SUBMISSIONS');
	}

	public function canDelete($member= null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

		return Permission::check('DELETE_FORM_SUBMISSIONS');
	}

	public function canEdit($member = null)
    {
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

		return Permission::check('VIEW_FORM_SUBMISSIONS');
	}

	public function canCreate($member = null, $context = [])
    {
        $extended = $this->extendedCan(__FUNCTION__, $member, $context = []);
        if ($extended !== null) {
            return $extended;
        }

		return false;
	}
}
