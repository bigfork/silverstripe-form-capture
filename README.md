# Silverstripe Form Capture
Provides a method to capture simple silverstripe forms and a friendly admin interface for users.

## Installation
Either clone/download this repository into a folder named 'silverstripe-form-capture' or run:

```
composer require andrewhaine/silverstripe-form-capture ~2.0
```

## Initialisation
After installing you will need to run 'dev/build' and add the module extension to the form class or any subclass of form that you wish to store.

### Adding the extension:

In 'mysite.yml':

```yaml
SilverStripe\Forms\Form:
  extensions:
    - SSFormCapture\FormCaptureExtension
```

## Usage
To store submissions from a form simply add a call to the new method in the function you will use to handle the form. See the example below for usage

### Example
In the page controller:

```php
public function MyForm() {
	$fields = FieldList::create(
		TextField('ExampleTextField'),
		TextareaField::create('ExampleTextareaField')
	);

	$actions = FieldList::create(
		FormAction::create('doMyForm', 'Submit')
	);

	$form = Form::create($this, __FUNCTION__, $fields, $actions);

	return $form;
}

public function doMyForm($data, $form) {
	$form->captureForm();

	// Other processing
}
```

### Options
Call the captureForm() method on a form you wish to store the data of, optional parameters can be passed as follows:

* __Form Submission Title__ - A string which will be displayed as the submission title in the admin area (defaults to 'Form Submission').
* __Excluded Fields__ - An array of field names which will not be stored, this can also be a string containing the name of a single field to exclude.
* __Details Fields__ - An array of fields which will be included in the 'details' column of the gridfield, this can also be a string containing the name of a single field to include in the details.

#### Example

```php
$form->captureForm('Contact Form Submission', ['IDontWantThisField', 'OrThisOne'], 'Details');
```
