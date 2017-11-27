<?php

namespace SSFormCapture;

use SilverStripe\Admin\ModelAdmin;
use SSFormCapture\CapturedFormSubmission;

class MyAdmin extends ModelAdmin
{

    private static $managed_models = [CapturedFormSubmission::class];

    private static $url_segment = 'captured-form-submmissions';

    private static $menu_title = 'Form Submissions';

    private static $menu_icon ='silverstripe-form-capture/icon/captured-form-submissions.png';

}
