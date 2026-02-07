# Vich basic usage

## Mapping

```yaml
# config/packages/vich_uploader.yaml
mappings:
  # ...
  products:
    uri_prefix: /assets/images/products
    upload_destination: '%kernel.project_dir%/public/assets/images/products'
    namer: Vich\UploaderBundle\Naming\UniqidNamer
```

## Entity

Note:

- $pictureFile is not a mapped field
- $picturePath should be nullable for deletion to work

```php
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Vich\UploaderBundle\Validator\Constraints as VichAssert;

#[Vich\Uploadable]
class Product {
    #[Vich\UploadableField(mapping: 'user_pictures', fileNameProperty: 'picturePath')]
    #[Assert\File(
        maxSize: "5M",
        mimeTypes: ["image/jpeg", "image/png"],
    )]
    #[VichAssert\FileRequired(
        target: 'pictureFile',
        message: 'Please upload a file.',
    )]
    private ?File $pictureFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $picturePath = null;

    public function getPictureFile(): ?File
    {
        return $this->pictureFile;
    }

    public function setPictureFile(?File $pictureFile = null): void
    {
        $this->pictureFile = $pictureFile;

        if (null !== $pictureFile) {
            /// It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTime();
        }
    }

    # ...
}
```

## Getter & setter

```php
public function getPictureFile(): ?File
{
    return $this->pictureFile;
}

public function setPictureFile(?File $pictureFile = null): void
{
    $this->pictureFile = $pictureFile;

    if (null !== $pictureFile) {
        /// It is required that at least one field changes if you are using doctrine
        // otherwise the event listeners won't be called and the file is lost
        $this->updatedAt = new \DateTime();
    }
}
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
// Setting file property to null as user object is serialized and saved in the session (a File is not serializable)
$user->setPictureFile(null);
```
