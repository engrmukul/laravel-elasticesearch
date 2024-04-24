<?php

namespace App\Services;

use Elastic\Elasticsearch\ClientBuilder;

class ElasticsearchService
{
    protected $client;

    public function __construct()
    {
        try {
            $client = ClientBuilder::create()->build();
            $this->client = $client;
        } catch (\Exception $e) {
            echo "Error connecting to Elasticsearch: " . $e->getMessage() . "\n";
        }
    }

    public function autocomplete($index, $fields, $prefix)
    {
        try {

            $query = [
              'bool' => [
                'should' => [],
              ],
            ];

            foreach ($fields as $field) {
                $query['bool']['should'][] = [
                    'match_phrase_prefix' => [
                      $field => [
                        'query' => $prefix,
                      ],
                    ],
                  ];
             }

              $params = [
                'index' => $index,
                'body' => [
                    'query' => [
                        $query['bool']['should']
                        ]
                    ]
                ];

            $response = $this->client->search($params);

            if (!isset($response['hits']['total']['value']) || $response['hits']['total']['value'] === 0) {
                return array();
            } else {
                return $response['hits']['hits'];
            }

        } catch (\Exception $e) {
            echo "Error " . $e->getMessage() . "\n";
        }
    }

    public function search($index, $query, $currentPage, $pageSize)
    {
        try {
            $from = ($currentPage - 1) * $pageSize;
            $size = $pageSize;

            $params = [
                'index' => $index,
                'body' => [
                    'query' => [
                        'multi_match' => [
                            'query' => $query,
                            //'fields' => ['field1', 'field2'] // Specify the fields you want to search on
                        ]
                    ]
                ],

                'from' => $from,
                'size' => $size,
            ];

            $response = $this->client->search($params);

            if (!isset($response['hits']['total']['value']) || $response['hits']['total']['value'] === 0) {
                return array();
            } else {
                return $response['hits']['hits'];
            }

        } catch (\Exception $e) {
            echo "Error " . $e->getMessage() . "\n";
        }
    }

    public function filter($index, $filters, $currentPage, $pageSize)
    {
        try {
            $from = ($currentPage - 1) * $pageSize;
            $size = $pageSize;

            $params = [
                'index' => $index,
                'body' => [
                    'query' => [
                        'bool' => [
                            'filter' => $filters
                        ]
                    ]
                ],

                'from' => $from,
                'size' => $size,
            ];

            $response = $this->client->search($params);

            if (!isset($response['hits']['total']['value']) || $response['hits']['total']['value'] === 0) {
                return array();
            } else {
                return $response['hits']['hits'];
            }

        } catch (\Exception $e) {
            echo "Error " . $e->getMessage() . "\n";
        }
    }

    public function elasticDocumentSave($index, $id, $document)
    {
        $params = [
            'index' => $index,
            'id' => $id,
            'body' => $document
        ];

        try {
            return $this->client->index($params);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function elasticDocumentUpdate($index, $id, $document)
    {
        $params = [
            'index' => $index,
            'id' => $id,
            'body' => ['doc' => $document]
        ];

        try {
            return $this->client->update($params);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function elasticDocumentDelete($index, $id)
    {
        $params = [
            'index' => $index,
            'id' => $id
        ];

        try {
            return $this->client->delete($params);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function createElasticIndex(string $indexName, array $mappings = [])
    {
        $params = [
            'index' => $indexName,
        ];

        if (!empty($mappings)) {
            $params['body'] = [
                'mappings' => $mappings,
            ];
        }

        try {
            return $this->client->indices()->create($params);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function updateElasticIndex(string $indexName, array $updatedMappings)
    {
        $params = [
            'index' => $indexName,
            'body' => [
                'properties' => $updatedMappings,
            ],
        ];

        try {
            return $this->client->indices()->putMapping($params);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function deleteElasticIndex(array $indicesToDelete)
    {
        try {
            $bulkData = [];
            foreach ($indicesToDelete as $indexName) {
                $bulkData[] = [
                    'delete' => [
                        '_index' => $indexName,
                    ],
                ];
            }

            $params = ['body' => $bulkData];

            $response = $this->client->bulk($params);
            if ($response['errors'] === false) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function backupElasticIndex()
    {

    }

    public function exportElasticIndex()
    {

    }

    public function listAllElasticIndexes(): array
    {
        try {
            $response = $this->client->cat()->indices(['v' => true]);
            $indices = [];

            foreach ($response as $line) {
                $indices[] = explode(' ', $line)[2]; // Extract index name from response line
            }

            return $indices;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function listAllDocsInIndex(string $indexName)
    {
        try {
            $scrollId = null;
            $documents = [];

            while (true) {
                $params = [
                    'index' => $indexName,
                    'scroll' => '1m', // Adjust scroll size as needed
                    'size' => 1000, // Increase size for large indexes
                ];

                if ($scrollId) {
                    $params['scroll_id'] = $scrollId;
                }

                $response = $this->client->search()->scroll($params);

                $scrollId = $response['_scroll_id'];
                $documents = array_merge($documents, $response['hits']['hits']);

                if (empty($response['hits']['hits'])) {
                    break;
                }
            }

            // Clear the scroll context to release resources
            $this->client->scroll()->clear(['scroll_id' => $scrollId]);
        }catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function bulkSave(string $indexName, array $data)
    {
        try {
            $bulkData = [];

            foreach ($data as $document) {
                $bulkData[] = [
                    'index' => [
                        '_index' => $indexName,
                    ],
                ];
                $bulkData[] = $document;
            }

            $params = ['body' => $bulkData];
            $response = $this->client->bulk($params);

            if ($response['errors'] === false) {
                return count($data) . ' documents to index "' . $indexName . '".';
            } else {
                echo 'Error importing data: ' . json_encode($response);
            }
        }catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }

    }


}
