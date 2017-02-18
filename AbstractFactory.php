<?php

/**
 * Class ApptEncoder
 */
abstract class ApptEncoder {
    abstract function encode();
}

/**
 * Class TtdEncoder
 */
abstract class TtdEncoder {
    abstract function encode();
}

/**
 * Class ContactEncoder
 */
abstract class ContactEncoder {
    abstract function encode();
}

/**
 * Class BloggsApptEncoder
 */
class BloggsApptEncoder extends ApptEncoder {
    function encode()
    {
        return "BloggsApptEncoder method is called!\n";
    }
}

/**
 * Class BloggsTtdEncoder
 */
class BloggsTtdEncoder extends TtdEncoder {
    function encode()
    {
        return "BloggsTtdEncoder method is called!\n";
    }
}

/**
 * Class BloggsContactEncoder
 */
class BloggsContactEncoder extends ContactEncoder {
    function encode()
    {
        return "BloggsContactEncoder method is called \n";
    }
}

/**
 * Class CommsManager
 */
abstract class CommsManager {
    abstract function getHeaderText();
    abstract function getApptEncoder();
    abstract function getTtdEncoder();
    abstract function getContactEncoder();
    abstract function getFooterEncoder();
}

/**
 * Class BloggsCommsManager
 */
class BloggsCommsManager extends CommsManager {

    function getHeaderText(){
        return "BloggsCal UpperCase\n";
    }

    function getApptEncoder(){
        return new BloggsApptEncoder();
    }

    function getTtdEncoder(){
        return new BloggsTtdEncoder();
    }

    function getContactEncoder(){
        return new BloggsContactEncoder();
    }

    function getFooterEncoder(){
        return "BloggsCal LowerCase\n";
    }
}

