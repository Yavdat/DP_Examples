<?php
namespace Facade;

/**
 *Реализация отдельных частей компьютера.
 * У каждого метода классов имеется какая-то реализация, в данном примере она опущена.
 */

class CPU {
    public function freeze() {}
    public function jump($position) {}
    public function execute() {}
}

class Memory
{
    const BOOT_ADDRESS = 0x0005;
    public function load($position, $data) {}
}

class HardDrive
{
    const BOOT_SECTOR = 0x001;
    const SECTOR_SIZE = 64;
    public function read($lba, $size) {}
}


// ---------- realized By Zanstra
class Computer
{
    protected $cpu;
    protected $memory;
    protected $hardDrive;

    /**
     * Computer constructor.
     * Инициализируем части.
     */

    public function __construct()
    {
        $this->cpu = new CPU();
        $this->memory = new Memory();
        $this->hardDrive = new HardDrive();
    }

    /**
     * Упрощенная обработка запуска Компа
     */

    public function startComputer()
    {
        $cpu = $this->cpu;
        $memory = $this->memory;
        $hardDrive = $this->hardDrive;

        $cpu->freeze();
        $memory->load(
            $memory::BOOT_ADDRESS,
            $hardDrive->read($hardDrive::BOOT_SECTOR, $hardDrive::SECTOR_SIZE)
        );

        $cpu->jump($memory::BOOT_ADDRESS);
        $cpu->execute();
    }
}

$computer = new Computer();
$computer->startComputer();


function getProductFileLines ( $file ){
    return file( $file );
}

function getProductObjectFromId( $id, $productName ) {
    //Выполняем запрос к базе данных
    return new ProductFacade( $id, $productName );
}

function getNameFromLine ( $line ) {
    if ( preg_match("/.*-(.*)\s\d+/", $line, $array )) {
        return str_replace('_', ' ', $array[1]);
    }
}

function getIDFromLine ( $line ) {
    if ( preg_match("/^(\d{1,3})-/", $line, $array)) {
        return $array[1];
    }
    return -1;
}


class ProductFacade {
    private $products = array();

    function __construct($file)
    {
        $this->file=$file;
        $this->compile();
    }

    private function compile() {
        $lines = getProductFileLines( $this->file );
        foreach ($lines as $line) {
            $id = getIDFromLine ($line);
            $name = getNameFromLine($line);
            $this->products[$id] = getProductObjectFromId( $id, $name );
        }
    }

    function getProducts() {
        return $this->products;
    }

    function getProduct( $id ) {
        if( isset($this->products[$id])) {
            return $this->products[$id];
        }
        return null;
    }
}

$facade = new ProductFacade(__DIR__.'Facade.php');
$facade->getProduct(23);