<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ElasticsearchService;

class SearchController extends Controller
{
   protected $elasticsearch;

    public function __construct(ElasticsearchService $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }

    public function autocomplete()
    {
        //MERGE MRT station, city, 

        $query = trim(request()->get('query'));

        $rzyProperties = $this->elasticsearch->autocomplete('rzy_properties', 'product_name', $query);
        $rzyCityList = $this->elasticsearch->autocomplete('rzy_city_list', 'city', $query);
        $rzyMrtList = $this->elasticsearch->autocomplete('rzy_mrt_list', 'BUILDING', $query);
        
        //dd($rzyProperties);

        $rzyPropertySugessionArray = [];
        $rzyCitySugessionArray = [];
        $rzyMrtSugessionArray = [];

        if(!empty($rzyProperties)){
            foreach ($rzyProperties as $key => $value) {
                $propertySugession['name'] = $value['_source']['product_name'];
                $propertySugession['address'] = $value['_source']['property_address'];
                $propertySugession['type'] = $value['_source']['subcategory'];

                $rzyPropertySugessionArray[] = $propertySugession;
            }
        }

         if(!empty($rzyCityList)){
            foreach ($rzyCityList as $key => $value) {
                $citySugession['name'] = $value['_source']['city'];
                $citySugession['address'] = '';
                $citySugession['type'] = 'City';

                $rzyCitySugessionArray[] = $citySugession;
            }
        }


         if(!empty($rzyMrtList)){
            foreach ($rzyMrtList as $key => $value) {
                $mrtSugession['name'] = $value['_source']['BUILDING'];
                $mrtSugession['address'] = $value['_source']['ADDRESS'];
                $mrtSugession['type'] = 'MRT Station';

                $rzyMrtSugessionArray[] = $mrtSugession;
            }
        }


        $suggestions = array_merge($rzyPropertySugessionArray, $rzyCitySugessionArray, $rzyMrtSugessionArray);



        if(!empty($suggestions)){
            return response()->json($suggestions);
        }else{
            return response()->json(['errors' => ['message' => ['Data not found.']]], 400);
        }

        
    }

    public function search()
    {
        $query = trim(request()->get('query'));
        $currentPage = trim(request()->get('cp'));
        $pageSize = trim(request()->get('ps'));

        $result = $this->elasticsearch->search('rzy_properties', $query);

        if(!empty($result)){
            return response()->json($result);
        }else{
            return response()->json(['errors' => ['message' => ['Data not found.']]], 400);
        }
    }

    public function filter(Request $request)
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

        $result = $this->elasticsearch->filter('rzy_properties',  $filters, $currentPage, $pageSize);

        if(!empty($result)){
            return response()->json($result);
        }else{
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

        $result = $this->elasticsearch->propertyDocumentSave('rzy_properties', 10, $productData);

        dd($result);

        if(!empty($result)){
            return response()->json($result);
        }else{
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

        $result = $this->elasticsearch->propertyDocumentSave('rzy_properties', 10, $productUpdateData);

        dd($result);

        if(!empty($result)){
            return response()->json($result);
        }else{
            return response()->json(['errors' => ['message' => ['Data not found.']]], 400);
        }
    }

    public function propertyDocumentDelete()
    {
        $result = $this->elasticsearch->propertyDocumentDelete('rzy_properties', 10);

        if(!empty($result)){
            return response()->json(['message'=> 'Document deleted successfully!']);
        }else{
            return response()->json(['errors' => 'Document not deleted'], 400);
        }
    }

}
