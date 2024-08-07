<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Plan;
use App\Constants\Status;

class PackageController extends Controller
{
    // Add functions here
    public function index(){
        $pageTitle = "Investment Plans";
        $packages = Package::orderBy('price', 'asc')->paginate(getPaginate());
        $plans = Plan::orderBy('price', 'asc')->get();
        $emptyMessage = "No Packages";

        return view('admin.package.index', compact('pageTitle', 'packages', 'emptyMessage', 'plans'));
    }

    public function save(Request $request){
        // dd($request->all());
        $this->validate($request, [
            'name'                  => 'required',
            'price'                 => 'required|numeric|min:0', 
            'bonus_price'           => 'required|numeric|min:0',
            'weekly_fee'            => 'required|numeric|min:0',
            // 'package_type'       => 'required',
            'max_income'            => 'required|numeric|min:0',
            'start_income'          => 'required|numeric|min:0',
            'rising_income'         => 'required|numeric',           
            // 'select_plan'        => 'required|integer',
            'description'           => 'required',
            'duration'              => 'required|integer',
            'select_unit'           => 'required'
        ]);

        $package = new Package();
        if($request->id){
            $package = Package::findOrFail($request->id);
        }

        $package_type = $request->rising_income == 0 ? STATUS::PACKAGE_TYPE_FIXED : STATUS::PACKAGE_TYPE_VARIABLE;

        // $plan = Plan::where('id', $request->select_plan)->first();

        $package->name              = $request->name;
        $package->price             = $request->price;
        $package->bonus_price       = $request->bonus_price;
        $package->type              = $package_type;
        $package->description       = $request->description;
        $package->duration          = $request->duration;
        $package->repeat_unit       = $request->select_unit;
        $package->max_income        = $request->max_income;
        $package->start_income      = $request->start_income;
        $package->rising_income     = $request->rising_income;
        $package->weekly_fee        = $request->weekly_fee;
        // $package->plan_id           = $plan->id;
        // $package->plan_name         = $plan->name;
        $package->save();

        $notify[] = ['success', 'Package saved successfully'];
        return back()->withNotify($notify);
    }

    public function delete($id){
        $package = Package::where('id', $id)->first();
        $package->delete();
        
        $notify[] = ['success', 'Package deleted successfully'];
        return back()->withNotify($notify);
    }

    public function status($id){
        return Package::changeStatus( $id);
    }
}
