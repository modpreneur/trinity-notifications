<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\NotificationBundle\AppTests;

use Trinity\NotificationBundle\Annotations\Source;
use Trinity\NotificationBundle\Annotations\Url;


/**
 * Class AnnotationsTest.
 */
class AnnotationsTest extends BaseTest
{
    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     * @expectedExceptionMessage Annotation error: Url postfix is not set.
     */
    public function testURLWithError()
    {
        $urlClass = new Url();
    }


    /**
     * @test
     */
    public function testHasColumns()
    {
        $source = new Source();
        $this->assertFalse($source->hasColumns());
    }
}
