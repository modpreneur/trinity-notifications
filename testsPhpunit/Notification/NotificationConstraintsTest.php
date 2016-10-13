<?php


class NotificationConstraintsTest extends \PHPUnit_Framework_TestCase
{
    /** @var  array */
    static protected $invalidDataSet;

    /** @var  array */
    static protected $validDataSet;

    public static function setUpBeforeClass()
    {
//        $entity = self::createMock(\Trinity\NotificationBundle\Entity\NotificationEntityInterface::class);

//        self::$invalidDataSet = [
//
//        ];
    }

    public function testVariations()
    {
        echo "start";
        return;
        self::assertEquals([], $this->createAllVariations());
        self::assertEquals([[1,2,3]], $this->createAllVariations(1,2,3));
        self::assertEquals(
            [
                [1,'a','A'],
                [1,'b','A'],
                [1,'b','B'],
                [1,'a','B'],
                [2,'a','A'],
                [2,'b','A'],
                [2,'b','B'],
                [2,'a','B'],
            ],
            $this->createAllVariations([1,2],['a','b'],['A', 'B'])
        );
    }


    /**
     * returns array
     * [
     *  [val1, val2, val3,...]
     * ]
     */
    protected function createAllVariations()
    {
        $returned = [];
        $returnedIndex = 0;
        foreach (func_get_args() as $arg) {
            echo "foreach1";
            $valueIndex = 0;
            foreach ($arg as $value) {
                echo "foreach2";
                $returned[$returnedIndex][$valueIndex] = $value;
                $valueIndex++;
            }

        }

        return $returned;
    }
}