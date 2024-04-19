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

    public function search(Request $request)
    {
        $query = $request->input('query');

        $result = $this->elasticsearch->search('rzy_properties', 'your_autocomplete_field', $query);

        return response()->json($result);
    }

    public function filter(Request $request)
    {
        //$query = $request->input('query');
	// data from request


	$filters = [
        	['range' => ['timestamp' => ['gte' => '2022-01-01', 'lte' => '2022-12-31']]],
        	['term' => ['status' => 'active']]
    	];

        $result = $this->elasticsearch->filter('rzy_properties',  $filters);

        return response()->json($result);
    }


    public function autocomplete(Request $request)
    {
        $query = $request->input('query');

        $suggestions = $this->elasticsearch->autocomplete('your_index_name', 'your_autocomplete_field', $query);

        return response()->json($suggestions);
    }
}
