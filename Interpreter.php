<?php
namespace Interpreter;

/**
 * Class Expression
 * @package Interpreter
 * Методу interpret передается объект типа InterpreterContext
 */
abstract class Expression {

    private static $keyCount = 0;
    private $key;

    abstract function interpret(InterpreterContext $context); //

    /**
     * @return int
     * Возвращает уникальный дескриптор
     *
     */
    function getKey() {
        if (!isset($this->key)) {
            self::$keyCount++;
            $this->key = self::$keyCount;
        }
        
        return $this->key;
    }

}


class LiteralExpression extends Expression
{
    private $value;

    /**
     * LiteralExpression constructor.
     * @param $value
     * Инициализируем value
     */
    function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param InterpreterContext $context
     * Сохраняем контекст в $expressionStore
     */
    function interpret(InterpreterContext $context)
    {
        $context->replace($this, $this->value);
    }
}

/**
 * Class InterpreterContext
 * @package Interpreter
 * Представляет внешний интерфейс для ассоциативного массива
 */
class InterpreterContext
{

    private $expressionStore = [];

    /**
     * @param Expression $exp
     * @param $value
     * Передаются ключ и значение
     */
    function replace(Expression $exp, $value)
    {
        $this->expressionStore[$exp->getKey()] = $value;
    }

    /**
     * @param Expression $exp
     * @return mixed
     *
     * Возвращаем элемент по ключу getKey()
     */
    function lookup(Expression $exp)
    {
        return $this->expressionStore[$exp->getKey()];
    }
}

//$context = new InterpreterContext();
//$literal = new LiteralExpression('Four');
//$literal -> interpret( $context );
//print $context->lookup($literal) . "\n";

class VariableExpression extends Expression {

    private $name;
    private $val;

    public function __construct($name, $val = null) {
        $this->name = $name;
        $this->val = $val;
    }

    /**
     * @param InterpreterContext $context
     * Метод провереяет имеет ли свойство $val ненулевое значение. Если у $val есть некоторое значение,
     * то его значение сохраняется в объекте InterpreterContext.
     * Затем мы устанавливаем для свойства $val значение null. Это делается для того,
     * чтобы повторный вызов метода interpret() не испортил значение переменной с тем же именем,
     * сохраненный в объекте InterpreterContext другим экземпляром объекта VariableExpression
     */
    public function interpret(InterpreterContext $context) {
        if (!is_null($this->val)) {
            $context->replace($this, $this->val);
            $this->val = null;
        }
    }

    function setValue($val) {
        $this->val = $val;
    }

    function getKey() {
        return $this->name;
    }
}

//
//
//$context = new InterpreterContext();
//$myVar = new VariableExpression( 'Input', 'Four');
//$myVar -> interpret($context);
//echo $context->lookup($myVar) . "\n";

//$newVar = new VariableExpression( 'Input' );
//$newVar -> interpret($context);
//echo $context->lookup($newVar) . "\n";

//$myVar->setValue("Five");
//$myVar->interpret($context);
//print $context->lookup($myVar) . "\n";

//print $context->lookup($newVar) . "\n";
//

abstract class OperatorExpression extends Expression {
    protected $l_op;
    protected $r_op;

    function __construct( Expression $l_op, Expression $r_op )
    {
        $this->l_op = $l_op;
        $this->r_op = $r_op;
    }

    function interpret(InterpreterContext $context)
    {
        $this->l_op->interpret( $context );
        $this->r_op->interpret( $context );
        $result_l = $context->lookup( $this->l_op );
        $result_r = $context->lookup( $this->r_op );
        $this->doInterpret( $context, $result_l, $result_r );
    }

    protected abstract function doInterpret( InterpreterContext $context, $result_l, $result_r );
}

class EqualsExpression extends OperatorExpression {

    /**
     * @param InterpreterContext $context
     * @param $result_l
     * @param $result_r
     * Метод doInterpret() представляет собой экземпляр шаблона Template Method.
     * В этом шаблоне в родительском классе и определяется, и вызывается абстрактный метод,
     * реализация которого оставляется дочерним классам. Это может упростить разработку конкретных классов,
     * поскольку совместно используемыми функциями управляет суперкласс,
     * оставляя дочерним классам задачу сконцентрироваться на четких и понятных целях.
     */
    protected function doInterpret(InterpreterContext $context, $result_l, $result_r)
    {
        $context -> replace( $this, $result_l == $result_r );
    }
}

class BooleanOrExpression extends OperatorExpression {
    protected function doInterpret(InterpreterContext $context, $result_l, $result_r)
    {
        $context -> replace( $this, $result_l || $result_r );
    }
}

class BooleanAndExpression extends OperatorExpression {
    protected function doInterpret(InterpreterContext $context, $result_l, $result_r)
    {
        $context -> replace( $this, $result_l && $result_r );
    }
}

$context = new InterpreterContext();
$input = new VariableExpression( 'input' );
$statement = new BooleanOrExpression(
    new EqualsExpression( $input, new LiteralExpression( 'Four' ) ),
    new EqualsExpression( $input, new LiteralExpression( '4'))
);

foreach ( array( "Four", "4", "52" ) as $val ) {
    $input->setValue( $val );
    echo "$val:\n";
    $statement -> interpret( $context );
    if ( $context->lookup( $statement ) ) {
        echo "equal \n\n";
    } else {
        echo "not equal \n\n";
    }
}



