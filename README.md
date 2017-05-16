# Silverstripe Form Capture
Provides a method to capture simple silverstripe forms and a friendly admin interface for users.

## Installation
Either clone/download this repository into a folder named 'silverstripe-form-capture' or run:

```
composer require andrewhaine/silverstripe-form-capture dev-master
```

## Initialisation
After installing you will need to run 'dev/build' and add the module extension to the form class or any subclass of form that you wish to store.

### Adding the extension:

In 'config.yml':

```yaml
Form:
  extensions:
    - CapturedFormExtension
```

Or alternatively in '\_config.php'

```php
Form::add_extension('CapturedFormExtension');
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
	$form->captureForm($form);

	// Other processing
}
```

### Options
When calling the captureForm() method it is required that the Form to be stored is passed as the first parameter, there are two additional parameters which are optional:

* __Form Submission Title__ - A string which will be displayed as the submission title in the admin area (defaults to 'Form Submission').
* __Excluded Fields__ - An array of field names which will not be stored, this can also be a string containing the name of a single field to exclude.

#### Example

```php
$form->captureForm($form, 'Contact Form Submission', ['IDontWantThisField', 'OrThisOne']);
```
