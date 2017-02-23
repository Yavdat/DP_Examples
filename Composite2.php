<?php
namespace Composite2;

abstract class Unit
{
    function getComposite()
    {
        return null;
    }

    abstract function bombardStrength();
}

abstract class CompositeUnit extends Unit
{
    private $units = array();

    function getComposite()
    {
        return $this;//parent::getComposite();
    }

    protected function units()
    {
        return $this->units;
    }

    function addUnit( Unit $unit)
    {
        if( in_array( $unit, $this->units, true)){
            return;
        }
        $this->units[] = $unit;
    }

    function removeUnit(Unit $unit)
    {
        $this->units = array_udiff($this->units, array($unit),
            function ($a, $b) {
                return ($a === $b) ? 0 : 1;
            }
        );
    }
}

class Army extends CompositeUnit {
    private $units = array();

    function addUnit( Unit $unit ){
        if( in_array($unit, $this->units, true)) {
            return;
        }
        $this->units[] = $unit;
    }

    function removeUnit(Unit $unit)
    {
        $this->units = array_udiff($this->units, array($unit),
            function($a, $b) {return ($a === $b)?0:1;});
    }

    function bombardStrength()
    {
        $ret = 0;
        foreach ($this->units as $unit) {
            $ret+=$unit->bombardStrength();
        }
        return $ret;
    }
}

class UnitException extends \Exception {}

class Archer extends Unit {

    function bombardStrength()
    {
        return 4;
    }
}

class LaserCannonUnit extends Unit {

    function bombardStrength()
    {
        return 44;
    }
}



$main_army = new Army();

$main_army->addUnit(new Archer());
$main_army->addUnit(new LaserCannonUnit());


$sub_army = new Army();

$sub_army->addUnit(new Archer());
$sub_army->addUnit(new Archer());
$sub_army->addUnit(new Archer());

$main_army->addUnit($sub_army);

echo $main_army->getComposite()->bombardStrength();


print "Атакующая сила: {$main_army->bombardStrength()} \n";
