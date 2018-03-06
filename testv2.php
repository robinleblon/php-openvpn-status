<?php
include(__DIR__ . '/bootstrap.php');

$file = <<<LOGFILE
TITLE,OpenVPN 2.3.10 x86_64-pc-linux-gnu [SSL (OpenSSL)] [LZO] [EPOLL] [PKCS11] [MH] [IPv6] built on Jun 22 2017
TIME,Tue Mar  6 15:55:20 2018,1520348120
HEADER,CLIENT_LIST,Common Name,Real Address,Virtual Address,Bytes Received,Bytes Sent,Connected Since,Connected Since (time_t),Username
CLIENT_LIST,00D01259F0E7,10.10.0.254:40421,10.8.0.10,6587324,1272977,Tue Mar  6 15:08:03 2018,1520345283,UNDEF
CLIENT_LIST,00156DDFE44F,10.10.0.254:52707,10.8.0.18,27531,27862,Tue Mar  6 15:08:05 2018,1520345285,UNDEF
HEADER,ROUTING_TABLE,Virtual Address,Common Name,Real Address,Last Ref,Last Ref (time_t)
ROUTING_TABLE,10.8.0.10,00D01259F0E7,10.10.0.254:40421,Tue Mar  6 15:55:16 2018,1520348116
ROUTING_TABLE,192.168.71.0/24,00D01259F0E7,10.10.0.254:40421,Tue Mar  6 15:08:05 2018,1520345285
ROUTING_TABLE,10.8.0.18,00156DDFE44F,10.10.0.254:52707,Tue Mar  6 15:08:07 2018,1520345287
GLOBAL_STATS,Max bcast/mcast queue length,0
END
LOGFILE;

$status = new OpenVPN\Status();
$status->loadFromString($file);
$status->parse();
print_r($status->getClients());

foreach($status->getClients() as $client) {
    echo $client->name . PHP_EOL;
    print("<pre>".print_r($client->getReadableArray(),true)."</pre>");
}