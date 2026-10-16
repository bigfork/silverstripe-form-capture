<?php

declare(strict_types=1);

namespace Bigfork\SilverstripeFormCapture\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;

class CapturedField extends DataObject
{
	private static string $table_name = 'FormCapture_CapturedField';

    private static array $db = [
        'Name' => 'Text',
        'Title' => 'Text',
        'Value' => 'Text',
        'IsInDetails' => 'Boolean'
    ];

    private static array $has_one = [
        'Submission' => CapturedFormSubmission::class
    ];

	private static string $singular_name = 'CapturedField';

	private static string $plural_name = 'Captured Fields';

	private static array $summary_fields = [
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
