<?php

class FirestoreClient
{
    private array $firebaseConfig;
    private string $accessToken;
    private string $baseUrl;
    private string $projectId;

    public function __construct(array $firebaseConfig)
    {
        $this->firebaseConfig = $firebaseConfig;
        $this->accessToken = FirebaseAuth::getAccessToken($firebaseConfig);
        $this->baseUrl = rtrim($firebaseConfig['firestore_base_url'], '/');
        $this->projectId = $firebaseConfig['project_id'];
    }

    public function createDocument(string $collection, array $data): array
    {
        $url = "{$this->baseUrl}/projects/{$this->projectId}/databases/(default)/documents/{$collection}";
        return $this->request('POST', $url, $this->toFirestoreDocument($data));
    }

    public function updateDocument(string $collection, string $documentId, array $data): array
    {
        $url = "{$this->baseUrl}/projects/{$this->projectId}/databases/(default)/documents/{$collection}/{$documentId}";
        return $this->request('PATCH', $url, $this->toFirestoreDocument($data));
    }

    public function getDocument(string $collection, string $documentId): ?array
    {
        $url = "{$this->baseUrl}/projects/{$this->projectId}/databases/(default)/documents/{$collection}/{$documentId}";
        $response = $this->request('GET', $url, null, false);

        if (($response['http_code'] ?? 500) === 404) {
            return null;
        }

        if (($response['http_code'] ?? 500) >= 400) {
            throw new Exception('Error al obtener documento: ' . ($response['raw'] ?? ''));
        }

        return $this->fromFirestoreDocument($response['body']);
    }

    public function runQuery(array $structuredQuery): array
    {
        $url = "{$this->baseUrl}/projects/{$this->projectId}/databases/(default)/documents:runQuery";
        $result = $this->request('POST', $url, [
            'structuredQuery' => $structuredQuery
        ]);

        $items = [];
        foreach ($result as $row) {
            if (!empty($row['document'])) {
                $items[] = $this->fromFirestoreDocument($row['document']);
            }
        }

        return $items;
    }

    private function request(string $method, string $url, ?array $body = null, bool $decodeBodyDirectly = true): array
    {
        $ch = curl_init($url);

        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ];

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers
        ];

        if ($body !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($body, JSON_UNESCAPED_UNICODE);
        }

        curl_setopt_array($ch, $options);

        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($raw === false) {
            throw new Exception('Error de conexión con Firestore');
        }

        curl_close($ch);

        $decoded = json_decode($raw, true);

        if (!$decodeBodyDirectly) {
            return [
                'http_code' => $httpCode,
                'body' => $decoded,
                'raw' => $raw
            ];
        }

        if ($httpCode >= 400) {
            throw new Exception('Firestore error: ' . $raw);
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function toFirestoreDocument(array $data): array
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[$key] = $this->encodeValue($value);
        }

        return ['fields' => $fields];
    }

    private function encodeValue($value): array
    {
        if (is_null($value)) {
            return ['nullValue' => null];
        }

        if (is_bool($value)) {
            return ['booleanValue' => $value];
        }

        if (is_int($value)) {
            return ['integerValue' => (string) $value];
        }

        if (is_float($value)) {
            return ['doubleValue' => $value];
        }

        if (is_array($value)) {
            $isAssoc = array_keys($value) !== range(0, count($value) - 1);

            if ($isAssoc) {
                $fields = [];
                foreach ($value as $k => $v) {
                    $fields[$k] = $this->encodeValue($v);
                }
                return ['mapValue' => ['fields' => $fields]];
            }

            $values = [];
            foreach ($value as $item) {
                $values[] = $this->encodeValue($item);
            }

            return ['arrayValue' => ['values' => $values]];
        }

        return ['stringValue' => (string) $value];
    }

    private function fromFirestoreDocument(array $document): array
    {
        $name = $document['name'] ?? '';
        $parts = explode('/', $name);
        $id = end($parts);

        $fields = $document['fields'] ?? [];
        $parsed = ['id' => $id];

        foreach ($fields as $key => $value) {
            $parsed[$key] = $this->decodeValue($value);
        }

        return $parsed;
    }

    private function decodeValue(array $value)
    {
        if (isset($value['stringValue'])) return $value['stringValue'];
        if (isset($value['integerValue'])) return (int) $value['integerValue'];
        if (isset($value['doubleValue'])) return (float) $value['doubleValue'];
        if (isset($value['booleanValue'])) return (bool) $value['booleanValue'];
        if (array_key_exists('nullValue', $value)) return null;

        if (isset($value['mapValue'])) {
            $fields = $value['mapValue']['fields'] ?? [];
            $result = [];
            foreach ($fields as $k => $v) {
                $result[$k] = $this->decodeValue($v);
            }
            return $result;
        }

        if (isset($value['arrayValue'])) {
            $values = $value['arrayValue']['values'] ?? [];
            return array_map(fn($item) => $this->decodeValue($item), $values);
        }

        return null;
    }
}