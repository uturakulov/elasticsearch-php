<?php

namespace Alifuz\AlifSearch;

use Alifuz\AlifSearch\DTO\SearchResultDTO;
use Alifuz\AlifSearch\Enums\SearchTypesEnum;
use Alifuz\Utils\Exceptions\ParseException;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;

class Alifsearch
{
    protected string $index;
    protected Client $httpClient;

    public function __construct()
    {
        $this->index = config('elasticsearch.index');
        $this->httpClient = ClientBuilder::create()
            ->setHosts([config('elasticsearch.host')])
            ->setBasicAuthentication('admin', 'admin')
            ->setSSLVerification(false)
            ->build();
    }

    /**
     * Adds document to indexing.
     * NOTE: Pass an `id` within $data in order to make it changeable
     * Otherwise there will be duplicates in search result.
     * @param array $data - any data you want to add to index
     * @return array
     */
    public function createDocument(array $data): array
    {
        $params = [
            'index' => $this->index,
            'id' => $data['id'],
            'body' => $data,
            'timeout' => '10s',
        ];

        $response = $this->httpClient->index($params);

        return $response; // $response['_shards']['failed'] == 0 ? 'nice' : 'throw error'
    }

    /**
     * Updates indexed document
     * NOTE: this method uses same route as creating index.
     * Be sure to pass existing `id` of document, otherwise there
     * will be new document which may cause duplicates in search result.
     * @param array $data
     * @return void
     */
    public function updateDocument(array $data, bool $upsert = false): void
    {
        $params = [
            'index' => $this->index,
            'id' => $data['id'],
            'timeout' => '10s',
        ];

        foreach ($data as $key => $field) {
            if ($upsert) {
                $params['body'] = [
                    'script' => [
                        'source' => 'ctx._source.' . $key . '=' . $key,
                        'params' => [$key => $field],
                    ],
                    'upsert' => [$key => $field],
                ];
            } else {
                $params['body'] = [
                    'script' => [
                        'source' => 'ctx._source.' . $key . '=' . $key,
                        'params' => [$key => $field],
                    ],
                ];
            }

            $this->httpClient->update($params);
        }
    }

    /**
     * Updates indexed document
     * NOTE: this method uses same route as creating index.
     * Be sure to pass existing `id` of document, otherwise there
     * will be new document which may cause duplicates in search result.
     * @param array $data
     * @return void
     */
    public function updateDocumentCredits(array $data): void
    {
        $params = [
            'index' => $this->index,
            'id' => $data['id'],
            'body' => [
                'script' => [
                    'source' => 'ctx._source.credits=params.credits',
                    'params' => ['credits' => $data],
                ],
            ],
            'timeout' => '10s',
        ];

        $this->httpClient->update($params);
    }

    /**
     * Deletes index from ElasticSearch.
     *
     * @param array $data
     * @return array
     */
    public function deleteDocument(array $data): array
    {
        $params = [
            'index' => $this->index,
            'id' => $data['id'],
        ];

        $response = $this->httpClient->delete($params);

        return $response; // $response['_shards']['failed'] == 0 ? 'nice' : 'throw error'
    }

    /**
     * Bulk create indexed documents.
     *
     * @param array $documents
     * @return array
     */
    public function bulkCreate(array $documents): array
    {
        $params = [];
        foreach ($documents as $document) {
            $params['body'][] = [
                'index' => [
                    '_index' => config('local_services.elasticsearch.index'),
                    '_id' => $document['id'],
                ],
            ];

            $params['body'][] = $document;
        }

        $response = $this->httpClient->bulk($params);

        return $response; // $response['_shards']['failed'] == 0 ? 'nice' : 'throw error'
    }

    /**
     * @throws ParseException
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function search(string $query, ?string $type): SearchResultDTO
    {
        return match ($type) {
            SearchTypesEnum::PASSPORT()->getValue() => $this->searchByPassport($query),
            SearchTypesEnum::FIO()->getValue() => $this->searchByName($query),
            SearchTypesEnum::PHONE()->getValue() => $this->searchByPhone($query),
            SearchTypesEnum::PINFL()->getValue() => $this->searchByPinfl($query),
            SearchTypesEnum::CONTRACT_NUMBER()->getValue() => $this->searchByContractNumber($query),
            default => $this->searchGeneral($query),
        };
    }

    /**
     * @throws ParseException
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    protected function searchByPhone(string $query): SearchResultDTO
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'query' => [
                    'match' => [
                        'phone' => [
                            'query' => $query,
                            'analyzer' => 'regex_analyzer',
                            'fuzziness' => 1,
                        ],
                    ],
                ],
                'size' => 50,
            ],
        ];

        $result = $this->httpClient->search($params)['hits']['hits'];

        return SearchResultDTO::fromArray($result);
    }

    /**
     * @throws ParseException
     * @throws ServerResponseException
     * @throws ClientResponseException
     */
    protected function searchByPassport(string $query): SearchResultDTO
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'query' => [
                    'match' => [
                        'passport_id' => [
                            'query' => $query,
                            'analyzer' => 'regex_analyzer',
                        ],
                    ],
                ],
                'size' => 50,
            ],
        ];

        $result = $this->httpClient->search($params)['hits']['hits'];

        return SearchResultDTO::fromArray($result);
    }

    /**
     * @throws ParseException
     * @throws ServerResponseException
     * @throws ClientResponseException
     */
    protected function searchByPinfl(string $query): SearchResultDTO
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'query' => [
                    'match' => [
                        'pinfl' => [
                            'query' => $query,
                            'analyzer' => 'regex_analyzer',
                        ],
                    ],
                ],
                'size' => 50,
            ],
        ];

        $result = $this->httpClient->search($params)['hits']['hits'];

        return SearchResultDTO::fromArray($result);
    }

    /**
     * @throws ParseException
     * @throws ServerResponseException
     * @throws ClientResponseException
     */
    protected function searchByName(string $query): SearchResultDTO
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => [
                            'name',
                            'patronymic',
                            'surname',
                            'full_name',
                        ],
                        'analyzer' => 'standard',
                        'fuzziness' => 1,
                        'minimum_should_match' => 2,
                    ],
                ],
                'size' => 50,
            ],
        ];

        $result = $this->httpClient->search($params)['hits']['hits'];

        return SearchResultDTO::fromArray($result);
    }

    /**
     * @throws ParseException
     * @throws ServerResponseException
     * @throws ClientResponseException
     */
    protected function searchByContractNumber(string $query): SearchResultDTO
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'query' => [
                    'nested' => [
                        'path' => 'credits',
                        'query' => [
                            'match' => [
                                'credits.contract_number' => $query,
                            ],
                        ],
                    ],
                ],
                'size' => 50,
            ],
        ];

        $result = $this->httpClient->search($params)['hits']['hits'];

        return SearchResultDTO::fromArray($result);
    }

    /**
     * @throws ParseException
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    protected function searchGeneral(string $query): SearchResultDTO
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => [
                            'name',
                            'patronymic',
                            'surname',
                            'pinfl',
                            'passport_id',
                            'phone',
                            'full_name^2',
                        ],
                        'analyzer' => 'standard',
                        'fuzziness' => 2,
                        'minimum_should_match' => 2,
                    ],
                ],
                'size' => 50,
            ],
        ];

        $result = $this->httpClient->search($params)['hits']['hits'];

        return SearchResultDTO::fromArray($result);
    }
}
