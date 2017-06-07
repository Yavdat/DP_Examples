<?php

abstract class Question {
    protected $prompt;
    protected $marker;

    function __construct( $prompt, Marker $marker )
    {
        $this->marker = $marker;
        $this->marker = $prompt;
    }

    function mark( $response )
    {
        return $this->marker->mark($response);
    }
}

class TextQuestion extends Question {
    //Выполняются действия, специфичные для текстовых вопросов
}

class AVQuestion extends Question {
    //Выполняются действия, специфичные для мультимедийных вопросов
}

abstract class Marker {
    protected $test;

    function __construct( $test )
    {
        $this->test = $test;
    }

    abstract function mark($response);
}

class MarkLogicMarker extends Marker {

    private $engine;

    function __construct($test)
    {
        parent::__construct($test);
//        $this->engine = new MarkerParse($test);
    }

    function mark($response) {
//        return $this->engine->evaluate($response);
        //Возвратим фиктивное значение;
        return true;
    }
}

class MatchMarker extends Marker {
    function mark($response){
        return ( $this->test == $response );
    }
}

class RegexpMarker extends Marker {
    function mark( $response ) {
        return (preg_match($this->test, $response));
    }
}

$markers = array(new RegexpMarker("/П.ть/"),
    new MatchMarker("Marker"),
    new MarkLogicMarker('$input equals "Пять"')
);

foreach ($markers as $marker) {
    print get_class($marker)."\n";
    $question = new TextQuestion("Сколько лучей к Кремлевской звезды?", $marker);

    foreach ( array("Пять", "Четыре") as $response ) {
        print "Ответ: $response: ";
        if ($question->mark($response)) {
            print "Правильно! \n";
        } else {
            print "Неверно! \n";
        }
    }
}

