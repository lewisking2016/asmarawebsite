<?php
/**
 * Google Analytics Data API helper
 * Native PHP client for GA4 server-side reporting
 */

class GoogleAnalyticsService {
    private $propertyId;
    private $credentials;
    private $accessToken = null;
    private $tokenExpiresAt = 0;
    private $configurationError = null;

    public function __construct($propertyId = null, $credentials = null) {
        $this->propertyId = $this->normalizePropertyId($propertyId ?: getenv('ASMARA_GA4_PROPERTY_ID'));
        try {
            $this->credentials = $credentials ?: $this->loadCredentialsFromEnvironment();
        } catch (Exception $e) {
            $this->configurationError = $e->getMessage();
            $this->credentials = null;
        }
    }

    public static function fromEnvironment() {
        return new self();
    }

    public function isConfigured() {
        return empty($this->configurationError) && !empty($this->propertyId) && !empty($this->credentials);
    }

    public function getDashboardData($startDate = '30daysAgo', $endDate = 'today') {
        if (!empty($this->configurationError)) {
            return [
                'enabled' => false,
                'error' => $this->configurationError
            ];
        }

        if (!$this->isConfigured()) {
            return [
                'enabled' => false,
                'error' => 'Google Analytics is not configured yet.'
            ];
        }

        try {
            $overview = $this->runReport([
                'dimensions' => [],
                'metrics' => [
                    'activeUsers',
                    'sessions',
                    'newUsers',
                    'screenPageViews',
                    'engagementRate',
                    'bounceRate',
                    'eventCount',
                    'keyEvents'
                ],
                'limit' => 1
            ], $startDate, $endDate);

            $topPages = $this->runReport([
                'dimensions' => ['fullPageUrl'],
                'metrics' => ['screenPageViews', 'activeUsers', 'eventCount'],
                'orderBys' => [
                    ['metric' => 'screenPageViews', 'desc' => true]
                ],
                'limit' => 10
            ], $startDate, $endDate);

            $trafficSources = $this->runReport([
                'dimensions' => ['sessionDefaultChannelGroup'],
                'metrics' => ['sessions', 'activeUsers', 'engagementRate'],
                'orderBys' => [
                    ['metric' => 'sessions', 'desc' => true]
                ],
                'limit' => 10
            ], $startDate, $endDate);

            $audience = $this->runReport([
                'dimensions' => ['country'],
                'metrics' => ['activeUsers', 'sessions'],
                'orderBys' => [
                    ['metric' => 'activeUsers', 'desc' => true]
                ],
                'limit' => 10
            ], $startDate, $endDate);

            return [
                'enabled' => true,
                'property_id' => $this->propertyId,
                'overview' => $overview,
                'top_pages' => $topPages,
                'traffic_sources' => $trafficSources,
                'audience' => $audience
            ];
        } catch (Exception $e) {
            return [
                'enabled' => true,
                'error' => $e->getMessage()
            ];
        }
    }

    private function runReport(array $config, $startDate, $endDate) {
        $payload = [
            'dateRanges' => [
                ['startDate' => $startDate, 'endDate' => $endDate]
            ],
            'dimensions' => array_map(function ($dimension) {
                return ['name' => $dimension];
            }, $config['dimensions'] ?? []),
            'metrics' => array_map(function ($metric) {
                return ['name' => $metric];
            }, $config['metrics'] ?? []),
            'limit' => $config['limit'] ?? 10
        ];

        if (!empty($config['orderBys'])) {
            $payload['orderBys'] = array_map(function ($orderBy) {
                return [
                    'metric' => [
                        'metricName' => $orderBy['metric']
                    ],
                    'desc' => (bool)($orderBy['desc'] ?? true)
                ];
            }, $config['orderBys']);
        }

        $response = $this->apiRequest(
            'POST',
            $this->apiUrl('runReport'),
            $payload
        );

        return $this->normalizeReportResponse($response);
    }

    private function runRealtimeReport(array $config) {
        $payload = [
            'dimensions' => array_map(function ($dimension) {
                return ['name' => $dimension];
            }, $config['dimensions'] ?? []),
            'metrics' => array_map(function ($metric) {
                return ['name' => $metric];
            }, $config['metrics'] ?? []),
            'limit' => $config['limit'] ?? 10
        ];

        if (!empty($config['orderBys'])) {
            $payload['orderBys'] = array_map(function ($orderBy) {
                return [
                    'metric' => [
                        'metricName' => $orderBy['metric']
                    ],
                    'desc' => (bool)($orderBy['desc'] ?? true)
                ];
            }, $config['orderBys']);
        }

        $response = $this->apiRequest(
            'POST',
            $this->apiUrl('runRealtimeReport'),
            $payload
        );

        return $this->normalizeReportResponse($response, true);
    }

    private function normalizeReportResponse(array $response, $realtime = false) {
        $dimensionHeaders = $response['dimensionHeaders'] ?? [];
        $metricHeaders = $response['metricHeaders'] ?? [];
        $rows = $response['rows'] ?? [];

        $normalizedRows = [];
        foreach ($rows as $row) {
            $dimensions = [];
            foreach (($row['dimensionValues'] ?? []) as $index => $value) {
                $key = $dimensionHeaders[$index]['name'] ?? 'dimension_' . $index;
                $dimensions[$key] = $value['value'] ?? '';
            }

            $metrics = [];
            foreach (($row['metricValues'] ?? []) as $index => $value) {
                $key = $metricHeaders[$index]['name'] ?? 'metric_' . $index;
                $metrics[$key] = $this->parseMetricValue($value['value'] ?? '0');
            }

            $normalizedRows[] = [
                'dimensions' => $dimensions,
                'metrics' => $metrics
            ];
        }

        return [
            'row_count' => (int)($response['rowCount'] ?? count($normalizedRows)),
            'rows' => $normalizedRows,
            'totals' => $this->extractTotals($response, $metricHeaders),
            'metadata' => $response['metadata'] ?? [],
            'realtime' => $realtime
        ];
    }

