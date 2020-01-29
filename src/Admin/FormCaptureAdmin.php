<?php

namespace SSFormCapture\Admin;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldImportButton;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\GridfieldQueuedExport\Forms\GridFieldQueuedExportButton;
use SilverStripe\ORM\ArrayLib;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\Filters\GreaterThanOrEqualFilter;
use SilverStripe\ORM\Filters\LessThanOrEqualFilter;
use SilverStripe\ORM\Queries\SQLSelect;
use SilverStripe\ORM\Search\SearchContext;
use SSFormCapture\Model\CapturedField;
use SSFormCapture\Model\CapturedFormSubmission;

class MyAdmin extends ModelAdmin
{

    private static $managed_models = [CapturedFormSubmission::class];

    private static $url_segment = 'captured-form-submissions';

    private static $menu_title = 'Form Submissions';

    private static $menu_icon ='andrewhaine/silverstripe-form-capture:icon/captured-form-submissions.svg';

    public function getEditForm($id = null, $fields = null)
	{
		$form = parent::getEditForm();

		/** @var GridField $gridField */
		$gridField = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass));
		$gridField->setName('Submissions');

		$config = $gridField->getConfig();
		$config->removeComponentsByType(GridFieldImportButton::class);
		$config->removeComponentsByType(GridFieldPrintButton::class);

		// Configure filtering options
        $updateSearchContext = function(SearchContext $context) {
            $filters = $context->getFilters();
            $filters['MinDate'] = GreaterThanOrEqualFilter::create('Created');
            $filters['MaxDate'] = LessThanOrEqualFilter::create('Created');
            $context->setFilters($filters);
        };

        $updateSearchForm = function(Form $form) {
            // Replace free-text with dropdown of available submission types
            $types = ArrayLib::valuekey(CapturedFormSubmission::get()->columnUnique('Type'));
            $typeField = DropdownField::create('Search__Type', 'Type', $types)
                ->setEmptyString(_t('SilverStripe\\Forms\\DropdownField.CHOOSE', '(Choose)'))
                ->addExtraClass('stacked');

            $form->Fields()->replaceField('Search__Type', $typeField);
            $form->Fields()->push($minDate = DateField::create('MinDate', 'Submitted from'));
            $form->Fields()->push($maxDate = DateField::create('MaxDate', 'Submitted to'));

            foreach ([$minDate, $maxDate, $typeField] as $field) {
                $field->addExtraClass('stacked')
                    ->setForm($form);
            }
        };

        $config->removeComponentsByType(GridFieldFilterHeader::class);
        $config->addComponent(new GridFieldFilterHeader(false, $updateSearchContext, $updateSearchForm));
        // Filter only updates page indicators if paginator is added *after* it, so we have to remove and re-add it
        // See https://github.com/silverstripe/silverstripe-framework/issues/8454
        $config->removeComponentsByType(GridFieldPaginator::class);
        $config->addComponent(new GridFieldPaginator());

        // Configure CSV export columns
        $fieldTable = DataObject::getSchema()->tableName(CapturedField::class);
        $columnSelect = SQLSelect::create()
            ->setSelect('Name')
            ->setFrom($fieldTable)
            ->setGroupBy(['Name']);

        $columns = [
            'Type' => 'Type',
            'Created' => 'Date'
        ];
        foreach ($columnSelect->execute() as $row) {
            $columns["export__{$row['Name']}"] = $row['Name'];
        }

        // Replace existing export button with new one (queued export if module is installed)
        $config->removeComponentsByType(GridFieldExportButton::class);
        if (class_exists(GridFieldQueuedExportButton::class)) {
            $export = new GridFieldQueuedExportButton('buttons-before-left');
        } else {
            $export = new GridFieldExportButton('buttons-before-left');
        }

        $config->addComponent($export);
        $export->setExportColumns($columns);

		return $form;
	}

}
