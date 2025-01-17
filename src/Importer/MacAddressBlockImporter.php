<?php

namespace IMEdge\Web\Data\Importer;

use Exception;
use gipfl\ZfDb\Adapter\Adapter;
use IMEdge\Web\Data\ForeignModel\MacAddressBlockRegistration;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

use function fclose;
use function implode;
use function pack;

// MAC-48 / EUI-48
class MacAddressBlockImporter implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const OUI_URLS = [
        'https://standards-oui.ieee.org/oui/oui.csv' => 24,
        'https://standards-oui.ieee.org/oui28/mam.csv' => 28,
        'https://standards-oui.ieee.org/oui36/oui36.csv' => 36,
        'https://standards-oui.ieee.org/iab/iab.csv' => 36,
    ];
    protected const REGISTRY_NAMES = [
        24 => ['MA-L'],
        28 => ['MA-M'],
        36 => ['MA-S', 'IAB'],
    ];
    protected const MIN_EXPECTED_REGISTRATIONS = 5_000;

    protected Adapter $db;

    public function __construct(Adapter $db, LoggerInterface $logger = null)
    {
        $this->db = $db;
        $this->logger = $logger ?? new NullLogger();
    }

    public function refreshRegistrations(): void
    {
        $rows = [
            24 => [],
            28 => [],
            36 => [],
        ];
        foreach (self::OUI_URLS as $url => $prefixLength) {
            $this->downloadUrl($url, $prefixLength, $rows[$prefixLength]);
            $cntRows = count($rows[$prefixLength]);
            $registryName = implode(' / ', self::REGISTRY_NAMES[$prefixLength]);
            if ($cntRows < self::MIN_EXPECTED_REGISTRATIONS) {
                throw new RuntimeException(sprintf(
                    'Got %d registrations from %s (%s), expected at least %d',
                    $cntRows,
                    $registryName,
                    $url,
                    self::MIN_EXPECTED_REGISTRATIONS
                ));
            }
            $this->logger->debug(sprintf(
                'Downloaded %d %s registrations',
                $cntRows,
                $registryName
            ));
        }
        $this->db->beginTransaction();
        $formerStats = $this->getStats();
        try {
            $cntDeleted = $this->db->delete(MacAddressBlockRegistration::DB_TABLE);
            if ($cntDeleted) {
                $this->logger->debug("Deleted $cntDeleted rows");
            }

            $cntInsert = 0;
            foreach ($rows as $prefixRows) {
                foreach ($prefixRows as $row) {
                    $this->db->insert(MacAddressBlockRegistration::DB_TABLE, $row);
                    $cntInsert++;
                }
            }
            $newStats = $this->getStats();
            $this->logger->debug("Inserted $cntInsert rows, committing");
            $this->db->commit();
            $logMessage = 'Refreshed MAC address block registrations: '
                .  $this->formatStatsDiff($formerStats, $newStats);

            if ($formerStats === $newStats) {
                $this->logger->debug($logMessage);
            } else {
                $this->logger->notice($logMessage);
            }
        } catch (Exception $e) {
            try {
                $this->db->rollback();
            } catch (Exception $e) {
            }
            throw new RuntimeException(\sprintf('Importing MAC address blocks failed: %s', $e->getMessage()));
        }
    }

    public function downloadUrl(string $url, int $prefixLength, array &$assignments): array
    {
        $options = [
            'http' => [
                'method' => 'GET',
            ]
        ];
        $context = stream_context_create($options);
        if (! $fp = fopen($url, 'r', false, $context)) {
            throw new RuntimeException("Unable to fetch from URL: '$url'");
        }

        $firstLine = true;
        $registry = self::REGISTRY_NAMES[$prefixLength];
        // @ because of SSL_OP_IGNORE_UNEXPECTED_EOF:
        // fgetcsv(): SSL operation failed with code 1. OpenSSL Error messages:
        // error:0A000126:SSL routines::unexpected eof while reading
        while (false !== ($line = @fgetcsv($fp))) {
            if ($firstLine) {
                // Skip first line, defines columns
                $firstLine = false;
                continue;
            }
            if ($line === [null]) {
                continue; // empty line
            }
            if (! in_array($line[0], $registry)) {
                throw new RuntimeException(sprintf("Registry is not %s: %s", implode(' / ', $registry), $line[0]));
            }

            $assignment = $line[1];
            $prefix = pack('H*', $assignment);
            if (array_key_exists($prefix, $assignments)) {
                $this->logger->notice(sprintf("Skipping duplicate Prefix '%s'", $assignment));
                continue;
            }

            $assignments[$prefix] = [
                'prefix'        => $prefix,
                'prefix_length' => $prefixLength,
                'company'       => $line[2],
                'address'       => $line[3],
            ];
        }
        fclose($fp);

        return $assignments;
    }

    protected function getStats(): array
    {
        $query = $this->db->select()->from(MacAddressBlockRegistration::DB_TABLE, [
            'prefix_length',
            'cnt' => 'COUNT(*)',
        ])->group('prefix_length');
        $sums = [
            24 => 0,
            28 => 0,
            36 => 0,
        ];

        foreach ($this->db->fetchPairs($query) as $prefixLength => $sum) {
            $sums[(int) $prefixLength] = (int) $sum;
        }

        return $sums;
    }

    protected function formatStatsDiff(array $old, array $new): string
    {
        $messages = [];
        foreach ($old as $prefixLength => $oldCount) {
            $newCount = $new[$prefixLength];
            $registryName = implode(' / ', self::REGISTRY_NAMES[$prefixLength]);
            if ($oldCount === $newCount) {
                $messages[] = sprintf('%s has still %d registrations', $registryName, $oldCount);
            } else {
                $messages[] = sprintf('%s went from %d to %d registrations', $registryName, $oldCount, $newCount);
            }
        }

        return implode(', ', $messages);
    }
}
