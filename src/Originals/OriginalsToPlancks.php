<?php

namespace PlanckId\Originals;

use PlanckId\Flo\InvokableFloComponent;
use PlanckId\Planck\OriginalAndPlanckMap;
use PlanckId\Planck\Plancks;

/**
 * was MakeNewIdentities
 *
 * This takes in `Original`(s) and adds them to the OriginalAndPlanckMap, mapping them with Planck
 */
class OriginalsToPlancks extends InvokableFloComponent {
    protected $ports = array(['in', 'in', []], ['out', 'error'], 'out');

    public function __construct() {
        $this->addPorts($this->ports);
        $this->inPorts['in']->on('data', [$this, '__invoke']);
        $this->inPorts['in']->on('disconnect', [$this, 'outs']);
    }

    public function outs() {
        lineOut(__METHOD__);
        $this->outPorts['out']->disconnect();
    }
 
    /**
     * @example 
     *    pass in the originals
     *    
     * @param  array<string>|string $originalArray
     * @return void
     */
    public function __invoke($originalArray) {
        lineOut(__METHOD__);

        foreach ((array)$originalArray as $original)
            $this->originalToPlanck($original);

        $this->outPorts['out']->send(OriginalAndPlanckMap::$map);
    }

    /**
     * @param  string $original
     * @return void
     */
    function originalToPlanck($original) {
        if (!OriginalAndPlanckMap::has($original)) {
            $new = Plancks::next();
            while (OriginalAndPlanckMap::hasPlanck($new)) 
                $new = Plancks::next();

            OriginalAndPlanckMap::set($original, $new);
            OriginalAndPlanckMap::sortByLength();
        }
    }
}