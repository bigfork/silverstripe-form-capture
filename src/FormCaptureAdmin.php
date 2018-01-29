<?php

namespace SSFormCapture;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldImportButton;
use SilverStripe\Forms\GridField\GridFieldPrintButton;

use SSFormCapture\CapturedFormSubmission;

class MyAdmin extends ModelAdmin
{

    private static $managed_models = [CapturedFormSubmission::class];

    private static $url_segment = 'captured-form-submmissions';

    private static $menu_title = 'Form Submissions';

    private static $menu_icon ='silverstripe-form-capture/icon/captured-form-submissions.png';

    public function getEditForm($id = null, $fields = null)
	{
		$form = parent::getEditForm();
		$gridField = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass));
		$gridField->getConfig()->removeComponentsByType(GridFieldExportButton::class);
		$gridField->getConfig()->removeComponentsByType(GridFieldImportButton::class);
		$gridField->getConfig()->removeComponentsByType(GridFieldPrintButton::class);
		return $form;
	}

}
