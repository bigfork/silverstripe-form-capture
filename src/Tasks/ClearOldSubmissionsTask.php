<?php

declare(strict_types=1);

namespace Bigfork\SilverstripeFormCapture\Tasks;

use Bigfork\SilverstripeFormCapture\Model\CapturedFormSubmission;
use LogicException;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class ClearOldSubmissionsTask extends BuildTask
{
    protected static string $commandName = 'ClearOldSubmissionsTask';

    protected string $title = 'Clear Old Form Submissions Task';

    protected static string $description = 'Deletes form submissions that are older than a pre-configured age';

    /**
     * @config
     */
    private static ?int $max_age_days = null;

    protected function execute(InputInterface $input, PolyOutput $output): int
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

        $output->writeln("{$removed} submission(s) deleted");

        return Command::SUCCESS;
    }
}
