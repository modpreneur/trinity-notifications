<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 28.04.16
 * Time: 19:07.
 */
namespace Trinity\NotificationBundle\DataTransformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class NotificationFormTransformer.
 */
class NotificationTransformer implements DataTransformerInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var  string */
    protected $entityName;

    /** @var  string If server mode, this property contains name of the method which is used to set the server id */
    protected $idSetterMethod;

    /** @var  string Name of the field which is mapped to the id field from notification*/
    protected $idFieldName;

    /** @var  string */
    protected $idGetterMethod;

    /**
     * NotificationFormTransformer constructor.
     * It is called in the Type class.
     *
     * @param EntityManagerInterface $entityManager
     * @param string                 $entityName
     * @param string                 $idFieldName
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        string $entityName,
        string $idFieldName
    ) {
        $this->entityManager = $entityManager;
        $this->entityName = $entityName;
        $this->idFieldName = $idFieldName;
        $this->idSetterMethod = 'set'.ucfirst($idFieldName);
        $this->idGetterMethod = 'get'.ucfirst($idFieldName);
    }

    /**
     * Transforms a value from the transformed representation to its original
     * representation.
     *
     * This method is called when {@link Form::submit()} is called to transform the requests tainted data
     * into an acceptable format for your data processing/model layer.
     *
     * This method must be able to deal with empty values. Usually this will
     * be an empty string, but depending on your implementation other empty
     * values are possible as well (such as empty strings). The reasoning behind
     * this is that value transformers must be chainable. If the
     * reverseTransform() method of the first value transformer outputs an
     * empty string, the second value transformer must be able to process that
     * value.
     *
     * By convention, reverseTransform() should return NULL if an empty string
     * is passed.
     *
     * @param mixed $value The value in the transformed representation
     *
     * @return mixed The value in the original representation
     *
     * @throws \UnexpectedValueException
     * @throws TransformationFailedException When the transformation fails.
     */
    public function reverseTransform($value)
    {
        if ($value === null || empty($value)) {
            return;
        }

        $entity = $this
            ->entityManager
            ->getRepository($this->entityName)
            ->findOneBy([$this->idFieldName => $value]);

        if ($entity === null) {
            $entityClass = new \ReflectionClass($this->entityName);

            $entity = $entityClass->newInstanceArgs();
            if ($this->idSetterMethod !== null) {
                $entity->{$this->idSetterMethod}((int) $value);
            }
        }

        return $entity;
    }

    /**
     * Transforms a value from the original representation to a transformed representation.
     *
     * This method is called on two occasions inside a form field:
     *
     * 1. When the form field is initialized with the data attached from the datasource (object or array).
     * 2. When data from a request is submitted using {@link Form::submit()} to transform the new input data
     *    back into the renderable format. For example if you have a date field and submit '2009-10-10'
     *    you might accept this value because its easily parsed, but the transformer still writes back
     *    "2009/10/10" onto the form field (for further displaying or other purposes).
     *
     * This method must be able to deal with empty values. Usually this will
     * be NULL, but depending on your implementation other empty values are
     * possible as well (such as empty strings). The reasoning behind this is
     * that value transformers must be chainable. If the transform() method
     * of the first value transformer outputs NULL, the second value transformer
     * must be able to process that value.
     *
     * By convention, transform() should return an empty string if NULL is
     * passed.
     *
     * @param mixed $value The value in the original representation. Entity in this case.
     *
     * @return mixed The value in the transformed representation
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     * @throws TransformationFailedException                                   When the transformation fails.
     */
    public function transform($value)
    {
        if (!$value) {
            return '';
        }

        if (is_object($value)) {
            if (method_exists($value, $this->idGetterMethod)) {
                return $value->{$this->idGetterMethod}();
            } elseif (property_exists($value, $this->idFieldName)) {
                return $value->{$this->idFieldName};
            }
        }

        throw new TransformationFailedException('Given value is not an object');
    }
}
