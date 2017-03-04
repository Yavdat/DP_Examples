<?php
namespace Decorator2;

abstract class AbstractComponent
{
    abstract public function operation();
}

class ConcreteComponent extends AbstractComponent
{
    public function operation()
    {}
}

abstract class AbstractDecorator extends AbstractComponent
{
    protected $component;

    public function __construct(AbstractComponent $component)
    {
        $this->component = $component;
    }
}

class ConcreteDecorator extends AbstractDecorator
{
    public function operation()
    {
        //extention code ...
        $this->component->operation();
        //extention code ...
    }
}

$decoratedComponent = new ConcreteDecorator(
    new ConcreteComponent()
);

$decoratedComponent -> operation();

///-------Code by Zanstra
class RequestHelper{};

abstract class ProcessRequest {
    abstract function process ( RequestHelper $req );
}

class MainProcess extends ProcessRequest {
    function process(RequestHelper $req)
    {
        print __CLASS__. ": process is running \n";
    }
}

abstract class DecorateProcess extends ProcessRequest {
    protected $processRequest;

    function __construct( ProcessRequest $pr)
    {
        $this->processRequest = $pr;
    }
}

class LogRequest extends DecorateProcess {
    function process(RequestHelper $req)
    {
        print __CLASS__.": registration query \n";
        $this->processRequest->process($req);
    }
}

class AuthenticateRequest extends DecorateProcess {
    function process(RequestHelper $req)
    {
        print __CLASS__.": authenticating query \n";
        $this->processRequest->process($req);
    }
}

class StructureRequest extends DecorateProcess {
    function process(RequestHelper $req)
    {
        print __CLASS__.": ordering data \n";
        $this->processRequest->process($req);
    }
}

$process = new AuthenticateRequest( new StructureRequest(new LogRequest(new MainProcess())));

$process->process( new RequestHelper());