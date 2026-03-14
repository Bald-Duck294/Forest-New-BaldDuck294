<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ActivityLog;

class CompanyRegisterController extends Controller
{
   
    

    public function createStep1()
    {
        //dd('hii');
        //$product = $request->session()->get('product');
        return view('admin.companyregister.createStep1');
    }

    /**
     * Post Request to store step1 info in session
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postCreateStep1(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required|unique:products',
            'amount' => 'required|numeric',
            'company' => 'required',
            'available' => 'required',
            'description' => 'required',
        ]);

        if (empty($request->session()->get('product'))) {
            $product = new Product();
            $product->fill($validatedData);
            $request->session()->put('product', $product);
        } else {
            $product = $request->session()->get('product');
            $product->fill($validatedData);
            $request->session()->put('product', $product);
        }

        return redirect('/products/create-step2');
    }
}
