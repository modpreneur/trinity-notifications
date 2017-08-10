<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 10.05.16
 * Time: 12:39.
 */
namespace Trinity\NotificationBundle\Annotations;

/**
 * Class AssociationSetter.
 *
 *
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("targetClass", type = "string")
 * })
 */
class AssociationSetter
{
    /** @var string */
    protected $targetEntity;

    /**
     * AssociationSetter constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->targetEntity = $values['targetEntity'];
    }

    /**
     * @return string
     */
    public function getTargetEntity()
    {
        return $this->targetEntity;
    }
}
