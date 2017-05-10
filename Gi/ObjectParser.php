<?php


class Scanner
{
//    Типы токенов
    const WORD = 1;
    const QUOTE = 2;
    const APOS = 3;
    const WHITESPACE = 6;
    const EOL = 8;
    const CHAR = 9;
    const EOF = 0;
    const SOF = -1;

    protected $line_no = 1;
    protected $char_no = 0;
    protected $token = null;
    protected $token_type = -1;

    //Доступ к исходным данным осуществляетсся через
    //класс Reader. Результирующие данные сохраняются
    //в представленном контексте.
    function __construct(Reader $r, Context $context)
    {
        $this->r = $r;
        $this->context = $context;
    }

    function getContext()
    {
        return $this->context;
    }

    // Пропускает все пробельные символы.
    function eatWhiteSpace()
    {
        $ret = 0;
        if ($this->token_type != self::WHITESPACE &&
            $this->token_type != self::EOL
        ) {
            return $ret;
        }
        while ($this->nextToken() == self::WHITESPACE ||
            $this->token_type == self::EOL) {
            $ret++;
        }
        return $ret;
    }

    //Возвращает строковое представление токена.
    //Возвращается либо текущий токен, либо тот,
    //который указан в аргументе $int.
    function getTypeString($int = -1)
    {
        if ( $int < 0 ) { $int = $this->tokenType();}
        if ( $int < 0 ) { return null; }
        $resolve = array (
            self::WORD  => 'WORD',
            self::QUOTE => 'QUOTE',
            self::APOS =>  'APOS',
            self::WHITESPACE => 'WHITESPACE',
            self::EOL => 'EOL',
            self::CHAR => 'CHAR',
            self::EOF => 'EOF'
        );
        return $resolve[$int];
    }

    //Возвращет текущий тип токена, представленный
    //целым числом.
    function tokenType()
    {
        return $this->token_type;
    }

    //Возвращает содержимое текущего токена.
    function token()
    {
        return $this->token;
    }

    //Возвращает истинное значение, если текущий токен
    //имеет тип WORD.
    function isWord()
    {
        return ( $this->token_type == self::WORD );
    }

    //Возвращает истинное значение, если текущий токен
    //имеет тип WORD.
    function isQuote()
    {
        return ($this->token_type == self::APOS ||
            $this->token_type == self::QUOTE);
    }

    //Возвращает номер текущей строки в исходном файле
    function line_no()
    {
        return $this->line_no;
    }

    //Возвращает номер текущего символа в исходном файле.
    function char_no()
    {
        return $this->char_no;
    }

    //Клонирует этот объект
    function __clone()
    {
        $this->r = clone($this->r);
    }

    // Перемещается к следующему токену в исходном файле.
    // Устанавливает текущий токен и отслеживает номер строки
    // и номер символа.
    function nextToken()
    {
        $this->token = null;
//        $type = null;
        while (!is_bool($char = $this->getChar())){
            if($this->isEolChar($char)){
                $this->token = $this->manageEolChars($char);
                $this->line_no++;
                $this->char_no = 0;
                $type = self::EOL;
                return ( $this->token_type = self::EOL );
            } else if ($this->isWordChar($char)) {
                $this->token = $this->eatWordChars( $char );
                $type = self::WORD;
            } else if ( $this->isSpaceChar( $char ) ){
                $this->token = $char;
                $type = self::WHITESPACE;
            } else if ($char == "'") {
                $this->token = $char;
                $type = self::APOS;
            } else if ($char == '"') {
                $this->token = $char;
                $type = self::QUOTE;
            } else {
                $type = self::CHAR;
                $this->token = $char;
            }

            $this->char_no += strlen($this->token());
            return ($this->token_type = $type);
        }
        return ($this->token_type = self::EOF);
    }

    // Возвращает массив, содержащий тип токена и содержимое токена для следующего токена
    function peekToken()
    {
        $state = $this->getState();
        $type = $this->nextToken();
        $token = $this->token();
        $this->setState($state);
        return array($type, $token);
    }

    // Получает объект ScannerState, содержащий текущую позицию
    // анализатора в исходный строке и данные около текущего токена
    function getState()
    {
        $state = new ScannerState();
        $state->line_no = $this->line_no;
        $state->char_no = $this->char_no;
        $state->token = $this->token;
        $state->token_type = $this->token_type;
        $state->r = clone($this->r);
        $state->context = clone($this->context);
        return $state;
    }

