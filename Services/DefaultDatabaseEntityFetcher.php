<?php

namespace Trinity\NotificationBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Trinity\NotificationBundle\Entity\NotificationEntityInterface;
use Trinity\NotificationBundle\Interfaces\DatabaseEntityFetcherInterface;

/**
 * Class DefaultDatabaseEntityFetcher
 *
 * Fetches all entities by their id field
 */
class DefaultDatabaseEntityFetcher implements DatabaseEntityFetcherInterface
{
    /** @var EntityAliasTranslator */
    protected $aliasTranslator;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var string Field name which will be mapped to the id from the notification request */
    protected $entityIdFieldName;

    /**
     * DefaultDatabaseEntityFetcherInterface constructor.
     * @param EntityAliasTranslator $aliasTranslator
     * @param EntityManagerInterface $entityManager
     * @param string $entityIdFieldName
     */
    public function __construct(
        EntityAliasTranslator $aliasTranslator,
        EntityManagerInterface $entityManager,
        string $entityIdFieldName
    ) {
        $this->aliasTranslator = $aliasTranslator;
        $this->entityManager = $entityManager;
        $this->entityIdFieldName = $entityIdFieldName;
    }

    /**
     * Fetch an entity from the database.
     *
     * @param string $fullClassName FQCN of the entity.
     * @param array $notificationData Associative array of data.
     *          For keys, refer to the entity's Source annotation on the sender system.
     *
     * @return null|NotificationEntityInterface
     */
    public function fetchEntity(string $fullClassName, array $notificationData): ?NotificationEntityInterface
    {
        /** @var NotificationEntityInterface|null $entity */
        $entity = $this->entityManager->getRepository($fullClassName)->findOneBy(
            [$this->entityIdFieldName => $notificationData['id']]
        );

        return $entity;
    }
}