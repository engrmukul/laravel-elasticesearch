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
              $info = $client->info();
              $this->client = $client;

              //echo "Elasticsearch connection successful!\n";
              // Optional: Process information from $info (cluster name, version, etc.)
            } catch (Exception $e) {
              echo "Error connecting to Elasticsearch: " . $e->getMessage() . "\n";
            }

        }

        public function autocomplete($index, $field, $prefix)
        {
            try {
                $params = [
                    'index' => $index,
                    'body' => [
                         'query' => [
                            'match_phrase_prefix' => [
                              "$field" => $prefix
                            ]
                          ]
                    ]
                ];

                $response = $this->client->search($params);

                if (!isset($response['hits']['total']['value']) || $response['hits']['total']['value'] === 0) {
                    return array();
                }else{
                    return $response['hits']['hits'];
                }
     
            } catch (Exception $e) {
              echo "Error " . $e->getMessage() . "\n";
            }
        }

        public function search($index, $query)
        {
            try {
                $params = [
                    'index' => $index,
                    'body' => [
                        'query' => [
                            'multi_match' => [
                                'query' => $query,
                                //'fields' => ['field1', 'field2'] // Specify the fields you want to search on
                            ]
                        ]
                    ]
                ];

                $response = $this->client->search($params);

                if (!isset($response['hits']['total']['value']) || $response['hits']['total']['value'] === 0) {
                    return array();
                }else{
                    return $response['hits']['hits'];
                }

            } catch (Exception $e) {
                echo "Error " . $e->getMessage() . "\n";
            }
        }

        public function filter($index, $filters)
        {
        	try{
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
            }
            
                $response = $this->client->search($params);

                if (!isset($response['hits']['total']['value']) || $response['hits']['total']['value'] === 0) {
                    return array();
                }else{
                    return $response['hits']['hits'];
                }

                } catch (Exception $e) {
                echo "Error " . $e->getMessage() . "\n";
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