    //Использует объект ScannerState для восстановления состояния сканера
    function setState( ScannerState $state )
    {
        $this->line_no = $state->line_no;
        $this->char_no = $state->char_no;
        $this->token = $state->token;
        $this->token_type = $state->token_type;
        $this->r = $state->r;
        $this->context = $state->context;
        return;
    }

    //Возвращает следующий символ из исходного файла
    private function getChar()
    {
        return $this->r->getChar();
    }

    //Возвратить все символы до конца слова
    private function eatWordChars( $char )
    {
        $val = $char;
        while ($this->isWordChar($char = $this->getChar())) {
            $val .= $char;
        }
        if ($char) {
            $this -> pushBackChar();
        }
        return $val;
    }

    //Возвратить все пробельные символы
    private function eatSpaceChars( $char )
    {
        $val = $char;
        while ($this->isSpaceChar($char = $this->getChar())){
            $val .= $char;
        }
        $this->pushBackChar();
        return $val;
    }

    //Отодвинуться на один символ в исходном файле
    function pushBackChar()
    {
        $this->r->pushBackChar();
        return;
    }

    //Проверяет, не является ли аргумент символом слова
    private function isWordChar($char)
    {
        return preg_match("/[A-Za-z0-9_\-]/", $char);
    }

    //Проверяет, не является ли аргумент пробельным символом
    private function isSpaceChar($char)
    {
        return preg_match("/\t| /", $char );
    }

    //Проверяет, не является ли аргумент символом конца строки
    private function isEolChar($char)
    {
        return preg_match("/\n|\r/", $char);
    }

    //Обрабатывает конец строки: \n, \r или \r\n
    private function manageEolChars($char)
    {
        if($char == "\r") {
            $next_char = $this->getChar();
            if ( $next_char == "\n" ) {
                return "{$char}{$next_char}";
            } else {
                $this->pushBackChar();
            }
        }
        return $char;
    }

    function getPos() {
        return $this->r->getPos();
    }
}

class ScannerState {
    public $line_no;
    public $char_no;
    public $token;
    public $token_type;
    public $r;
    public $context;
}

class Context {

    public $resultStack = array();

    function pushResult( $mixed ) {
        array_push( $this->resultStack, $mixed );
    }

    function popResult( ) {
        return array_pop( $this->resultStack );
    }

    function resultCount() {
        return count( $this->resultStack );
    }
    function peekResult( ) {
        if ( empty( $this->resultStack ) ) {
            throw new \Exception( "empty resultStack" );
        }
        return $this->resultStack[ count( $this->resultStack ) -1 ];
    }
}


interface Reader {

    function getChar();

    function getPos();
    function pushBackChar();
}

class StringReader implements Reader {
    private $in;
    private $pos;

    function __construct( $in ) {
        $this->in = $in;
        $this->pos = 0;
    }

    function getChar() {
        if ( $this->pos >= strlen( $this->in ) ) {
            return false;
        }
        $char = substr( $this->in, $this->pos, 1 );
        $this->pos++;
        return $char;
    }

    function getPos() {
        return $this->pos;
    }

    function pushBackChar() {
        $this->pos--;
    }

    function string() {
        return $this->in;
    }
}


//----------------------------------------------------------------------------------------


abstract class Parser {

    const GIP_RESPECTSPACE = 1;
    protected $respectSpace = false;
    protected static $debug = false;
    protected
        $discard = false;
    protected $name;
    private static $count=0;

    function __construct( $name=null, $options=null ) {
        if ( is_null( $name ) ) {
            self::$count++;
            $this->name = get_class( $this )." (".self::$count.")";
        } else {
            $this->name = $name;
        }
        if ( is_array( $options ) ) {
            if ( isset( $options[self::GIP_RESPECTSPACE] ) ) {
                $this->respectSpace=true;
            }
        }
    }

    protected function next( Scanner $scanner ) {
        $scanner->nextToken();
        if ( ! $this->respectSpace ) {
            $scanner->eatWhiteSpace();
        }
    }

    function spaceSignificant( $bool ) {
        $this->respectSpace = $bool;
    }

    static function setDebug( $bool ) {
        self::$debug = $bool;
    }

    function setHandler( Handler $handler ) {
        $this->handler = $handler;
    }

