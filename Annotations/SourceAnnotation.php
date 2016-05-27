<?php

/*
 * This file is part of the Trinity project.
 *
 */

namespace Trinity\NotificationBundle\Annotations;

/**
 * Class SourceAnnotation
 *
 * @package Trinity\NotificationBundle\Annotations
 *
 * @Annotation
 */
class SourceAnnotation
{
    const ANNOTATION_CLASS = '\\Trinity\\NotificationBundle\\Annotations\\Source';
    const SERIALIZED_NAME = '\\JMS\\Serializer\\Annotation\\SerializedName';
}
