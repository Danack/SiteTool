<?php

namespace SiteTool;

class GraphVizTest
{
    /** @var \Alom\Graphviz\Digraph */
    private $graph;
    
    
    private $switchParams = [
        'shape' => 'circle',
        //'fixedsize' => true, 
        //'width' => 0.9
    ];
    
    
    private $eventParams = [
        'shape' => 'box'
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
        $this->graph->node($eventName, $this->eventParams);
        $switchParams = $this->switchParams;
        $switchParams['label'] = "<". wordwrap($eventSource, 15, "<br/>") . ">";
//        $switchParams['label'] = wordwrap($eventSource, 15, "'n");
        $switchParams['_escaped'] = false;
        
        $this->graph->node($eventSource, $switchParams);
        $this->graph->edge([$eventSource, $eventName]);
    }
    
    public function addEventListener($eventName, $eventListener)
    {
        $this->graph->node($eventName, $this->eventParams);
        $this->graph->node($eventListener, $this->switchParams);
        $this->graph->edge([$eventName, $eventListener]);
    }

    public function finalize()
    {
        $this->graph->attr(
            'graph', 
            [
                'label' => 'Switches are circles. Events are boxes.',
                'fontsize' => 12,
                //'splines' => 'polyline',
                //'overlap' => 'voronoi'
                //'mindist' => 1.5,
                'overlap' => 'scale',
                //'packMode' => 'clust',
                //'smoothType' => 'spring'
                //'ranksep' => "5",
//                'sep' => '0.1',
//                'esep' => '+5'
                
            ]
        );
        $this->graph->attr(
            'edge', 
            [
                'splines' => 'curved'
            ]
        );

        $data = $this->graph->render();
        file_put_contents(__DIR__ . "/../../graph.dot", $data);
        
        $commmands = [
//            'dot',
//            'neato',
//            'twopi',
            'circo',
//            'fdp',
//            'sfdp',
//            //'patchwork',
//            'osage',
        ];
        
        foreach ($commmands as $commmand) {
            exec("/usr/bin/$commmand -Tpng -ograph_output_$commmand.png graph.dot");
        }
        
        //   exec("/usr/bin/circo -Tpng -ograph_output_circo.png graph.dot");
    }
}
