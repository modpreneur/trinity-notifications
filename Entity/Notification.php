<?php
/**
 * Created by PhpStorm.
 * User: Jakub Fajkus
 * Date: 24.03.16
 * Time: 15:38
 */

namespace Trinity\NotificationBundle\Entity;


class Notification
{
    const DELETE = 'DELETE';
    const POST = 'POST';
    const PUT = 'PUT';

    const METHOD = "method";
    const DATA = "data";
    const BATCH_ID = "batchId";

    /**
     * @var string
     */
    protected $batchId;


    /**
     * @var array Array of notification data(e.g. name, description)
     */
    protected $data;


    /**
     * @var string HTTP method of the message.
     */
    protected $method;

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }


    /**
     * @param string $method
     * @return Notification
     */
    public function setMethod(string $method)
    {
        $this->method = $method;

        return $this;
    }


    /**
     * @return string
     */
    public function getBatchId()
    {
        return $this->batchId;
    }


    /**
     * @param string $batchId
     * @return Notification
     */
    public function setBatchId(string $batchId)
    {
        $this->batchId = $batchId;

        return $this;
    }


    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * @param array $data
     * @return Notification
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Convert object into array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::BATCH_ID => $this->batchId,
            self::METHOD => $this->method,
            self::DATA => $this->data
        ];
    }


    /**
     * Fill object with data.
     *
     * @param array $array
     * @param string $batch
     * @return $this
     */
    public function fromArray(array $array, string $batch)
    {
        $this->batchId = $batch;
        $this->data = $array[self::DATA];
        $this->method = $array[self::METHOD];

        return $this;
    }

}