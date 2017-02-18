<?php

/*
 * Стратегия
 * Поведенческий шаблон проектирования
 * предназначенный для определения семейства алгоритмов, инкапсуляции каждого из них
 * обеспечения их взаимозаменяемости.
 * Это позволяет выбирать алгоритм путем определения соответствующего класса.
 * Шаблон Стратегия позволяет менять выбранный алгоритм независимо от объектов-клиентов, которые его используют.
 *
 * Задача
 * По типу клиента(обрабатываемых данных) выбрать подходящий алгоритм, который следует применить. Если используется правило,
 * которое не подвержено изменениям, нет необходимости обращаться к шаблону Strategy
 *
 * Мотивы программа должна обеспечивать различные варианты алгоритма или поведения
 * Нужно изменять поведение каждого экземпляра класса
 * Необходимо изменять поведение объектов на стадии выполнения
 * Введение интерфейса позволяет классам-клиентам ничего не знать о классах, реализующих этот интерфейс и инкапсулирующих
 * в себе конкретные алгоритмы
 *
 *
 */

abstract class Lesson {
    private $duration;
    private $costStrategy;

    function __construct($duration, CostStrategy $strategy) {
        $this->duration = $duration;
        $this->costStrategy = $strategy;
    }

    function cost(){
        return $this->costStrategy->cost($this);
    }

    function chargeType(){
        return $this->costStrategy->chargeType();
    }

    function getDuration() {
        return $this->duration;
    }
    //Another methods
}

class Lecture extends Lesson {
    //specific realize
}

class Seminar extends Lesson {
    //specific realize
}

abstract class CostStrategy {
    abstract function cost(Lesson $lesson);
    abstract function chargeType();
}

class TimedCostStrategy extends CostStrategy {
    function cost(Lesson $lesson) {
        return ( $lesson->getDuration()*5 );
    }
    function chargeType()
    {
        return "Почасовая оплата\n";
    }
}

class FixedCostStrategy extends CostStrategy {
    function cost(Lesson $lesson) {
        return 30;
    }
    function chargeType()
    {
        return "Фиксированная ставка\n";
    }
}

$lessons[] = new Seminar(4, new TimedCostStrategy());
$lessons[] = new Lecture(4, new FixedCostStrategy());

foreach ($lessons as $lesson) {
    print "Payment for Lesson {$lesson->cost()}\n";
    print "Payment type {$lesson->chargeType()}\n";
}