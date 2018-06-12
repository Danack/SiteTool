<?php

namespace SiteTool;

function normalizeEventName($eventName)
{
    $lastSlashPos = strrpos($eventName, '\\');
    if ($lastSlashPos !== false) {
        return substr($eventName, $lastSlashPos + 1);
    }
    return $eventName;
}

class GraphVizBuilder
{
    /** @var \Alom\Graphviz\Digraph */
    private $graph;

    private $switchParams = [
        'shape' => 'circle',
        //'shape' => 'box',
        //'fixedsize' => true,
        //'width' => 0.9
        'fontsize' => 28,
        //'margin' => '0.5,0.5',
        //'fixedsize' => 'shape'
    ];

    private $eventParams = [
        'shape' => 'box',
        'fontsize' => 28,
        //'margin' => '0.5,0.5',
        'margin' => '0.2,0.1',
        //'fixedsize' => 'shape'
    ];
    
    private $edgeParams = [
        'penwidth' => '1.5',
    ];
    
    public function __construct()
    {
        $this->graph = new \Alom\Graphviz\Digraph('G');
    }

    public function test()
    {
        $this->addEventTrigger('FOUND_URL', 'scanner');
        $this->addEventListener('FOUND_URL', 'url fetcher');

        $this->finalize();
    }

    public function addEventTrigger($eventName, $eventSource)
    {
        $eventName = normalizeEventName($eventName);
        $this->graph->node($eventName, $this->eventParams);
        $switchParams = $this->switchParams;
        $switchParams['label'] = "<". wordwrap($eventSource, 15, "<br/>") . ">";
        $switchParams['_escaped'] = false;
        
        $this->graph->node($eventSource, $switchParams);
        $this->graph->edge([$eventSource, $eventName], $this->edgeParams);
    }
    
    public function addEventListener($eventName, $eventListener)
    {
        $eventName = normalizeEventName($eventName);
        $this->graph->node($eventName, $this->eventParams);
        $this->graph->node($eventListener, $this->switchParams);
        $this->graph->edge([$eventName, $eventListener], $this->edgeParams);
    }

    public function finalize()
    {
        $this->graph->attr(
            'graph',
            [
                'center' => true,
                'fontsize' => 28,
                'label' => 'Boxes are events, circles are event processors.',
                'margin' => "0.5,0.5",
                'mindist' => 1.0,
                'packMode' => 'node',
                'nodesep' => 0.9,
                'overlap' => 'voronoi',
                'ranksep' => 0.1,
                'smoothing' => "triangle",
                'splines' => 'true',
                //   Values are "none", "avg_dist", "graph_dist", "power_dist", "rng", "spring" and "triangle".
            ]
        );
        $this->graph->attr(
            'edge',
            [
                'splines' => 'curved',
                'weight' => 45
            ]
        );

        $this->graph->attr(
            'node',
            [
                'splines' => 'curved',
            ]
        );

        $data = $this->graph->render();
        file_put_contents(__DIR__ . "/../../graph.dot", $data);
        
        $commmands = [
//            'dot',
//            'neato',
//            'twopi',
            'circo',
            //'fdp',
//            'sfdp',
//            //'patchwork',
//            'osage',
        ];
        
        foreach ($commmands as $commmand) {
            exec("/usr/bin/$commmand -Tpng -ograph_output_$commmand.png graph.dot");
        }
    }
}
