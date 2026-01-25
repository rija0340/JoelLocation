<?php

namespace App\Service;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Exception;

class TsaClient
{
    private $httpClient;
    private $tsaUrl;
    private $username;
    private $password;

    public function __construct(HttpClientInterface $httpClient, string $tsaUrl = null, string $username = null, string $password = null)
    {
        $this->httpClient = $httpClient;
        // Default to FreeTSA if no URL provided
        $this->tsaUrl = $tsaUrl ? $tsaUrl : 'https://freetsa.org/tsa';
        $this->username = $username ? $username : '';
        $this->password = $password ? $password : '';
    }

    /**
     * Request timestamp token for a given hash from TSA
     */
    public function requestTimestamp(string $hash): ?string
    {
        $tempRequest = tempnam(sys_get_temp_dir(), 'tsq');

        try {
            // 1. Generate Timestamp Request using OpenSSL
            // Note: input hash must be the digest, not the data
            // calculateSha256Hash returns hex, but -digest expects hex string by default in some versions or bytes?
            // openssl ts -query -digest <hex> works.

            $process = new Process(['openssl', 'ts', '-query', '-digest', $hash, '-sha256', '-cert', '-out', $tempRequest]);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $requestContent = file_get_contents($tempRequest);

            // 2. Send Request to TSA
            $options = [
                'headers' => [
                    'Content-Type' => 'application/timestamp-query',
                ],
                'body' => $requestContent,
            ];

            // Add basic auth if configured
            if ($this->username && $this->password) {
                $options['auth_basic'] = [$this->username, $this->password];
            }

            $response = $this->httpClient->request('POST', $this->tsaUrl, $options);

            if ($response->getStatusCode() !== 200) {
                throw new Exception('TSA returned status code ' . $response->getStatusCode());
            }

            $token = $response->getContent(); // Binary DER

            // Return base64 encoded token for storage
            return base64_encode($token);

        } catch (Exception $e) {
            error_log("TSA request failed: " . $e->getMessage());
            return null;
        } finally {
            if (file_exists($tempRequest)) {
                unlink($tempRequest);
            }
        }
    }

    /**
     * Verify a timestamp token from TSA
     */
    public function verifyTimestamp(string $token, string $originalHash): bool
    {
        if (empty($token)) {
            return false;
        }

        // Real verification would require the TSA's CA certificate
        // For now, we decode to ensure it's valid base64 and looks like a token
        $decoded = base64_decode($token);
        if ($decoded === false) {
            return false;
        }

        // TODO: Implement full OpenSSL verification
        // openssl ts -verify -in <token_file> -digest <hash> -CAfile <ca_cert>

        return strlen($decoded) > 0;
    }

    /**
     * Get the current configured TSA URL
     */
    public function getTsaUrl(): string
    {
        return $this->tsaUrl;
    }
}