    final function scan( Scanner $scanner ) {
        if ( $scanner->tokenType() == Scanner::SOF ) {
            $scanner->nextToken();
        }
        $ret = $this->doScan( $scanner );
        if ( $ret && ! $this->discard && $this->term() ) {
            $this->push( $scanner );
        }
        if ( $ret ) {
            $this->invokeHandler( $scanner );
        }

        if ( $this->term() && $ret ) {
            $this->next( $scanner );
        }
        $this->report("::scan returning $ret");
        return $ret;
    }

    function discard() {
        $this->discard = true;
    }

    abstract function trigger( Scanner $scanner );

    function term() {
        return true;
    }

// private/protected

    protected function invokeHandler(
        Scanner $scanner ) {
        if ( ! empty( $this->handler ) ) {
            $this->report( "calling handler: ".get_class( $this->handler ) );
            $this->handler->handleMatch( $this, $scanner );
        }
    }

    protected function report( $msg ) {
        if ( self::$debug ) {
            print "<{$this->name}> ".get_class( $this ).": $msg\n";
        }
    }

    protected function push( Scanner $scanner ) {
        $context = $scanner->getContext();
        $context->pushResult( $scanner->token() );
    }

    abstract protected function doScan( Scanner $scan );
}

class CharacterParse extends Parser {
    private $char;

    function __construct( $char, $name=null, $options=null ) {
        parent::__construct( $name, $options );
        $this->char = $char;
    }

    function trigger( Scanner $scanner ) {
        return ( $scanner->token() == $this->char );
    }

    protected function doScan( Scanner $scanner ) {
        return ( $this->trigger( $scanner ) );
    }
}

// This abstract class holds subparsers
abstract class CollectionParse extends Parser {
    protected $parsers = array();

    function add( Parser $p ) {
        if ( is_null( $p ) ) {
            throw new Exception( "argument is null" );
        }
        $this->parsers[]= $p;
        return $p;
    }

    function term() {
        return false;
    }
}

class SequenceParse extends CollectionParse {

    function trigger( Scanner $scanner ) {
        if ( empty( $this->parsers ) ) {
            return false;
        }
        return $this->parsers[0]->trigger( $scanner );
    }

    protected function doScan( Scanner $scanner ) {
        $start_state = $scanner->getState();
        foreach( $this->parsers as $parser ) {
            if ( ! ( $parser->trigger( $scanner ) &&
                $scan=$parser->scan( $scanner )) ) {
                $scanner->setState( $start_state );
                return false;
            }
        }
        return true;
    }
}

// This matches if one or more subparsers match
class RepetitionParse extends CollectionParse {
    private $min;
    private $max;

    function __construct( $min=0, $max=0, $name=null, $options=null ) {
        parent::__construct( $name, $options );
        if ( $max < $min && $max > 0 ) {
            throw new Exception(
                "maximum ( $max ) larger than minimum ( $min )");
        }
        $this->min = $min;
        $this->max = $max;
    }

    function trigger( Scanner $scanner ) {
        return true;
    }

    protected function doScan( Scanner $scanner ) {
        $start_state = $scanner->getState();
        if ( empty( $this->parsers ) ) {
            return true;
        }
        $parser = $this->parsers[0];
        $count = 0;

        while ( true ) {
            if ( $this->max > 0 && $count >= $this->max ) {
                return true;
            }

            if ( !$parser->trigger( $scanner ) ) {
                if ( $this->min == 0 || $count >= $this->min ) {
                    return true;
                } else {
                    $scanner->setState( $start_state );
                    return false;
                }
            }
            if ( !$parser->scan( $scanner ) ) {
                if ( $this->min == 0 || $count >= $this->min ) {
                    return true;
                } else {
                    $scanner->setState( $start_state );
                    return false;
                }
            }
            $count++;
        }
        return true;
    }
}

// This matches if one or other of two subparsers match
class AlternationParse extends CollectionParse {

    function trigger( Scanner $scanner ) {
        foreach ( $this->parsers as $parser ) {
            if ( $parser->trigger( $scanner ) ) {
                return true;
            }
        }
        return false;
    }
    protected function doScan( Scanner $scanner ) {
        $type = $scanner->tokenType();
        foreach ( $this->parsers as $parser ) {
            $start_state = $scanner->getState();
            if ( $type == $parser->trigger( $scanner ) &&
                $parser->scan( $scanner ) ) {
                return true;
            }
        }
        $scanner->setState( $start_state );
        return false;
    }
}

