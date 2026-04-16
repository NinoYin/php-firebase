<?php

class UserRepository
{
    private FirestoreClient $firestore;
    private string $collection;

    public function __construct(FirestoreClient $firestore, array $firebaseConfig)
    {
        $this->firestore = $firestore;
        $this->collection = $firebaseConfig['collection_users'];
    }

    public function create(array $data): array
    {
        return $this->firestore->createDocument($this->collection, $data);
    }

    public function update(string $id, array $data): array
    {
        return $this->firestore->updateDocument($this->collection, $id, $data);
    }

    public function findById(string $id): ?array
    {
        return $this->firestore->getDocument($this->collection, $id);
    }

    public function findByUsuario(string $usuario): ?array
    {
        $items = $this->firestore->runQuery([
            'from' => [
                ['collectionId' => $this->collection]
            ],
            'where' => [
                'fieldFilter' => [
                    'field' => ['fieldPath' => 'usuario'],
                    'op' => 'EQUAL',
                    'value' => ['stringValue' => $usuario]
                ]
            ],
            'limit' => 1
        ]);

        return $items[0] ?? null;
    }

    public function findAllActive(?string $q = null): array
    {
        $filters = [
            [
                'fieldFilter' => [
                    'field' => ['fieldPath' => 'deleted'],
                    'op' => 'EQUAL',
                    'value' => ['booleanValue' => false]
                ]
            ]
        ];

        $items = $this->firestore->runQuery([
            'from' => [
                ['collectionId' => $this->collection]
            ],
            'where' => [
                'compositeFilter' => [
                    'op' => 'AND',
                    'filters' => $filters
                ]
            ]
        ]);

        if ($q !== null && trim($q) !== '') {
            $q = mb_strtolower(trim($q));
            $items = array_values(array_filter($items, function ($item) use ($q) {
                $text = mb_strtolower(
                    ($item['nombre'] ?? '') . ' ' .
                    ($item['apaterno'] ?? '') . ' ' .
                    ($item['amaterno'] ?? '') . ' ' .
                    ($item['usuario'] ?? '') . ' ' .
                    ($item['ciudad'] ?? '') . ' ' .
                    ($item['estado'] ?? '')
                );
                return str_contains($text, $q);
            }));
        }

        return $items;
    }
}