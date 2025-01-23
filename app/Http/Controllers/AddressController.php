<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Store;
use App\Traits\ExternalServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    use ExternalServices;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $getAll = DB::table('addresses')
            ->selectRaw(
                "
        id,
        CONCAT(SUBSTRING(postal_code, 1, 5), '-', SUBSTRING(postal_code, 6, 3)) AS postal_code, 
        state, 
        city, 
        sublocality, 
        street, 
        street_number, 
        complement
        "
            )->get()->all();
        if ($getAll == true) {
            return response()->json([
                'data' => $getAll
            ]);
        } else {
            return response()->json([
                'error' => "Dont have any results"
            ], 200);
        }

        return response()->json([
            'error' => "There was an error trying to fetch the information"
        ], 400);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $findPostalCode = Address::where('postal_code', $request->postal_code)->get()->all();
        if ($findPostalCode == true) {

            return response()->json([
                'error' => 'Postal Code in use, please try other postal code'
            ], 400);
        }

        $validation = Validator::make(
            $request->all(),
            [
                "postal_code" => "required",
                "street_number" => "numeric"
            ],
            [
                "postal_code.required" => "Field 'Postal Code' can't be blank",
                "street_number.numeric" => "the street number field must be a number"
            ]
        );

        if ($validation->fails()) {

            return response()->json([
                'error' => $validation->messages()
            ], 400);
        } else {

            $findAddressByRv = $this->republicaVirtual('get', [
                'cep' => $request->postal_code,
                'formato' => 'json'
            ]);

            if (isset($findAddressByRv->resultado) && $findAddressByRv->resultado == 1) {

                $storeName = substr($request->postal_code, 0, -4);
                $createStoreForAddress = Store::create([
                    'name' => "Store $storeName"
                ])->id();

                $createAddress = Address::create([
                    'foreign_table' => 'stores',
                    'store_id' => $createStoreForAddress,
                    'postal_code' => $request->postal_code,
                    'state' => $findAddressByRv->uf,
                    'city' => $findAddressByRv->cidade,
                    'sublocality' => $findAddressByRv->bairro,
                    'street' => $findAddressByRv->logradouro,
                    'street_number' => $request->street_number,
                    'complement' => $request->complement != '' ? $request->complement : ''
                ]);
                if ($createAddress == true) {
                    return response()->json([
                        'message' => 'Created by Succesfully'
                    ], 200);
                }
            } else {

                $findAddressByViacep = $this->viaCep('get', "$request->postal_code/json/");
                if (isset($findAddressByViacep->ibge)) {

                    $createStoreForAddress = Store::create([
                        'name' => "Store $findAddressByViacep->siafi"
                    ]);

                    if ($createStoreForAddress == true) {
                        $createAddress = DB::table('addresses')->insert([
                            'foreign_table' => 'stores',
                            'store_id' => $createStoreForAddress->id,
                            'postal_code' => str_replace('-', '', $findAddressByViacep->cep),
                            'state' => $findAddressByViacep->uf,
                            'city' => $findAddressByViacep->localidade,
                            'sublocality' => $findAddressByViacep->bairro,
                            'street' => $findAddressByViacep->logradouro,
                            'street_number' => $findAddressByViacep->unidade != '' ? $findAddressByViacep->unidade : $request->street_number,
                            'complement' => $findAddressByViacep->complemento != '' ? $findAddressByViacep->complemento : $request->complement
                        ]);
                        if ($createAddress == true) {
                            return response()->json([
                                'message' => 'Created by Succesfully'
                            ], 200);
                        }
                    }
                } else {

                    return response()->json([
                        'message' => 'CEP Não encontrado'
                    ], 400);
                }
            }
        }
        return response()->json([
            'error' => 'There was an error trying to run the API, please try again later'
        ], 400);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $findAddress = DB::table('addresses')
            ->selectRaw(
                "
            id,
            CONCAT(SUBSTRING(postal_code, 1, 5), '-', SUBSTRING(postal_code, 6, 3)) AS postal_code, 
            state, 
            city, 
            sublocality, 
            street, 
            street_number, 
            complement"
            )->where('id', $id)->get()->all();
        if ($findAddress != []) {

            return response()->json([
                'data' => $findAddress
            ]);
        } else {

            return response()->json([
                'error' => 'Address not found'
            ]);
        }

        return response()->json([
            'error' => 'There was an error trying to run the API, please try again later'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validation = Validator::make(
            $request->all(),
            [
                "postal_code" => "required|min:8|max:8",
                "street_number" => "required|numeric"
            ],
            [
                "postal_code.required" => "Field 'Postal Code' can't be blank",
                "postal_code.min" => "The 'Postal Code' field must have at least 8 characters",
                "postal_code.max" => "The 'Postal Code' field must not have a maximum of 8 characters",
                "street_number.required" => "Field 'Street Number' can't be blank",
                "street_number.numeric" => "the street number field must be a number"
            ]
        );

        if ($validation->fails()) {

            return response()->json([
                'error' => $validation->messages()
            ], 400);
        } else {
            $findAddress = Address::findOrFail($id)->getAttributes();
            if (isset($request) && $request->postal_code != $findAddress['postal_code']) {

                $findAddressByRv = $this->republicaVirtual('get', [
                    'cep' => $request->postal_code,
                    'formato' => 'json'
                ]);

                if (isset($findAddressByRv->resultado) && $findAddressByRv->resultado == 1) {

                    $createNewAddress = DB::table('addresses')->insert([
                        'foreign_table' => 'stores',
                        'store_id' => $findAddress['store_id'],
                        'postal_code' => $request->postal_code ? $request->postal_code : '',
                        'state' => $findAddressByRv->uf,
                        'city' => $findAddressByRv->cidade,
                        'sublocality' => $findAddressByRv->bairro,
                        'street' => $findAddressByRv->logradouro,
                        'street_number' => $request->street_number ? $request->street_number : '',
                        'complement' => $request->complement ? $request->complement : ''
                    ]);
                    if ($createNewAddress == true) {
                        Address::where('id', $id)->delete();
                        return response()->json([
                            'message' => 'Updated by Succesfully'
                        ], 200);
                    }
                } else {

                    $findAddressByViacep = $this->viaCep('get', "$request->postal_code/json/");
                    if (isset($findAddressByViacep->ibge)) {

                        $createNewAddress = DB::table('addresses')->insert([
                            'foreign_table' => 'stores',
                            'store_id' => $findAddress['store_id'],
                            'postal_code' => str_replace('-', '', $findAddressByViacep->cep),
                            'state' => $findAddressByViacep->uf,
                            'city' => $findAddressByViacep->localidade,
                            'sublocality' => $findAddressByViacep->bairro,
                            'street' => $findAddressByViacep->logradouro,
                            'street_number' => $findAddressByViacep->unidade != '' ? $findAddressByViacep->unidade : $request->street_number,
                            'complement' => $findAddressByViacep->complemento != '' ? $findAddressByViacep->complemento : $request->complement
                        ]);
                        if ($createNewAddress == true) {
                            Address::where('id', $id)->delete();
                            return response()->json([
                                'message' => 'Updated by Succesfully'
                            ], 200);
                        }
                    } else {
                        return response()->json([
                            'message' => 'Postal Code not found'
                        ], 400);
                    }
                }
            }
        }
        return response()->json([
            'message' => 'There was an error trying to run the API, please try again later'
        ], 400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $findAddress = Address::find($id);
        if ($findAddress == true) {
            $findAddress->delete();
            return response()->json([
                'message' => 'Deleted by Succesfully'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Address not found'
            ], 200);
        }
        return response()->json([
            'error' => 'Try again another time'
        ], 400);
    }
}
