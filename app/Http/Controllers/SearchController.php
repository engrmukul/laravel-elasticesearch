<?php

namespace App\Http\Controllers;

use App\Services\ElasticsearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SearchController extends Controller
{
    protected $elasticsearch;

    public function __construct(ElasticsearchService $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }

    public function autocomplete(): JsonResponse
    {
        //MERGE MRT station, city,

        $query = trim(request()->get('query'));

        $rzyPropertieFields = ['product_name','postal_code'];
        $rzyDistrictFields = ['title'];
        $rzyMrtFields = ['BUILDING','POSTAL'];

        $rzyProperties = $this->elasticsearch->autocomplete('rzy_properties', $rzyPropertieFields, $query);
        $rzyCityList = $this->elasticsearch->autocomplete('rzy_district_list', $rzyDistrictFields, $query);
        $rzyMrtList = $this->elasticsearch->autocomplete('rzy_mrt_list', $rzyMrtFields, $query);

        //dd($rzyProperties);

        $rzyPropertySugessionArray = [];
        $rzyCitySugessionArray = [];
        $rzyMrtSugessionArray = [];

        if (!empty($rzyProperties)) {
            foreach ($rzyProperties as $key => $value) {
                $propertySugession['name'] = $value['_source']['product_name'];
                $propertySugession['address'] = $value['_source']['property_address'];
                $propertySugession['type'] = $value['_source']['subcategory'];

                $rzyPropertySugessionArray[] = $propertySugession;
            }
        }

        if (!empty($rzyCityList)) {
            foreach ($rzyCityList as $key => $value) {
                $citySugession['name'] = $value['_source']['city'];
                $citySugession['address'] = '';
                $citySugession['type'] = 'District';

                $rzyCitySugessionArray[] = $citySugession;
            }
        }


        if (!empty($rzyMrtList)) {
            foreach ($rzyMrtList as $key => $value) {
                $mrtSugession['name'] = $value['_source']['BUILDING'];
                $mrtSugession['address'] = $value['_source']['ADDRESS'];
                $mrtSugession['type'] = 'MRT Station';

                $rzyMrtSugessionArray[] = $mrtSugession;
            }
        }


        $suggestions = array_merge($rzyPropertySugessionArray, $rzyCitySugessionArray, $rzyMrtSugessionArray);


        if (!empty($suggestions)) {
            return response()->json($suggestions);
        } else {
            return response()->json(['errors' => ['message' => ['Data not found.']]], 400);
        }


    }

    public function search(): JsonResponse
    {
        $query = trim(request()->get('query'));
        $currentPage = trim(request()->get('cp'));
        $pageSize = trim(request()->get('ps'));

        $result = $this->elasticsearch->search('rzy_properties', $query, $currentPage, $pageSize);

        if (!empty($result)) {
            return response()->json($result);
        } else {
            return response()->json(['errors' => ['message' => ['Data not found.']]], 400);
        }
    }

    public function filter(Request $request): JsonResponse
    {
        $query = $request->input('query');
        $currentPage = trim($request->input('cp'));
        $pageSize = trim($request->input('ps'));

        $filters = [
            "bathroom" => trim($query->bathroom),
            "bedroom" => trim($query->bedroom),
            "district" => trim($query->district),
            "furnishing" => trim($query->district),
            "hdb" => trim($query->hdb),
            "price_start" => trim($query->price_start),
            "price_end" => trim($query->price_end),
            "sub_category" => trim($query->sub_category),
            "rental_type" => trim($query->rental_type),
            "unit_area" => trim($query->unit_area),
        ];

        $result = $this->elasticsearch->filter('rzy_properties', $filters, $currentPage, $pageSize);

        if (!empty($result)) {
            return response()->json($result);
        } else {
            return response()->json(['errors' => ['message' => ['Data not found.']]], 400);
        }
    }

    public function propertyDocumentSave()
    {
        $productData = [
            "id" => 10,
            "address" => "Singapore",
            "property_address" => "Dhaka Bangladesh",
            "city" => "Dhaka",
            "category" => "Residential Pro",
            "description" => "",
            "document" => "[]",
            "fileds" => null,
            "images" => "[]",
            "cover_image_url" => "https://rzylivebucket.s3.ap-southeast-1.amazonaws.com/63a0021052c2d.jpg",
            "min_booking_amount" => null,
            "mobile_no" => "+8806587481245",
            "name" => "undefined",
            "price" => "1400",
            "price_unit" => "Month",
            "product_name" => "MRT Condo Master 7 Bedroom",
            "subcategory" => "Condo",
            "rental_type" => "Whole Unit",
            "unit_type" => "",
            "bedroom" => "",
            "bathroom" => "",
            "floor_size" => "",
            "build_year" => "2023",
            "floor_level" => "null",
            "furnishing" => "null",
            "rent_term" => "null",
            "property_estate" => " ",
            "permit_gender" => "",
            "keyword" => null,
            "adder_role" => null,
            "country" => "",
            "price_negotiable" => null,
            "state" => null,
            "postal_code" => "555321",
            "street_name" => null,
            "house_no" => null,
            "street_no" => null,
            "pax_number" => null,
            "parking_vehicle_num" => null,
            "property_city" => "D19 Hougang / Punggol / Sengkang",
            "facing" => "null",
            "listing_purpose" => null,
            "latitude" => "1.357065",
            "longitude" => "103.861910",
            "hdb" => " ",
            "mrt" => null,
            "property_emenity" => [
            ],
            "property_facility" => [
            ],
            "room_facility" => [
            ],
            "available_from" => "2024-04-15",
            "upload_title" => null,
            "image_list" => "[]",
            "unit_number" => "2"
        ];

        $result = $this->elasticsearch->elastiDocumentSave('rzy_properties', 10, $productData);

        if (!empty($result)) {
            return response()->json($result);
        } else {
            return response()->json(['errors' => ['message' => ['Data not found.']]], 400);
        }
    }

    public function propertyDocumentUpdate()
    {
        $productUpdateData = [
            "price" => "1400",
            "latitude" => "1.357065",
            "longitude" => "103.861910",
            "unit_number" => "2"
        ];

        $result = $this->elasticsearch->elasticDocumentUpdate('rzy_properties', 10, $productUpdateData);

        if (!empty($result)) {
            return response()->json($result);
        } else {
            return response()->json(['errors' => ['message' => ['Data not found.']]], 400);
        }
    }

    public function propertyDocumentDelete(): JsonResponse
    {
        $result = $this->elasticsearch->elasticDocumentDelete('rzy_properties', 10);

        if (!empty($result)) {
            return response()->json(['message' => 'Document deleted successfully!']);
        } else {
            return response()->json(['errors' => 'Document not deleted'], 400);
        }
    }

    public function prepareIndexAndUploadMasterData(): JsonResponse
    {
        //DELETE IF EXIST INDICES
        $this->elasticsearch->deleteElasticIndex(['rzy_properties', 'rzy_district_list', 'rzy_mrt_list']);

        //CREATE PROPERTY INDEX
        $productMapping = [
          'mappings' => [
            'properties' => [
              'property_name' => [
                'type' => 'text',
                'analyzer' => 'autocomplete_analyzer',
              ],
              'postal_code' => [
                'type' => 'text',
                'analyzer' => 'autocomplete_analyzer',
              ],
            ],
          ],
          'settings' => [
            'analysis' => [
              'analyzer' => [
                'autocomplete_analyzer' => [
                  'type' => 'custom',
                  'tokenizer' => 'autocomplete',
                  'filter' => ['lowercase'],
                ],
              ],
              'tokenizer' => [
                'autocomplete' => [
                  'type' => 'edge_ngram',
                  'min_gram' => 2,
                  'max_gram' => 10,
                  'token_chars' => ['letter'], ], ], ], ], ];


        $this->elasticsearch->createElasticIndex('rzy_properties', $productMapping);

        //CREATE DISTRICT INDEX
        $districtMapping = [
          'mappings' => [
            'properties' => [
              'title' => [
                'type' => 'text',
                'analyzer' => 'autocomplete_analyzer',
              ],
            ],
          ],
          'settings' => [
            'analysis' => [
              'analyzer' => [
                'autocomplete_analyzer' => [
                  'type' => 'custom',
                  'tokenizer' => 'autocomplete',
                  'filter' => ['lowercase'],
                ],
              ],
              'tokenizer' => [
                'autocomplete' => [
                  'type' => 'edge_ngram',
                  'min_gram' => 2,
                  'max_gram' => 10,
                  'token_chars' => ['letter'],
                ],
              ],
            ],
          ],
        ];

        $this->elasticsearch->createElasticIndex('rzy_district_list', $districtMapping);

        //CREATE MRT INDEX
        $mrtMapping = [
            'mappings' => [
            'properties' => [
              'BUILDING' => [
                'type' => 'text',
                'analyzer' => 'autocomplete_analyzer',
              ],
              'POSTAL' => [
                'type' => 'text',
                'analyzer' => 'autocomplete_analyzer',
              ],
              'ADDRESS' => [
                'type' => 'text',
                'analyzer' => 'autocomplete_analyzer',
              ],
              'ROAD_NAME' => [
                'type' => 'text',
                'analyzer' => 'autocomplete_analyzer',
              ],
            ],
          ],
          'settings' => [
            'analysis' => [
              'analyzer' => [
                'autocomplete_analyzer' => [
                  'type' => 'custom',
                  'tokenizer' => 'autocomplete',
                  'filter' => ['lowercase'],
                ],
              ],
              'tokenizer' => [
                'autocomplete' => [
                  'type' => 'edge_ngram',
                  'min_gram' => 2,
                  'max_gram' => 10,
                  'token_chars' => ['letter'],
                ],
              ],
            ],
          ],
        ];

        $this->elasticsearch->createElasticIndex('rzy_mrt_list', $mrtMapping);

        //BULK SAVE DISTRICT DATA
        $districtJsonData = Storage::disk('local')->get('resources/json/districts.json');
        $districtData = json_decode($districtJsonData, true);
        $this->elasticsearch->bulkSave('rzy_district_list', $districtData);

        //BULK SAVE MRT DATA
        $mrtJsonData = Storage::disk('local')->get('resources/json/mrt.json');
        $mrtData = json_decode($mrtJsonData, true);
        $this->elasticsearch->bulkSave('rzy_mrt_list', $mrtData);

        return response()->json(['message' => 'Indices and data prepare successfully!']);
    }

}
