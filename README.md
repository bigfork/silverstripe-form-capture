# Silverstripe Form Capture
Provides a method to capture simple Silverstripe forms and a friendly admin interface for users.

<img src="docs/images/screenshot.png" width="900" height="417" />

## Installation

```
composer require bigfork/silverstripe-form-capture
```

After installing you will need to run 'dev/build'.

## Usage
To store submissions from a form simply call `$form->captureForm()` in your form handler method. See the example below for usage:

### Example
In the page controller:

```php
public function MyForm()
{
	$fields = FieldList::create(
		TextField::create('Name'),
		EmailField::create('Email'),
		TextareaField::create('Enquiry')
	);

	$actions = FieldList::create(
		FormAction::create('doMyForm', 'Submit')
	);

	$form = Form::create($this, __FUNCTION__, $fields, $actions);

	return $form;
}

public function doMyForm($data, $form)
{
	$form->captureForm(
	    'Enquiry form submission', // Required - type of form submission
	    'Name', // Required (can be null) - form field containing the submitter's name
	    'Email', // Required (can be null) - form field containing the submitter's email address
	    ['Captcha'], // Optional - list of fields that shouldn't be stored
	    ['Enquiry'] // Optional - list of fields to show in "Details" column in CMS
	);

	// Other processing
}
```

When capturing a form some useful information is returned which can be used in the controller. For example a link is returned to the submission area in the CMS.

```php
$capturedSubmission = $form->captureForm('Contact form', null, null);

echo($capturedSubmission['Link']);
// http://your-site.com/admin/<Link to exact submission>
```
