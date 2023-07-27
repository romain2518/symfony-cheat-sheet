# Vich basic usage

## Annotate the entity class with the Uploadable

- `use Vich\UploaderBundle\Mapping\Annotation as Vich;`
- `#[Vich\Uploadable]`

## File property

With file validation :

```php
#[Vich\UploadableField(mapping: 'user_pictures', fileNameProperty: 'picturePath')]
#[Assert\File(
    maxSize: "5M",
    mimeTypes: ["image/jpeg", "image/png"],
)]
private ?File $pictureFile = null;
```

Without file validation :

```php
#[Vich\UploadableField(mapping: 'user_pictures', fileNameProperty: 'picturePath')]
private ?File $pictureFile = null;
```

## Getter & setter

```php
public function getPictureFile(): ?File
{
    return $this->pictureFile;
}

public function setPictureFile(?File $pictureFile = null): self
{
    $this->pictureFile = $pictureFile;

    if (null !== $pictureFile) {
        /// It is required that at least one field changes if you are using doctrine
        // otherwise the event listeners won't be called and the file is lost
        $this->updatedAt = new \DateTime();
    }

    return $this;
}
```

## Accept null on path porperty setter

```php
public function setPicturePath(?string $picturePath): self
{
    $this->picturePath = $picturePath;

    return $this;
}
```

## Mapping

`config/packages/vich_uploader.yaml`

```yaml
mappings:
    # ...
    products:
        uri_prefix: /assets/images/products
        upload_destination: '%kernel.project_dir%/public/assets/images/products'
        namer: Vich\UploaderBundle\Naming\UniqidNamer
```

## Form

```php
<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;

class EditProduct extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pictureFile', VichImageType::class, [
                'attr' => ['accept' => 'image/png, image/jpeg'],
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
                'image_uri' => false,
                'empty_data' => '',
            ])
        ;
    }
}
```

## When dealing with user or any object that must be serialized, an error can be thrown if the file property is not set to null

In controller, after editing object :

```php
// Setting file properties to null as user object is serialized and saved in the session (a File is not serializable)
$user->setPictureFile(null);
```

## Not blank validator

Default NotBlank validator doesn't work with Vich file properties, therefore we can add a custom validator.

```php
<?php
# src/Validator/NotBlankVich.php
namespace App\Validator;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraints\NotBlank;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class NotBlankVich extends NotBlank
{
    public $target = null;
    public $message = null;

    #[HasNamedArguments]
    public function __construct(string $target, string $message, array $groups = null, mixed $payload = null)
    {
        parent::__construct([], $message, null, null, $groups, $payload);

        $this->target = $target;
    }
}
```

```php
<?php
# src/Validator/NotBlankVichValidator.php
namespace App\Validator;

use App\Validator\NotBlankVich;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlankValidator;

class NotBlankVichValidator extends NotBlankValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($constraint instanceof NotBlankVich && $constraint->target) {
            $targetValue = PropertyAccess::createPropertyAccessor()->getValue($this->context->getObject(), $constraint->target);

            if (!empty($targetValue)) {
                return;
            }
        }

        parent::validate($value, $constraint);
    }
}
```

To use :

```php
<?php

namespace App\Entity;

use App\Validator as CustomAssert;

class Product
{
    #[Vich\UploadableField(mapping: 'anime_pictures', fileNameProperty: 'picturePath')]
    #[CustomAssert\NotBlankVich(message: 'Please provide a picture to create a anime.', target: 'picturePath')]
    private ?File $pictureFile = null;
}
```
