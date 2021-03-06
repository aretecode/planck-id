<?php 

namespace PlanckId\Regex;

use InvalidArgumentException;
use PlanckId\Flo\FloComponent;
use Illuminate\Support\Arr;
use PlanckId\Emitter;

/**
 * could also pass a regex in as an inPort
 */
class RegexInOut extends FloComponent
{   
    protected $regexPiece = "";
    protected $regex = "";
    protected $ports = array(
        ['out', 'error'], 
        ['in', 'regexpiece'],
        'in',   
        'out',  
    );
     
    public function __construct() {
        $this->addPorts($this->ports);
        $this->onIn('in', 'data', 'outs');
        $this->onIn('regexpiece', 'data', 'setRegexPiece');
    }

    /**
     * @example: 
     *     - $regexPiece = "\n"; $regex = '/(\{'.$regexPiece.')/'; 
     * 
     * regex pieces can be used inside regexes  
     * @param  string $data
     * @return void
     */
    public function setRegexPiece($data) {
        $this->regexPiece = $data;
    }

    /**
     * @uses   $this->regex
     * @param  mixed $data
     * @return array matches
     */
    protected function get($data) {
        $dataExtended = $data;
        if (is_array($data)) 
            $dataExtended = implode(" ", $data);

        $matches = pregMatchAll($dataExtended, $this->regex);
        if (is_array($matches)) 
            $matches = Arr::flatten($matches);

        if (is_array($matches) && count($matches) === 0 && $this->outPorts['error']->isAttached())
            $this->outPorts['error']->send($matches);

        Emitter::emit('regex.inout', $matches, static::class);

        return $matches;
    }

    /**
     * @param string $data 
     * @out array<string|array<string>> matches
     * @return void
     */
    public function outs($data) {
        lineOut(__CLASS__ . ".> " .static::class . " " . __METHOD__);
        $dataOut = $this->get($data);

        lineOut($dataOut);

        $this->sendThenDisconnect('out', $dataOut);
    }
}   