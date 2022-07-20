<?php
# src/Serializer/DoctrineDenormalizer.php
namespace App\Serializer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class DoctrineDenormalizer implements DenormalizerInterface
{

    /** @var EntityManagerInterface */
    private $entityManagerInterface;

    public function __construct(EntityManagerInterface $entityManagerInterface)
    {
        $this->entityManagerInterface = $entityManagerInterface;
    }

    public function supportsDenormalization($data, string $type, ?string $format = null): bool
    {
        $dataIsID = is_numeric($data);
        $typeIsEntity = strpos($type, 'App\Entity') === 0;

        return $typeIsEntity && $dataIsID;
    }

    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $denormalizedEntity = $this->entityManagerInterface->find($type, $data);

        return $denormalizedEntity;
    }
}
