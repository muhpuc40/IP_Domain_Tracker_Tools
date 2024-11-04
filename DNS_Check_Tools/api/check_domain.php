<?php
header("Content-Type: application/json");

// DNS Check
function checkDNS($domain) {
    $records = dns_get_record($domain, DNS_A);
    return $records ? $records : 'No DNS A records found';
}

// SPF Check
function checkSPF($domain) {
    $txtRecords = dns_get_record($domain, DNS_TXT);
    foreach ($txtRecords as $record) {
        if (strpos($record['txt'], 'v=spf1') !== false) {
            return $record['txt'];
        }
    }
    return 'No SPF record found';
}

// DMARC Check
function checkDMARC($domain) {
    $txtRecords = dns_get_record("_dmarc.$domain", DNS_TXT);
    foreach ($txtRecords as $record) {
        if (strpos($record['txt'], 'v=DMARC1') !== false) {
            return $record['txt'];
        }
    }
    return 'No DMARC record found';
}


// Blacklist Check (detailed per blacklist)
function checkBlacklist($domain) {
    $blacklistZones = [
        "all.s5h.net",
        "b.barracudacentral.org",
        "bl.spamcop.net",
        "blacklist.woody.ch",
        "bogons.cymru.com",
        "cbl.abuseat.org",
        "cdl.anti-spam.org.cn",
        "combined.abuse.ch",
        "db.wpbl.info",
        "dnsbl-1.uceprotect.net",
        "dnsbl-2.uceprotect.net",
        "dnsbl-3.uceprotect.net",
        "dnsbl.anticaptcha.net",
        "dnsbl.dronebl.org",
        "dnsbl.inps.de",
        "dnsbl.sorbs.net",
        "dnsbl.spfbl.net",
        "drone.abuse.ch",
        "duinv.aupads.org",
        "dul.dnsbl.sorbs.net",
        "dyna.spamrats.com",
        "dynip.rothen.com",
        "http.dnsbl.sorbs.net",
        "ips.backscatterer.org",
        "ix.dnsbl.manitu.net",
        "korea.services.net",
        "misc.dnsbl.sorbs.net",
        "noptr.spamrats.com",
        "orvedb.aupads.org",
        "pbl.spamhaus.org",
        "proxy.bl.gweep.ca",
        "psbl.surriel.com",
        "relays.bl.gweep.ca",
        "relays.nether.net",
        "sbl.spamhaus.org",
        "short.rbl.jp",
        "singular.ttk.pte.hu",
        "smtp.dnsbl.sorbs.net",
        "socks.dnsbl.sorbs.net",
        "spam.abuse.ch",
        "spam.dnsbl.anonmails.de",
        "spam.dnsbl.sorbs.net",
        "spam.spamrats.com",
        "spambot.bls.digibase.ca",
        "spamrbl.imp.ch",
        "spamsources.fabel.dk",
        "ubl.lashback.com",
        "ubl.unsubscore.com",
        "virus.rbl.jp",
        "web.dnsbl.sorbs.net",
        "wormrbl.imp.ch",
        "xbl.spamhaus.org",
        "z.mailspike.net",
        "zen.spamhaus.org",
        "zombie.dnsbl.sorbs.net"
    ];

    $results = [];

    foreach ($blacklistZones as $zone) {
        $lookup = $domain . '.' . $zone;
        if (checkdnsrr($lookup, "A")) {  // Perform DNS A record lookup for blacklist
            $results[] = [
                'category' => 'Blacklist',
                'host' => $domain,
                'list' => $zone
            ];
        }
    }

    return !empty($results) ? $results : 'Not found in any blacklist';
}

// SSL Check
function checkSSL($domain) {
    $context = stream_context_create(["ssl" => ["capture_peer_cert" => true]]);
    $client = stream_socket_client("ssl://{$domain}:443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);

    if ($client) {
        $params = stream_context_get_params($client);
        $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
        return $cert['validTo_time_t'] > time() ? date('Y-m-d', $cert['validTo_time_t']) : 'Expired';
    } else {
        return 'SSL connection failed';
    }
}

// Performance Check
function checkPerformance($domain) {
    $start = microtime(true);
    $headers = get_headers("https://{$domain}", 1);
    $duration = microtime(true) - $start;
    return $headers ? "{$duration} seconds" : 'Performance check failed';
}

// MX Check
function checkMX($domain) {
    $mxRecords = dns_get_record($domain, DNS_MX);
    return $mxRecords ? $mxRecords : 'No MX records found';
}

// MX Blacklist Check
function checkMXBlacklist($domain) {
    $mxRecords = dns_get_record($domain, DNS_MX);
    $blacklistResults = [];

    foreach ($mxRecords as $mx) {
        $ip = gethostbyname($mx['target']);
        $blacklistStatus = checkBlacklist($ip);
        $blacklistResults[] = [
            'mx' => $mx['target'],
            'blacklist_status' => $blacklistStatus
        ];
    }

    return !empty($blacklistResults) ? $blacklistResults : 'No MX records found to check';
}

// Aggregate the report
function domainHealthReport($domain) {
    return [
        'DNS' => checkDNS($domain),
        'SPF' => checkSPF($domain),
        'DMARC' => checkDMARC($domain),
        'Blacklist' => checkBlacklist($domain),
        'SSL Expiry Date' => checkSSL($domain),
        'Performance' => checkPerformance($domain),
        'MX Records' => checkMX($domain),
        'MX Blacklist' => checkMXBlacklist($domain),
    ];
}

if (isset($_GET['domain'])) {
    $domain = htmlspecialchars($_GET['domain']);
    $report = domainHealthReport($domain);
    echo json_encode($report);
} else {
    echo json_encode(["error" => "No domain provided"]);
}
