<?php

abstract class ApptEncoder {
    abstract function encode();
}

class BloggsApptEncoder extends ApptEncoder {
    function encode()
    {
        return "Данные о встече закодированы в формате BloggsCall \n";
    }
}

abstract class CommsManager {
    abstract function getHeaderText();
    abstract function getApptEncoder();
    abstract function getFooterText();
}

class BloggsCommsManager extends CommsManager {
    function getHeaderText()
    {
        return "BloggsCal uppercase";
    }

    function getApptEncoder()
    {
        return new BloggsApptEncoder();
    }

    function getFooterText()
    {
        return "BloggsCal lowercase";
    }
}

$mgr = new BloggsCommsManager();
print $mgr->getHeaderText();
print $mgr->getApptEncoder()->encode();
print $mgr->getFooterText();

//abstract class CommsManager {
//    const APPT = 1;
//    const TTD = 2;
//    const CONTACT = 3;
//
//    abstract function getHeaderText();
//    abstract function make(int $flag);
//    abstract function getFooterText();
//}
//
//class BloggsCommsManager extends CommsManager {
//    function getHeaderText() {
//        return "BloggsCal uppercase\n";
//    }
//
//    function make(int $flag) {
//        switch ($flag) {
//            case self::APPT:
//                return new BloggsApptEncoder();
//            case self::CONTACT:
//                return new BloggsContactEncoder();
//            case self::TTD:
//                return new BloggsTtdEncoder();
//        }
//    }
//
//    function  getFooterText() {
//        return "BloggsCal lowerCase\n";
//    }
//}