// this terminal parser matches a string literal
class StringLiteralParse extends Parser {

    function trigger( Scanner $scanner ) {
        return ( $scanner->tokenType() == Scanner::APOS ||
            $scanner->tokenType() == Scanner::QUOTE );
    }

    protected function push( Scanner $scanner ) {
        return;
    }

    protected function doScan( Scanner $scanner ) {
        $quotechar = $scanner->tokenType();
        $ret = false;
        $string = "";
        while ( $token = $scanner->nextToken() ) {
            if ( $token == $quotechar ) {
                $ret = true;
                break;
            }
            $string .= $scanner->token();
        }

        if ( $string && ! $this->discard ) {
            $scanner->getContext()->pushResult( $string );
        }

        return $ret;
    }
}

    // this terminal parser matches a word token
class WordParse extends Parser {

    function __construct( $word=null, $name=null, $options=null ) {
        parent::__construct( $name, $options );
        $this->word = $word;
    }

    function trigger( \Scanner $scanner ) {
        if ( $scanner->tokenType() != \Scanner::WORD ) {
            return false;
        }
        if ( is_null( $this->word ) ) {
            return true;
        }
        return ( $this->word == $scanner->token() );
    }

    protected function doScan( \Scanner $scanner ) {
        $ret = ( $this->trigger( $scanner ) );
        return $ret;
    }
}



/**
 * Class Expression
 * @package Interpreter
 * Методу interpret передается объект типа InterpreterContext
 */
abstract class Expression {

    private static $keyCount = 0;
    private $key;

    abstract function interpret( \InterpreterContext $context); //

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

    function __construct($value)
    {
        $this->value = $value;
    }

    function interpret(\InterpreterContext $context)
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
    function replace(\Expression $exp, $value)
    {
        $this->expressionStore[$exp->getKey()] = $value;
    }

    function lookup(\Expression $exp)
    {
        return $this->expressionStore[$exp->getKey()];
    }
}

//$context = new InterpreterContext();
//$literal = new LiteralExpression('Four');
//$literal -> interpret( $context );
//print $context->lookup($literal) . "\n";

