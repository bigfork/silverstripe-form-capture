<?php

namespace Bigfork\SilverstripeFormCapture\Extensions;

use Bigfork\SilverstripeFormCapture\Filters\HavingPartialMatchFilter;
use Bigfork\SilverstripeFormCapture\Model\CapturedField;
use Bigfork\SilverstripeFormCapture\Model\CapturedFormSubmission;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Queries\SQLSelect;

class CapturedFormSubmissionExtension extends Extension
{
    /**
     * For backward-compatibility with submissions which may have not stored Name/Email against the submission
     * record, we include a subquery that looks for a matching CapturedField and use that as the Name/Email
     *
     * @param SQLSelect $query
     * @return void
     */
    public function augmentSQL(SQLSelect $query)
    {
        $formCaptureTable = DataObject::getSchema()->tableName(CapturedFormSubmission::class);
        $capturedFieldTable = DataObject::getSchema()->tableName(CapturedField::class);

        $query->selectField(
            <<<SQL
CASE
    WHEN Name IS NULL THEN (
        SELECT "{$capturedFieldTable}"."Value"
        FROM "{$capturedFieldTable}"
        WHERE "{$capturedFieldTable}"."Name" IN ('Name', 'Surname', 'FullName')
        AND "{$capturedFieldTable}"."SubmissionID" = "{$formCaptureTable}"."ID"
    )
    ELSE Name END
SQL
            ,
            'NameWithFallback'
        );

        $query->selectField(
            <<<SQL
CASE
    WHEN Email IS NULL THEN (
        SELECT "{$capturedFieldTable}"."Value"
        FROM "{$capturedFieldTable}"
        WHERE "{$capturedFieldTable}"."Name" IN ('Email', 'EmailAddress')
        AND "{$capturedFieldTable}"."SubmissionID" = "{$formCaptureTable}"."ID"
    )
    ELSE Email END
SQL
            ,
            'EmailWithFallback'
        );
    }

    /**
     * We have to push our custom searchable fields here instead of using searchable_fields directly, as
     * searchable_fields will call relObject() with the field name which causes errors as the below fields
     * technically don't exist
     *
     * @param array $fields
     * @return void
     */
    public function updateSearchableFields(array &$fields)
    {
        $fields = array_merge(
            [
                'NameWithFallback' => [
                    'title' => 'Name',
                    'field' => TextField::class,
                    'filter' => HavingPartialMatchFilter::class
                ],
                'EmailWithFallback' => [
                    'title' => 'Email',
                    'field' => TextField::class,
                    'filter' => HavingPartialMatchFilter::class
                ],
            ],
            $fields
        );
    }
}
