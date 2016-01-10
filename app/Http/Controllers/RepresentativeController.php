<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//use App\Http\Requests;
use App\Http\Controllers\Controller;
use GuzzleHttp\Promise;

use App\Representative;
use App\Providers\IPInfo\IPInfo;

use GoogleAPI;
use CongressAPI;
use StateAPI;

class RepresentativeController extends Controller
{

    public function view()
    {
        return view('pages.home');
    }

    public function index(Request $request)
    {
        $data = ['reps' => []];
        $ip = $request->ip();

        if ($ip == '192.168.10.1') $ip = '73.157.212.42';

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)){
            $data['location'] = IPInfo::getLocation($ip);
        }

        return view('pages.home', $data);
    }


    public function jsonDistrict($state, $district)
    {
        $googReq = GoogleAPI::districtAsync($state, $district);
        $congReq = CongressAPI::districtAsync($state, $district);
        $results = Promise\unwrap([$googReq, $congReq]);

        if (isset($results[0]->status) && $results[0]->status == 'error'){
            return response()->json($results[0]);
        }

        $resp = $this->buildResponse($results[0], $results[1]);

        return response()->json($resp);
    }

    public function jsonZipcode($zipcode)
    {
        $googReq = GoogleAPI::async($zipcode);
        $congReq = CongressAPI::asyncLocate('zip='.$zipcode);

        $results = Promise\unwrap([$googReq, $congReq]);

        if (isset($results[0]->error)){
            return response()->json($results[0]);
        }

        $resp = $this->buildResponse($results[0], $results[1]);

        return response()->json($resp);
    }

    public function jsonGPS($lat, $lng)
    {
        $googReq = GoogleAPI::async($lat.','.$lng);
        $congReq = CongressAPI::asyncLocate('latitude='.$lat.'&longitude='.$lng);

        $results = Promise\unwrap([$googReq, $congReq]);

        if (isset($results[0]->error)){
            return response()->json($results[0]);
        }

        $resp = $this->buildResponse($results[0], $results[1]);
        return response()->json($resp);
    }

    public function buildResponse($google, $congress)
    {
        $resp = ['reps' => []];

        if (isset($google['location'])){
            $resp['location'] = $google['location'];
        }

        $congressNames = array_map(function($i){
            return $i->aliases;
        }, $congress);

        foreach($google['reps'] as $gdata){
            $rep = new Representative($gdata);
            $c = count($congressNames);
            for ($i = 0; $i < $c; $i++){
                if (array_search($rep->name, $congressNames[$i]) !== false){
                    $rep->load($congress[$i]);
                    unset($congress[$i]);
                    unset($congressNames[$i]);
                }
            }
            $resp['reps'][] = $rep;
            $congressNames = array_values($congressNames);
            $congress = array_values($congress);
        }

        foreach($congress as $cdata){
            $resp['reps'][] = new Representative($cdata);
        }

        $ranks = [
            'President',
            'Vice-President',
            'Senate',
            'House of Representatives',
            'Governor',
            'Mayor'
        ];

        usort($resp['reps'], function($a, $b) use ($ranks){
            $ia = array_search($a->office, $ranks);
            $ib = array_search($b->office, $ranks);

            if ($ia === false) $ia = 6;
            if ($ib === false) $ib = 6;

            return $ia > $ib;
        });

        return $resp;
    }
}
