<?php
namespace OpenVPN;

class Status {

    private $contents;
    private $updated;
    private $clients;
    private $stats;
    private $statusVersion;

    private $srcTimeZone;

    public function __construct () {
        $this->srcTimeZone = new \DateTimeZone(\OpenVPN\Config::getValue('timezone.src'));
    }

    public function parse () {
        $contents = $this->contents;
        
        // Determine version
        if (substr($contents, 0, 5) === 'TITLE') {
            $statusVersion = 2;
        } else {
            $statusVersion = 1;
        }

        if ($statusVersion === 1) {
            preg_match('/OpenVPN CLIENT LIST(.*)Common.*Connected Since(.*)ROUTING TABLE.*Virtual.*Last Ref(.*)GLOBAL STATS(.*)END/s', $contents, $matches);
            $this->setUpdated($this->parseUpdated($matches[1]));
            $this->setClients($this->parseClients($matches[2]));
            $this->parseRouting($matches[3]);
            $this->setStats(preg_split('/\n|\r\n?/', trim($matches[4])));
        } else {
            preg_match_all('/TITLE.*2017(.*)HEADER,CLIENT_LIST.*Username(.*)HEADER,ROUTING_TABLE.*\(time_t\)(.*)GLOBAL_STATS,(.*)END/s', $contents, $matches);
            $this->setUpdated($this->parseUpdated($matches[1][0]));
            $this->setClients($this->parseClients($matches[2][0]));
            $this->parseRouting($matches[3][0]);
            $this->setStats(preg_split('/\n|\r\n?/', trim($matches[4][0])));
        }

    }

    /**
     * @param $string
     * @return \DateTime
     */
    private function parseUpdated ($string) {
        $updated = explode(',', trim($string));
        if ($this->statusVersion === 1) {
            $updated = array_pop($updated);
        } else {
            $updated = $updated[1];
        }
        
        return new \DateTime($updated, clone($this->srcTimeZone));
    }

    private function parseClients ($string) {
      
        if ($this->statusVersion === 1) {
            $i = $j = 0;
        } else {
            $i = 1;
            $j = 2; 
        }

        $clientLines = preg_split('/\n|\r\n?/', trim($string));
        $clients = array();

        foreach ($clientLines as $clientLine) {
            $fields = str_getcsv($clientLine);

            $client = new Client();

            // If the name is UNDEF, just ignore it
            if ($fields[0] == 'UNDEF') {
                continue;
            }

            // Name
            $client->name = $fields[0+$i];

            // IP and Port
            preg_match('/(.*):([\d]+)/', $fields[1+$i], $matches);
            $client->realIp = $matches[1];
            $client->realPort = $matches[2];

            // Other Fields
            $client->bytesReceived = $fields[2+$j];
            $client->bytesSent = $fields[3+$j];
            $client->connectedSince = new \DateTime($fields[4+$j], clone($this->srcTimeZone));

            $clients[] = $client;
        }

        usort($clients, function ($a, $b) {
            /** @var Client $a */
            /** @var Client $b */
            return strcmp($a->name, $b->name);
        });

        return $clients;
    }

    private function parseRouting ($string) {
        
        if ($this->statusVersion === 1) {
            $i = 0;
        } else {
            $i = 1;
        }

        $routingLines = preg_split('/\n|\r\n?/', trim($string));

        foreach ($routingLines as $routingLine) {
            $fields = str_getcsv($routingLine);
            $ip = $fields[0+$i];
            $name = $fields[1+$i];
            $dateTime = new \DateTime($fields[3+$i], clone($this->srcTimeZone));

            $client = $this->findClient($name);
            if ($client) {
                $client->vpnIp = $ip;
                $client->routingSince = $dateTime;
                if (strpos($ip, '/') !== false) {
                    $client->networks = $client->networks.$ip.' ';
		            }
            }
        }
    }

    /**
     * @param string $name the common name of the client
     * @return bool|Client
     */
    private function findClient ($name) {
        /** @var Client $client */
        foreach ($this->getClients() as $client) {
            if ($client->name == $name) {
                return $client;
            }
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getClients () {
        return $this->clients;
    }

    /**
     * @param mixed $clients
     */
    public function setClients ($clients) {
        $this->clients = $clients;
    }

    public function loadFromFile ($fileName) {
        $contents = file_get_contents($fileName);

        if (! $contents) {
            throw new \Exception('File not found or empty.');
        }

        $this->contents = $contents;
    }

    public function loadFromResource ($fileResource) {
        $contents = stream_get_contents($fileResource);

        if (! $contents) {
            throw new \Exception('Resource not available or empty.');
        }

        $this->contents = $contents;
    }

    public function loadFromString ($contents) {
        if (! $contents) {
            throw new \Exception('Given contents were empty.');
        }

        $this->contents = $contents;
    }

    /**
     * @return mixed
     */
    public function getContents () {
        return $this->contents;
    }

    /**
     * @param mixed $contents
     */
    public function setContents ($contents) {
        $this->contents = $contents;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated () {
        return $this->updated;
    }

    /**
     * @param \DateTime $updated
     */
    public function setUpdated (\DateTime $updated) {
        $this->updated = $updated;
    }

    /**
     * @return mixed
     */
    public function getStats () {
        return $this->stats;
    }

    /**
     * @param mixed $stats
     */
    public function setStats ($stats) {
        $this->stats = $stats;
    }
}
