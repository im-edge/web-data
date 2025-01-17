<?php

namespace IMEdge\Web\Data\Importer;

use Exception;
use Generator;
use gipfl\ZfDb\Adapter\Adapter;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

use function sprintf;

class IpToCountryLiteImporter
{
    public const URL = 'https://download.db-ip.com/free/dbip-country-lite-YYYY-MM.csv.gz';
    public const DB_TABLE = 'data_ip_country_lite';
    protected const MIN_EXPECTED = 500_000;

    protected Adapter $db;
    protected LoggerInterface $logger;

    public function __construct(Adapter $db, LoggerInterface $logger = null)
    {
        $this->db = $db;
        $this->logger = $logger ?? new NullLogger();
    }

    public function refreshRegistrations(): void
    {
        $url = self::URL;
        $url = (string) preg_replace('/YYYY/', date('Y'), $url);
        $url = (string) preg_replace('/MM/', date('m'), $url);
        $rows = $this->downloadUrl($url);
        $cntRows = count($rows);
        $label = 'IP Ranges';
        if ($cntRows < self::MIN_EXPECTED) {
            throw new RuntimeException(sprintf(
                'Got %d %s %s, expected at least %d',
                $cntRows,
                $label,
                $url,
                self::MIN_EXPECTED
            ));
        }
        $this->logger->debug(sprintf(
            'Downloaded %d %s',
            $cntRows,
            $label
        ));
        $this->db->beginTransaction();
        $formerStats = $this->getStats();
        try {
            $cntDeleted = $this->db->delete(self::DB_TABLE);
            if ($cntDeleted) {
                $this->logger->debug("Deleted $cntDeleted rows");
            }

            $cntInsert = 0;
            foreach ($rows as $row) {
                $this->db->insert(self::DB_TABLE, $row);
                $cntInsert++;
            }
            $newStats = $this->getStats();
            $this->logger->debug("Inserted $cntInsert rows, committing");
            $this->db->commit();
            $logMessage = sprintf('Refreshed %s: %s', $label, $this->formatStatsDiff($formerStats, $newStats));

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
            throw new RuntimeException(sprintf('Importing %s failed: %s', $label, $e->getMessage()));
        }
    }

    protected function fetchCsv(string $url): Generator
    {
        $options = [
            'http' => [
                'method' => 'GET',
            ]
        ];
        $context = stream_context_create($options);
        if (! $fp = fopen("compress.zlib://$url", 'r', false, $context)) {
            throw new RuntimeException("Unable to fetch from URL: '$url'");
        }
        $firstLine = true;
        $properties = [];

        // TODO: Flag?
        $firstLine = false;
        $properties = ['ip_range_from', 'ip_range_to', 'country_code'];
        while (false !== ($line = fgetcsv($fp))) {
            if ($firstLine) {
                // Skip first line, defines columns
                $firstLine = false;
                $properties = $line;
                continue;
            }
            if ($line === [null]) {
                continue; // empty line
            }
            $row = array_combine($properties, $line);
            $row['ip_range_from'] = inet_pton($row['ip_range_from']);
            $row['ip_range_to'] = inet_pton($row['ip_range_to']);
            $row['ip_family'] = (strlen($row['ip_range_from']) === 4) ? 'IPv4' : 'IPv6';
            yield $row;
        }
        fclose($fp);
    }

    public function downloadUrl(string $url): array
    {
        $result = [];
        foreach ($this->fetchCsv($url) as $line) {
            $result[] = $line;
        }

        return $result;
    }

    protected function getStats(): array
    {
        $query = $this->db->select()->from(self::DB_TABLE, [
            'label' => "('IP Ranges')",
            'cnt' => 'COUNT(*)',
        ]);

        $sums = [
            'IP Ranges' => 0
        ];
        foreach ($this->db->fetchPairs($query) as $label => $sum) {
            $sums[$label] = (int) $sum;
        }

        return $sums;
    }

    protected function formatStatsDiff(array $old, array $new): string
    {
        $messages = [];
        foreach ($old as $label => $oldCount) {
            $newCount = $new[$label];
            if ($oldCount === $newCount) {
                $messages[] = sprintf('There are still %d %d', $oldCount, $label);
            } else {
                $messages[] = sprintf('%s went from %d to %d', $label, $oldCount, $newCount);
            }
        }

        return implode(', ', $messages);
    }
}
