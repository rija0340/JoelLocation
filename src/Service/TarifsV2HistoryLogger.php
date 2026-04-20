<?php

namespace App\Service;

use App\Entity\User;
use Psr\Log\LoggerInterface;

class TarifsV2HistoryLogger
{
    private $logger;
    private $logFile;
    private $maxFileSize;

    public function __construct(string $projectDir, int $maxFileSize = 10485760) // 10MB default
    {
        $this->logFile = $projectDir . '/var/log/tarifs_v2_changes.log';
        $this->maxFileSize = $maxFileSize;
    }

    /**
     * Log a price change (or deletion when newPrice is null)
     */
    public function logChange(
        string $vehicleName,
        string $month,
        string $interval,
        ?float $oldPrice,
        ?float $newPrice,
        User $user,
        ?string $ipAddress = null
    ): void {
        // Check if rotation is needed
        $this->rotateIfNeeded();

        // Calculate difference
        $oldPriceStr = $oldPrice !== null ? number_format($oldPrice, 2) : 'N/A';
        $newPriceStr = $newPrice !== null ? number_format($newPrice, 2) : 'N/A';
        
        if ($oldPrice !== null && $newPrice !== null) {
            $diff = $newPrice - $oldPrice;
            $diffStr = ($diff >= 0 ? '+' : '') . number_format($diff, 2);
            $percentage = $oldPrice != 0 ? (($diff / $oldPrice) * 100) : 0;
            $percentageStr = ($percentage >= 0 ? '+' : '') . number_format($percentage, 1);
            $changeStr = "$oldPriceStr € → $newPriceStr € ($diffStr €, $percentageStr%)";
        } elseif ($oldPrice !== null && $newPrice === null) {
            // Deletion
            $changeStr = "$oldPriceStr € → (deleted)";
        } elseif ($oldPrice === null && $newPrice !== null) {
            // New entry
            $changeStr = "N/A → $newPriceStr € (new)";
        } else {
            $changeStr = "N/A → N/A";
        }

        // Format log entry
        $timestamp = date('Y-m-d H:i:s');
        $userName = $user->getNom() . ' ' . $user->getPrenom();
        $ipStr = $ipAddress ? " | IP: $ipAddress" : '';

        $logEntry = "[$timestamp] User: $userName | Vehicle: $vehicleName | Month: $month | Interval: $interval | $changeStr$ipStr" . PHP_EOL;

        // Write to file
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log a bulk operation
     */
    public function logBulkOperation(
        string $operation,
        string $vehicleName,
        int $affectedCells,
        User $user,
        ?string $ipAddress = null
    ): void {
        $this->rotateIfNeeded();

        $timestamp = date('Y-m-d H:i:s');
        $userName = $user->getNom() . ' ' . $user->getPrenom();
        $ipStr = $ipAddress ? " | IP: $ipAddress" : '';

        $logEntry = "[$timestamp] User: $userName | Vehicle: $vehicleName | Operation: $operation | Affected: $affectedCells cells$ipStr" . PHP_EOL;

        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Read history from log file
     */
    public function getHistory(int $limit = 100, ?\DateTime $from = null, ?\DateTime $to = null): array
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return [];
        }

        // Reverse to get newest first
        $lines = array_reverse($lines);
        $history = [];
        $count = 0;

        foreach ($lines as $line) {
            if ($count >= $limit) {
                break;
            }

            $entry = $this->parseLogEntry($line);
            if ($entry === null) {
                continue;
            }

            // Apply date filters
            if ($from && $entry['date'] < $from) {
                continue;
            }
            if ($to && $entry['date'] > $to) {
                continue;
            }

            $history[] = $entry;
            $count++;
        }

        return $history;
    }

    /**
     * Parse a log line into structured data
     */
    private function parseLogEntry(string $line): ?array
    {
        // Pattern: [2024-04-15 10:30:15] User: John Doe | Vehicle: Peugeot 208 | Month: Janvier | Interval: 1-2 jours | 45.00 € → 50.00 € (+5.00 €, +11.1%) | IP: 192.168.1.1
        // Use [^|]+ instead of .+? to avoid regex issues with special characters (accents, €, etc.)
        // Change field stops at | so IP is not included
        $pattern = '/\[([\d\-:\s]+)\] User: ([^|]+) \| Vehicle: ([^|]+) \| Month: ([^|]+) \| Interval: ([^|]+) \| ([^|]+)/';
        
        if (!preg_match($pattern, $line, $matches)) {
            return null;
        }

        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $matches[1]);
        $changeStr = trim($matches[6]);

