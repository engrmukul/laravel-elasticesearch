<?php

namespace App\Services;

use Elasticsearch\ClientBuilder;

class ElasticsearchService
{
    protected $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()->setHosts([config('elasticsearch.host')])->build();
    }

    public function autocomplete($index, $field, $prefix)
    {
        $params = [
            'index' => $index,
            'body' => [
                'suggest' => [
                    'autocomplete' => [
                        'prefix' => $prefix,
                        'completion' => [
                            'field' => $field,
                            'size' => 10 // Adjust the number of suggestions as needed
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->client->search($params);

        return $response['suggest']['autocomplete'][0]['options'] ?? [];
    }

    public function search($index, $query)
    {
        $params = [
            'index' => $index,
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => ['field1', 'field2'] // Specify the fields you want to search on
                    ]
                ]
            ]
        ];

        $response = $this->client->search($params);

        return $response['hits']['hits'] ?? [];
    }

    public function filterDocuments($index, $filters)
    {
    	$params = [
        'index' => $index,
        'body' => [
            'query' => [
                'bool' => [
                    'filter' => $filters
                ]
            ]
        ]
    ];

    try {
        $response = $this->client->search($params);
        return $response['hits']['hits'] ?? [];
    	} catch (\Exception $e) {
        // Handle exceptions, e.g., log or throw an error
        	return ['error' => $e->getMessage()];
    		}
	}

    public function insertDocument($index, $id, $document)
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

    public function updateDocument($index, $id, $document)
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

    public function deleteDocument($index, $id)
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


}