class VariableExpression extends \Expression {

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
    public function interpret(\InterpreterContext $context) {
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



class MarkParse {
    private $expression;
    private $operand;
    private $interpreter;
    private $context;

    function __construct( $statement ) {
        $this->compile( $statement );
    }

    function evaluate( $input ) {
        $icontext = new InterpreterContext();
        $prefab = new VariableExpression('input', $input );

        // add the input variable to Context
//        $prefab–>interpret( $icontext );

        $this->interpreter = new BooleanOrExpression(
            new EqualsExpression( $prefab, new LiteralExpression( 'five' ) ),
            new EqualsExpression( $prefab, new LiteralExpression( '5'))
        );

        $this->interpreter->interpret( $icontext );
        $result = $icontext->lookup( $this->interpreter );
        return $result;
    }

    function compile( $statement_str ) {
// build parse tree
        $context = new Context();
        $scanner = new \Scanner(
            new \StringReader($statement_str), $context );
        $statement = $this->expression();
        $scanresult = $statement->scan( $scanner );

        if ( ! $scanresult || $scanner->tokenType() != \Scanner::EOF ) {
            $msg = "";
            $msg .= " line: {$scanner->line_no()} ";
            $msg .= " char: {$scanner->char_no()}";
            $msg .= " token: {$scanner->token()}\n";
            throw new Exception( $msg );
        }

        $this->interpreter = $scanner->getContext()->popResult();
    }

    function expression() {
        if ( ! isset( $this->expression ) ) {
            $this->expression = new \SequenceParse();
            $this->expression->add( $this->operand() );
            $bools = new \RepetitionParse( );
            $whichbool = new \AlternationParse();
            $whichbool->add( $this->orExpr() );
            $whichbool->add( $this->andExpr() );
            $bools->add( $whichbool );
            $this->expression->add( $bools );
        }
        return $this->expression;
    }

    function orExpr() {
        $or = new \SequenceParse( );
        $or->add( new \WordParse('or') )->discard();
        $or->add( $this->operand() );
        $or->setHandler( new \BooleanOrHandler() );
        return $or;
    }

    function andExpr() {
        $and = new SequenceParse();
        $and->add( new \WordParse('and') )->discard();
        $and->add( $this->operand() );
        $and->setHandler( new \BooleanAndHandler() );
        return $and;
    }

    function operand() {
        if ( ! isset( $this->operand ) ) {
            $this->operand = new \SequenceParse( );
            $comp = new \AlternationParse( );
            $exp = new \SequenceParse( );
            $exp->add( new \CharacterParse( '(' ))->discard();
            $exp->add( $this->expression() );
            $exp->add( new \CharacterParse( ')' ))->discard();
            $comp->add( $exp );
            $comp->add( new \StringLiteralParse() )
                ->setHandler( new \StringLiteralHandler() );
            $comp->add( $this->variable() );
            $this->operand->add( $comp );
            $this->operand->add( new \RepetitionParse( ) )
                ->add($this->eqExpr());
        }
        return $this->operand;
    }

    function eqExpr() {
        $equals = new \SequenceParse();
        $equals->add( new \WordParse('equals') )->discard();
        $equals->add( $this->operand() );
        $equals->setHandler( new \EqualsHandler() );
        return $equals;
    }

    function variable() {
        $variable = new \SequenceParse();
        $variable->add( new \CharacterParse( '$' ))->discard();
        $variable->add( new \WordParse());
        $variable->setHandler( new \VariableHandler() );
        return $variable;
    }
}

abstract class OperatorExpression extends \Expression {
    protected $l_op;
    protected $r_op;

    function __construct( Expression $l_op, Expression $r_op )
    {
        $this->l_op = $l_op;
        $this->r_op = $r_op;
    }

    function interpret(\InterpreterContext $context)
    {
        $this->l_op->interpret( $context );
        $this->r_op->interpret( $context );
        $result_l = $context->lookup( $this->l_op );
        $result_r = $context->lookup( $this->r_op );
        $this->doInterpret( $context, $result_l, $result_r );
    }

    protected abstract function doInterpret( \InterpreterContext $context, $result_l, $result_r );
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
    protected function doInterpret(\InterpreterContext $context, $result_l, $result_r)
    {
        $context -> replace( $this, $result_l == $result_r );
    }
}

class BooleanOrExpression extends OperatorExpression {
    protected function doInterpret(\InterpreterContext $context, $result_l, $result_r)
    {
        $context -> replace( $this, $result_l || $result_r );
    }
}

class BooleanAndExpression extends OperatorExpression {
    protected function doInterpret(\InterpreterContext $context, $result_l, $result_r)
    {
        $context -> replace( $this, $result_l && $result_r );
    }
}

interface Handler {
    function handleMatch(
        Parser $parser,
        Scanner $scanner );
}

class VariableHandler implements \Handler {
    function handleMatch( Parser $parser, Scanner $scanner ) {
        $varname = $scanner->getContext()->popResult();
        $scanner->getContext()->pushResult( new VariableExpression( $varname ) );
    }
}

class StringLiteralHandler implements \Handler {
    function handleMatch( \Parser $parser, \Scanner $scanner ) {
        $value = $scanner->getContext()->popResult();
        $scanner->getContext()->pushResult( new \LiteralExpression( $value ) );
    }
}

class EqualsHandler implements \Handler {
    function handleMatch( \Parser $parser, \Scanner $scanner ) {
        $comp1 = $scanner->getContext()->popResult();
        $comp2 = $scanner->getContext()->popResult();
        $scanner->getContext()->pushResult(
            new \EqualsExpression( $comp1, $comp2 ) );
    }
}

class BooleanOrHandler implements \Handler {
    function handleMatch( \Parser $parser, \Scanner $scanner ) {
        $comp1 = $scanner->getContext()->popResult();
        $comp2 = $scanner->getContext()->popResult();
        $scanner->getContext()->pushResult(
            new BooleanOrExpression( $comp1, $comp2 ) );
    }
}

class BooleanAndHandler implements \Handler {
    function handleMatch( \Parser $parser, \Scanner $scanner ) {
        $comp1 = $scanner->getContext()->popResult();
        $comp2 = $scanner->getContext()->popResult();
        $scanner->getContext()->pushResult(
            new BooleanAndExpression( $comp1, $comp2 ) );
    }
}

$input = 'five';
$statement = "(\$input equals 'five')";

$engine = new MarkParse( $statement );

$result = $engine->evaluate( $input );

print "input: $input evaluating: $statement\n";
if( $result ) {
    print "true!\n";
} else {
    print "false!\n";
}