        // Parse price change
        $oldPrice = null;
        $newPrice = null;
        $difference = null;
        $percentage = null;

        // Update: price → new price (+diff €, +X%)
        // Using Unicode escapes in double-quoted strings
        if (preg_match("/^([\d.]+) \xE2\x82\xAC \xE2\x86\x92 ([\d.]+) \xE2\x82\xAC \(([\+\-][\d.]+) \xE2\x82\xAC, ([\+\-][\d.]+)%\)$/u", $changeStr, $priceMatches)) {
            $oldPrice = (float) $priceMatches[1];
            $newPrice = (float) $priceMatches[2];
            $difference = (float) $priceMatches[3];
            $percentage = (float) $priceMatches[4];
        }
        // New entry: N/A → price €
        elseif (preg_match("/^N\/A \xE2\x86\x92 ([\d.]+) \xE2\x82\xAC \(new\)$/u", $changeStr, $priceMatches)) {
            $newPrice = (float) $priceMatches[1];
        }
        // Deletion: price € → (deleted)
        elseif (preg_match("/^([\d.]+) \xE2\x82\xAC \xE2\x86\x92 \(deleted\)$/u", $changeStr, $priceMatches)) {
            $oldPrice = (float) $priceMatches[1];
            $newPrice = null;
        }
        // Empty deletion: N/A → (deleted)
        elseif (preg_match("/^N\/A \xE2\x86\x92 \(deleted\)$/u", $changeStr)) {
            $oldPrice = null;
            $newPrice = null;
        }

        return [
            'date' => $date,
            'date_formatted' => $date ? $date->format('Y-m-d H:i:s') : $matches[1],
            'user' => trim($matches[2]),
            'vehicle' => trim($matches[3]),
            'month' => trim($matches[4]),
            'interval' => trim($matches[5]),
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'difference' => $difference,
            'percentage' => $percentage,
            'raw_line' => $line
        ];
    }

    /**
     * Get statistics from log file
     */
    public function getStatistics(): array
    {
        if (!file_exists($this->logFile)) {
            return [
                'total_changes' => 0,
                'today_changes' => 0,
                'last_change' => null
            ];
        }

        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false || empty($lines)) {
            return [
                'total_changes' => 0,
                'today_changes' => 0,
                'last_change' => null
            ];
        }

        $totalChanges = count($lines);
        $today = date('Y-m-d');
        $todayChanges = 0;
        $lastChange = null;

        foreach ($lines as $line) {
            if (preg_match('/\[([\d\-:\s]+)\]/', $line, $matches)) {
                $dateStr = $matches[1];
                if (strpos($dateStr, $today) === 0) {
                    $todayChanges++;
                }
                if ($lastChange === null) {
                    $lastChange = $dateStr;
                }
            }
        }

        return [
            'total_changes' => $totalChanges,
            'today_changes' => $todayChanges,
            'last_change' => $lastChange
        ];
    }

    /**
     * Rotate log file if it exceeds max size
     */
    private function rotateIfNeeded(): void
    {
        if (!file_exists($this->logFile)) {
            return;
        }

        $fileSize = filesize($this->logFile);
        if ($fileSize < $this->maxFileSize) {
            return;
        }

        // Rotate: rename current to .1, .1 to .2, etc.
        $maxBackups = 5;
        
        for ($i = $maxBackups - 1; $i >= 1; $i--) {
            $oldFile = $this->logFile . '.' . $i;
            $newFile = $this->logFile . '.' . ($i + 1);
            
            if (file_exists($oldFile)) {
                rename($oldFile, $newFile);
            }
        }

        rename($this->logFile, $this->logFile . '.1');
    }

    /**
     * Get log file path
     */
    public function getLogFilePath(): string
    {
        return $this->logFile;
    }
}
