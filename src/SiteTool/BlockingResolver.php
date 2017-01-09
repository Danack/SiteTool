<?php


namespace SiteTool;

use Amp\Dns\Resolver;
use Amp\Success;
use Amp\Dns\Record;
use Amp\Dns\ResolutionException;

class BlockingResolver implements Resolver
{
    /**
     * @see \Amp\Dns\resolve
     */
    public function resolve($name, array $options = [])
    {
        $records = dns_get_record($name, DNS_ANY);
        $result = [];
        foreach ($records as $record) {
            if (isset($record['ip']) == true && isset($record['type']) == true) {
                if ($record['type'] === 'A' || $record['type'] === 'AAAA') {
                    $ttl = null;
                    if (isset($record['ttl']) === true) {
                        $ttl = $record['ttl'];
                    }
                    $result[] = [$record['ip'], $record['type'], $ttl];
                }
            }
        }

        if ($result) {
            return new Success($result);
        }

        throw new ResolutionException("Failed to resolve " . $name);
    
    }

    /**
     * @see \Amp\Dns\query
     */
    public function query($name, $type, array $options = [])
    {
        return $this->resolve($name);
    }
}