    private function extractTotals(array $response, array $metricHeaders) {
        $totals = [];
        $totalRows = $response['totals'] ?? [];

        if (!empty($totalRows[0]['metricValues'])) {
            foreach ($totalRows[0]['metricValues'] as $index => $value) {
                $key = $metricHeaders[$index]['name'] ?? 'metric_' . $index;
                $totals[$key] = $this->parseMetricValue($value['value'] ?? '0');
            }
        } elseif (!empty($response['rows'][0]['metricValues'])) {
            foreach ($response['rows'][0]['metricValues'] as $index => $value) {
                $key = $metricHeaders[$index]['name'] ?? 'metric_' . $index;
                $totals[$key] = $this->parseMetricValue($value['value'] ?? '0');
            }
        }

        return $totals;
    }

    private function parseMetricValue($value) {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            if (strpos((string)$value, '.') !== false) {
                return (float)$value;
            }

            return (int)$value;
        }

        return $value;
    }

    private function formatMetricValue($metricName, $value) {
        if ($value === null || $value === '') {
            return '0';
        }

        if (in_array($metricName, ['bounceRate', 'engagementRate'], true)) {
            return number_format(((float)$value) * 100, 1) . '%';
        }

        if (is_float($value)) {
            return number_format($value, 1);
        }

        return (string)$value;
    }

    private function apiRequest($method, $url, array $payload = null) {
        $token = $this->getAccessToken();
        if (!$token) {
            throw new Exception('Unable to obtain Google access token.');
        }

        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30
        ]);

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $responseBody = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($responseBody === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Google Analytics request failed: ' . $error);
        }

        curl_close($ch);

        $decoded = json_decode($responseBody, true);
        if ($httpCode < 200 || $httpCode >= 300) {
            $message = $decoded['error']['message'] ?? $responseBody;
            throw new Exception('Google Analytics API error: ' . $message);
        }

        if (!is_array($decoded)) {
            throw new Exception('Google Analytics API returned an invalid response.');
        }

        return $decoded;
    }

    private function getAccessToken() {
        if ($this->accessToken && time() < $this->tokenExpiresAt - 60) {
            return $this->accessToken;
        }

        $privateKey = $this->credentials['private_key'] ?? '';
        $clientEmail = $this->credentials['client_email'] ?? '';

        if ($privateKey === '' || $clientEmail === '') {
            throw new Exception('Google service account credentials are incomplete.');
        }

        $now = time();
        $jwtHeader = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $jwtPayload = $this->base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600
        ]));
        $unsignedJwt = $jwtHeader . '.' . $jwtPayload;

        $signature = '';
        $signed = openssl_sign($unsignedJwt, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if (!$signed) {
            throw new Exception('Unable to sign Google service account JWT.');
        }

        $jwt = $unsignedJwt . '.' . $this->base64UrlEncode($signature);
        $response = $this->postForm('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]);

        $this->accessToken = $response['access_token'] ?? null;
        $this->tokenExpiresAt = $now + (int)($response['expires_in'] ?? 3600);

        if (!$this->accessToken) {
            throw new Exception('Google OAuth token exchange failed.');
        }

        return $this->accessToken;
    }

    private function postForm($url, array $fields) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_TIMEOUT => 30
        ]);

        $responseBody = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($responseBody === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Google OAuth request failed: ' . $error);
        }

        curl_close($ch);

        $decoded = json_decode($responseBody, true);
        if ($httpCode < 200 || $httpCode >= 300) {
            $message = $decoded['error_description'] ?? $decoded['error'] ?? $responseBody;
            throw new Exception('Google OAuth error: ' . $message);
        }

        if (!is_array($decoded)) {
            throw new Exception('Google OAuth returned an invalid response.');
        }

        return $decoded;
    }

    private function apiUrl($method) {
        return 'https://analyticsdata.googleapis.com/v1beta/properties/' . rawurlencode($this->propertyId) . ':' . $method;
    }

    private function loadCredentialsFromEnvironment() {
        $json = getenv('ASMARA_GA4_SERVICE_ACCOUNT_JSON');
        if (!$json) {
            $json = getenv('ASMARA_GA4_SERVICE_ACCOUNT_JSON_B64');
            if ($json) {
                $decoded = base64_decode($json, true);
                if ($decoded !== false) {
                    $json = $decoded;
                }
            }
        }

        if (!$json) {
            $file = getenv('ASMARA_GA4_SERVICE_ACCOUNT_KEY_FILE');
            if ($file && is_file($file)) {
                $json = file_get_contents($file);
            }
        }

        if (!$json) {
            return null;
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new Exception('Google service account JSON is invalid.');
        }

        return $decoded;
    }

    private function normalizePropertyId($propertyId) {
        if (!$propertyId) {
            return null;
        }

        $propertyId = trim((string)$propertyId);
        $propertyId = preg_replace('#^properties/#', '', $propertyId);

        return $propertyId !== '' ? $propertyId : null;
    }

    private function base64UrlEncode($value) {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    public function metricDisplayValue($metricName, $value) {
        return $this->formatMetricValue($metricName, $value);
    }
}
