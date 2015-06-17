<?php

namespace Trinity\NotificationBundle\Resources\twig;

use Nette\Utils\DateTime;
use Nette\Utils\Html;


/**
 * Class JsonExtension
 * @author Tomáš Jančar
 *
 * @package Trinity\NotificationBundle\Resources\twig
 */
class JsonExtension extends \Twig_Extension {


    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('json_pretty_printer', [$this, 'jsonPrinter'], ['is_safe' => ['html']]),
        );
    }


    /**
     * @param $str
     * @return Html
     */
    public function jsonPrinter($str)
    {
        $output = Html::el("div");
        $output->addAttributes(["class" => "pretty-json"]);
        $objectJson = json_decode($str);

        foreach($objectJson as $index => $key){

            $p = Html::el("p");
            $p->add(Html::el("strong", $index . ": "));

            if(!is_object($key)){
                $item = Html::el("span", $key);

                // date
                if($index == "timestamp"){
                    $item = Html::el("time", $key);
                    $dateTime = new DateTime();
                    $dateTime->setTimestamp($key);
                    $stringDate = $dateTime->format(DateTime::ISO8601);
                    $item->addAttributes(['title' => $stringDate]);
                    $item->addAttributes(['date'  => $stringDate]);
                }

                $p->add($item);

            } else {
                $p->add(Html::el("span", "Object: " . get_class($key)));
            }

            $output->add($p);
        }

        return $output;
    }



    public function getName()
    {
        return "notification.json";
    }


}