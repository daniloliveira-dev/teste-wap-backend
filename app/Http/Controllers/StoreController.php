<?php

namespace App\Http\Controllers;

use App\Traits\ExternalServices;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    use ExternalServices;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $getAll = DB::table('stores')
            ->join('addresses', 'addresses.store_id', '=', 'stores.id')
            ->get()->all();
        if ($getAll == true) {
            return response()->json([
                'data' => $getAll
            ]);
        }
        return response()->json([
            'message' => 'Dont have any results'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $findName = Store::where('name', $request->name)->get()->all();
        if ($findName == true) {
            return response()->json([
                'message' => "Name in use, try other name"
            ], 400);
        } else {

            $validation = Validator::make(
                $request->all(),
                [
                    "name" => 'required',
                    "postal_code" => "required|max:8|min:8"
                ],
                [
                    "required.message" => "Field 'name' can't be blank",
                    "required.postal_code" => "Field 'postal_code' can't be blank",
                    "max.postal_code" => "The field must have a maximum of 8 characters",
                    "min.postal_code" => "The field must have at least 8 characters"
                ]
            );
            if ($validation->fails()) {
                return response()->json([
                    "error" => $validation->messages()
                ], 400);
            } else {
                $create = Store::create($validation->validated());
                if ($create == true) {
                    $findAddress = $this->viaCep('get', "$request->postal_code/json/");
                    if (isset($findAddress->cep)) {

                        $insertAddress = DB::table('addresses')->insert([
                            "foreign_table" => "stores",
                            "store_id" => $create->id,
                            'postal_code' => $request->postal_code,
                            'state' => $findAddress->uf,
                            'city' => $findAddress->localidade,
                            'sublocality' => $findAddress->bairro,
                            'street' => $findAddress->logradouro,
                            'street_number' => $findAddress->unidade,
                            'complement' => $findAddress->complemento,
                        ]);
                        if ($insertAddress == true) {
                            return response()->json([
                                'message' => "Created by Succesfully"
                            ], 200);
                        }
                    } else {
                        return response()->json([
                            'message' => "Postal code not found"
                        ], 400);
                    }
                }
            }
        }
        return response()->json([
            'message' => "There was an error trying to run the API, please try again later"
        ], 400);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $findStore = DB::table('stores')->join('addresses', 'addresses.store_id', '=', 'stores.id')->where('stores.id', $id)->get()->all();
        if ($findStore != []) {
            return response()->json([
                'data' => $findStore
            ]);
        } else {
            return response()->json([
                'message' => "Store not found"
            ], 200);
        }
        return response()->json([
            'error' => "Try again another time"
        ], 400);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $findStore = Store::findOrFail($id);
        if ($findStore == true) {
            $findByName = Store::where('name', $request->name)->get()->all();
            if ($findByName == []) {
                $update = $findStore->update(["name" => $request->name]);
                if ($update == true) {
                    return response()->json([
                        'message' => "Updated by Succesfully"
                    ], 200);
                }
            } else {
                return response()->json([
                    'message' => "Name in use, try another name"
                ], 200);
            }
        } else {
            return response()->json([
                'message' => "Store not found, try again"
            ], 200);
        }
        return response()->json([
            "error" => 'Fail, try again'
        ], 400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $findStore = Store::find($id);
        if ($findStore == true) {
            $findStore->delete();
            return response()->json([
                'message' => 'Deleted by Succesfully'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Store not found'
            ], 200);
        }
        return response()->json([
            'error' => 'Try again another time'
        ], 400);
    }
}
