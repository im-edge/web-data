<?php

namespace IMEdge\Web\Data\Importer;

use Exception;
use Generator;
use gipfl\ZfDb\Adapter\Adapter;
use IMEdge\Web\Data\ForeignModel\AutonomousSystem;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

use function sprintf;

class AutonomousSystemImporter
{
    public const URL = 'https://raw.githubusercontent.com/ipverse/asn-info/master/as.csv';
    protected const MIN_EXPECTED = 100_000;

    protected Adapter $db;
    protected LoggerInterface $logger;

    public function __construct(Adapter $db, ?LoggerInterface $logger = null)
    {
        $this->db = $db;
        $this->logger = $logger ?? new NullLogger();
    }

    public function refreshRegistrations(): void
    {
        $url = self::URL;
        $rows = $this->downloadUrl($url, 'asn');
        $cntRows = count($rows);
        $label = 'ASNs';
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
            $cntDeleted = $this->db->delete(AutonomousSystem::DB_TABLE);
            if ($cntDeleted) {
                $this->logger->debug("Deleted $cntDeleted rows");
            }

            $cntInsert = 0;
            foreach ($rows as $row) {
                $this->db->insert(AutonomousSystem::DB_TABLE, $row);
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
        if (! $fp = fopen($url, 'r', false, $context)) {
            throw new RuntimeException("Unable to fetch from URL: '$url'");
        }
        $firstLine = true;
        $properties = [];
        // Using fgetcsv is not an option, as of:
        //   935,PPS-NET-165,Peter Pan Seafood Co, LLC
        while (false !== ($line = fgets($fp, 4096))) {
            $line = explode(',', $line, 3);
            if ($firstLine) {
                // Skip first line, defines columns
                $firstLine = false;
                $properties = $line;
                continue;
            }
            if ($line === [null]) {
                continue; // empty line
            }
            yield array_combine($properties, $line);
        }
        fclose($fp);
    }

    public function downloadUrl(string $url, string $keyProperty): array
    {
        $result = [];
        foreach ($this->fetchCsv($url) as $line) {
            $result[$line[$keyProperty]] = $line;
        }

        return $result;
    }

    protected function getStats(): array
    {
        $query = $this->db->select()->from(AutonomousSystem::DB_TABLE, [
            'label' => "('ASNs')",
            'cnt' => 'COUNT(*)',
        ]);

        $sums = [
            'ASNs' => 0
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
                $messages[] = sprintf('There are still %d %s', $oldCount, $label);
            } else {
                $messages[] = sprintf('%s went from %d to %d', $label, $oldCount, $newCount);
            }
        }

        return implode(', ', $messages);
    }
}
