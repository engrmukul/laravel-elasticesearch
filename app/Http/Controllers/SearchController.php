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

        $result = $this->elasticsearch->filter('rzy_properties',  $filters);

        if(!empty($result)){
            return response()->json($result);
        }else{
            return response()->json(['errors' => ['message' => ['Data not found.']]], 400);
        }
    }



}
