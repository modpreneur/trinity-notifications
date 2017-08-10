<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 25.05.16
 * Time: 9:56.
 */
namespace Trinity\NotificationBundle\Entity;

/**
 * Class NotificationRequest.
 */
class NotificationRequest
{
    const ENTITY_NAME = 'entityName';
    const ENTITY_ID = 'entityId';

    /** @var string */
    protected $entityName;

    /** @var  string */
    protected $associationEntityId;

    /**
     * NotificationRequest constructor.
     *
     * @param string $entityName
     * @param string $associationEntityId
     */
    public function __construct( $entityName, $associationEntityId)
    {
        $this->entityName = $entityName;
        $this->associationEntityId = $associationEntityId;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param string $entityName
     */
    public function setEntityName( $entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * @return string
     */
    public function getAssociationEntityId()
    {
        return $this->associationEntityId;
    }

    /**
     * @param string $associationEntityId
     */
    public function setAssociationEntityId( $associationEntityId)
    {
        $this->associationEntityId = $associationEntityId;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [self::ENTITY_ID => $this->associationEntityId, self::ENTITY_NAME => $this->entityName];
    }

    /**
     * @param array $data
     *
     * @return NotificationRequest
     */
    public static function fromArray(array $data)
    {
        return new self($data[self::ENTITY_NAME], $data[self::ENTITY_ID]);
    }
}
