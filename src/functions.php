<?php

namespace SiteTool;

function ampRunAllTheThings($workers, $setupCallable)
{
    $coroutines = [];

    foreach ($workers as $worker) {
        $coroutines[] = \Amp\coroutine($worker);
    }

    \Amp\Loop::run(function () use ($coroutines, $setupCallable) {
        $setupCallable();
        $runningThings = [];

        foreach ($coroutines as $coroutine) {
            $runningThings[] = $coroutine();
        }

        yield $runningThings;

        // Because we automatically exit the event loop, this line will never be reached.
        // If some end condition is known, the while (true) in the workers can be replaced
        // and then this line will be reached once finished.
        print "Finished processing." . PHP_EOL; // <-- Never reached
    });
}
