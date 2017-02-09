<?php

namespace AppBundle\Service;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use \Symfony\Component\Serializer\Serializer as SymfonySerializer;

class Serializer
{
    /** @var SymfonySerializer */
    private $serializer;

    /**
     * Serializer constructor.
     *
     * @param Serializer $serializer
     */
    public function __construct(SymfonySerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function json($data, $groups = [])
    {
        /*$classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $encoders             = [new XmlEncoder(), new JsonEncoder()];
        $normalizers          = [new ObjectNormalizer($classMetadataFactory)];
        $serializer           = new Serializer($normalizers, $encoders);*/
        $context              = !empty($groups) ? ['groups' => $groups] : null;

        return $this->serializer->normalize($data, JsonEncoder::FORMAT, $context);
    }

}
