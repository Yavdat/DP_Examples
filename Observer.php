<?php
/**
 * Observer
 * Наблюдатель - поведенческий шаблон проектирования. Создает миханизм у класса, который позволяет получать экземпляру
 * объекта этого класса оповещения от других объектов об изменении их состояния, тем самым наблюдая за ними.
 */

//Интерфейс, с помощью которого наблюдаетель получает оповещение;

/**
 * Шаблон применяется в тех случаях, когда система обладает следующими свойствами:
 * -cуществует, как минимум, один объект, рассылающий сообщения;
 * -имеется не менее одного получателя сообщений, причем их количество и состав могут изменяться во время работы
 * приложения;
 * -нет надобности очень сильно связывать взаимодействующие объекты, что полезно для повторного использования;
 */

interface Observer
{
    function notify($obj);
}


class ExchangeRate
{
    static private $instance = NULL;
    private $observers = array();
    private $exchange_rate;

    private function __construct()
    {}

    private function __clone()
    {}

    static public function getInstance()
    {
        if(self::$instance == NULL)
        {
            self::$instance = new ExchangeRate();
        }
        return self::$instance;
    }

    public function getExchangeRate()
    {
        return $this->exchange_rate;
    }

    public function setExchangeRate($new_rate)
    {
        $this->exchange_rate = $new_rate;
        $this->notifyObservers();
    }

    public function registerObserver(Observer $obj)
    {
        $this->observers[] = $obj;
    }

    function notifyObservers()
    {
        foreach($this->observers as $obj)
        {
            $obj->notify($this);
        }
    }
}

class ProductItem implements Observer
{

    public function __construct()
    {
        ExchangeRate::getInstance()->registerObserver($this);
    }

    public function notify($obj)
    {
        if($obj instanceof ExchangeRate)
        {
            // Update exchange rate data
            print "Received update!\n";
        }
    }
}

$product1 = new ProductItem();
$product2 = new ProductItem();

ExchangeRate::getInstance()->setExchangeRate(4.5);

/*

interface Observable {
    function attach(Observer $observer);
    function detach(Observer $observer);
    function notify();
}

// ... Класс Login
class Login implements Observable {

    private $observers=array();
    private $status = array();

    const LOGIN_USER_UNKNOWN = 1;
    const LOGIN_WRONG_PASS = 2;
    const LOGIN_ACCESS = 3;

//    function __construct()
//    {
//        $this->observers = array();
//    }

    function attach(Observer $observer) {
        $this->observers[] = $observer;
    }

    function detach(Observer $observer)
    {
        $this->observers = array_filter( $this->observers,
        function ($a) use ($observer) {
            return (!($a === $observer));
        });
    }

    function notify()
    {
        foreach ($this->observers as $obs) {
            $obs->update($this);
        }
    }


    function handleLogin( $user,  $ip)
    {
        $isvalid = false;
        switch (rand(1,3))
        {
            case 1:
                $this->setStatus(self::LOGIN_ACCESS, $user, $ip);
                $isvalid = true;
                break;
            case 2:
                $this->setStatus(self::LOGIN_WRONG_PASS, $user, $ip);
                $isvalid = false;
                break;
            case 3:
                $this->setStatus(self::LOGIN_USER_UNKNOWN, $user, $ip);
                $isvalid = false;
                break;
        }
        $this->notify();
        return $isvalid;
    }

    private function setStatus($status, $user, $ip) {
        $this->status = array($status, $user, $ip);
    }


    function getStatus() {
        return $this->status;
    }
}

interface Observer {
    function update( Observable $observable );
}


class SecurityMonitor implements Observer {
    function update( Observable $observable ) {
        $status = $observable->getStatus();
        if($status[0] == Login::LOGIN_WRONG_PASS ) {
            // Отправим почту системному администратору
            print __CLASS__.":\tОтправка почты системному администратору \n";
        }
    }
}

$login = new Login();

$login->attach(new SecurityMonitor());





abstract class LoginObserver implements Observer {
    private $login;

    function __construct(Login $login)
    {
        $this->login = $login;
        $this->login->attach($this);
    }

    function update(Observable $observable)
    {
        if ($observable === $this->login) {
            $this->doUpdate($observable);
        }
    }

    abstract function doUpdate(Login $login);
}

class SecurityMonitor extends LoginObserver {

    function doUpdate(Login $login)
    {
        $status = $login->getStatus();
        if($status[0] == Login::LOGIN_WRONG_PASS){
            //Отправим почту системному администратору
            print __CLASS__.":\tОтправка почты системному администратору \n";
        }
    }
}

class GeneralLogger extends LoginObserver {
    function doUpdate(Login $login)
    {
        $status = $login->getStatus();
        //зарегистрируем подключение в журнале
        print __CLASS__.":\tРегистрация в системном журнале\n";
    }
}

class PartnershipTool extends LoginObserver {
    function doUpdate(Login $login) {
        $status = $login->getStatus();
        //Проверим IP-адрес
        //Отправим cookie-файл, если адрес соответствует списку
        print __CLASS__.
            ":\tОтправка cookie-файла, если адрес соответствует списку\n";
    }
}

$login = new Login();
new SecurityMonitor($login);
new GeneralLogger($login);
new PartnershipTool($login);

*/

/*
class Login implements SplSubject {
    private $storage;
    //...
    function __construct()
    {
        $this->storage = new SplObjectStorage();
    }

    function attach(SplObserver $observer)
    {
        $this->storage->attach($observer);
    }

    function detach(SplObserver $observer)
    {
        $this->storage->detach($observer);
    }

    function notify()
    {
        foreach ($this->storage as $obj) {
            $obj->update($this);
        }
    }
    //...
}

abstract class LoginObserver implements SplObserver {
    private $login;

    function __construct(Login $login)
    {
        $this->login = $login;
        $login->attach($this);
    }

    function update(SplSubject $subject)
    {
        if($subject === $this->login) {
            $this->doUpdate($subject);
        }
    }

    abstract function doUpdate(Login $login);
}

*/




