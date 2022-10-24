<?php

namespace Bigfork\SilverstripeFormCapture\Tasks;

use Bigfork\SilverstripeFormCapture\Model\CapturedFormSubmission;
use LogicException;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\FieldType\DBDatetime;

class ClearOldSubmissionsTask extends BuildTask
{
    private static $segment = 'ClearOldSubmissionsTask';

    protected $title = 'Clear Old Form Submissions Task';

    protected $description = 'Deletes form submissions that are older than a pre-configured age';

    /**
     * @var int
     * @config
     */
    private static $max_age_days;

    public function run($request)
    {
        $days = $this->config()->get('max_age_days');
        if (!$days) {
            throw new LogicException('No max_age_days setting configured');
        }

        $ago = DBDatetime::now()->modify("-{$days} days");
        $toRemove = CapturedFormSubmission::get()->filter(['Created:LessThan' => $ago->getValue()]);

        $removed = 0;
        /** @var CapturedFormSubmission $submission */
        foreach ($toRemove as $submission) {
            $submission->delete();
            $removed++;
        }

        echo "\n{$removed} submission(s) deleted\n";
    }
}
