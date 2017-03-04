<?php
namespace Decorator;

abstract class Tile {
    abstract function getWealthFactor();
}

class Plains extends Tile {
    private $wealthfactor = 2;

    function getWealthFactor()
    {
        return $this->wealthfactor;
    }
}

abstract class TileDecorator extends Tile {
    protected $tile;

    function __construct( Tile $tile)
    {
        $this->tile = $tile;
    }
}

class DiamondDecorator extends TileDecorator {
    function getWealthFactor() {
        return $this->tile->getWealthFactor()+2;
    }
}

class PollutionDecorator extends TileDecorator {
    function getWealthFactor()
    {
        return $this->tile->getWealthFactor()-4;
    }
}

$tile = new Plains();
print $tile->getWealthFactor(); // Returned 2;

$tile = new DiamondDecorator( new Plains());
print $tile->getWealthFactor(); // Returned 4;

$tile = new PollutionDecorator(new DiamondDecorator(new Plains()));
print $tile->getWealthFactor(); // Returned 0;

//abstract class Tile {
//    abstract function getWealthFactor();
//}
//
//class Plains extends Tile {
//
//    private $wealthfactor = 2;
//
//    function getWealthFactor()
//    {
//        return $this->wealthfactor;
//    }
//}
//
//class DiamondPlains extends Plains {
//    function getWealthFactor()
//    {
//        return parent::getWealthFactor()+2;
//    }
//}
//
//class PollutedPlains extends Plains {
//    function getWealthFactor()
//    {
//        return parent::getWealthFactor()-4;
//    }
//}
//
//$tile = new PollutedPlains();
//print $tile->getWealthFactor();
