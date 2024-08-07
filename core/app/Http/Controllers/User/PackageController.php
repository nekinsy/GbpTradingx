<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\PackageUser;
use App\Models\PackageTransaction;
use App\Models\Node;
use App\Models\User;
use App\Models\Deposit;
use App\Models\PackageIncomeUser;
use App\Constants\Status;
use App\Models\PackageInvite;
use App\Models\Transaction;
use App\Models\WeeklyIncome;
use Carbon\Carbon;

class PackageController extends Controller
{
    // Add some function here
    public function index()
    {
        $user = auth()->user();
        $user_nodes = Node::where('user_id', $user->id)->get();
        $plan_ids = $user_nodes->pluck('plan_id')->toArray();
        $packages = Package::where('status', STATUS::ENABLE)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc') // Add this line to order by id in descending order  
            ->paginate(getPaginate(10));
        foreach ($packages as $package) {
            $package_user = PackageUser::where('status', STATUS::PACKAGE_PURCHASED)->where('user_id', $user->id)->where('package_id', $package->id)->first();
            $package_income_user = PackageIncomeUser::where('status', STATUS::PACKAGE_PURCHASED)->where('user_id', $user->id)->where('package_id', $package->id)->first();
            if ($package_user) {
                if ($package_user->status == STATUS::PACKAGE_PURCHASED) {
                    $package->active = 1;
                    $package->today_income = $package_income_user->current_daily_income;
                    $package->total_income = $package_income_user->current_total_income;
                } else {
                    $package->active = 0;
                    $package->today_income = 0;
                    $package->total_income = 0;
                }
            } else {
                $package->active = 0;
                $package->today_income = 0;
                $package->total_income = 0;
            }
        }
        $pageTitle = "Investment Plans";
        return view($this->activeTemplate . 'user.package.index', compact('pageTitle', 'packages', 'user'));
    }

    public function history()
    {
        $user = auth()->user();

        $package_transactions = PackageTransaction::where('user_id', $user->id)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate(getPaginate(10));
        $pageTitle = 'Invest History';
        $emptyMessage = 'History is empty';
        return view($this->activeTemplate . 'user.package.history', compact('pageTitle', 'package_transactions', 'emptyMessage'));
    }

    public function purchase(Request $request)
    {
        // dd($request->all());
        $user = auth()->user();
        $request->validate([
            'package_id' => 'required',
            'balance' => 'required'
        ]);

        $package_id = $request->package_id;
        $user_id = $user->id;

        if ($user_id == 1) {
            $notify[] = ['error', `Admin don't need purchase plan. Please login another user`];
            return back()->withNotify($notify);
        }

        $package = Package::where('id', $package_id)->first();

        if (!$package) {
            $notify[] = ['error', `Package doesn't exist`];
            return back()->withNotify($notify);
        }

        if ($user->balance < $package->price) {
            $notify[] = ['error', 'Your balance is not enough'];
            return back()->withNotify($notify);
        }

        $user->balance -= $package->price;
        $user->weekly_paid = 0;
        $user->total_invest += $package->price;
        $user->save();

        $weekly_income = WeeklyIncome::where('user_id', $user->id)->first();
        if(!$weekly_income){
            $weekly_income = new WeeklyIncome();
            $weekly_income->user_id = $user->id;
            $weekly_income->save();
        }
        


        self::generate_package_users($package, $user);
        self::generate_package_transactions($package, $user, $user, $package->price, STATUS::PACKAGE_PURCHASED);    // 1 means purchase transaction
        self::generate_transactions($package, $user, $user, $package->price, STATUS::PACKAGE_PURCHASED);
        
        $ref_user = User::where('username', $user->ref_user)->first();
        $ref_user_packages = PackageUser::where('status', STATUS::PACKAGE_PURCHASED)->where('user_id', $ref_user->id)->orderBy('created_at', 'asc')->get();
        $ref_user_percent = 5;
        $ref_user_bonus = $package->price * $ref_user_percent / 100;
        $ref_user->balance += $ref_user_bonus;
        $ref_user->total_ref_com += $ref_user_bonus;
        $ref_user->save();

        self::generate_transactions($package, $ref_user, $user, $ref_user_bonus, STATUS::PACKAGE_INVITE_BONUS);
        self::generate_package_transactions($package, $ref_user, $user, $ref_user_bonus, STATUS::PACKAGE_INVITE_BONUS);


        if($ref_user_packages){
            foreach($ref_user_packages as $ref_user_package){
                $ref_user_income_package = PackageIncomeUser::where('status', STATUS::PACKAGE_PURCHASED)->where('user_id', $ref_user_package->user_id)->where('package_id', $ref_user_package->package_id)->first();
                $package_uncom_amount = $ref_user_income_package->max_income - $ref_user_income_package->current_total_income;
                if($ref_user_bonus >= $package_uncom_amount){
                    $ref_user_package->status = STATUS::PACKAGE_RELEASED;
                    $ref_user_package->save();
                    $ref_user_income_package->current_total_income = 0;
                    $ref_user_income_package->current_daily_income = 0;
                    $ref_user_income_package->status = STATUS::PACKAGE_RELEASED;
                    $ref_user_income_package->save();
                    $ref_user_bonus -= $package_uncom_amount;
                } else{
                    $ref_user_income_package->current_total_income += $ref_user_bonus;
                    $ref_user_income_package->save();
                    $ref_user_bonus = 0;
                }
                if($ref_user_bonus == 0) break;
            }
        }
        
        // self::generate_bonus_distributions($package, $user);

        $notify[] = ['success', 'Investment Plan purchased successfully'];
        return back()->withNotify($notify);
    }

    private function get_bonus_handle($user, $amount){

    }

    private function generate_package_users($package, $user)
    {
        $package_user = new PackageUser();
        $package_user->user_id = $user->id;
        $package_user->package_id = $package->id;
        $package_user->package_type = $package->type;
        $package_user->current_total_income = 0;
        $package_user->current_daily_income = $package->start_income * $package->price / 100;
        $package_user->max_income = $package->max_income;
        $package_user->rising_income = $package->rising_income * $package->price / 100;
        $package_user->weekly_fee = $package->weekly_fee;
        $package_user->status = STATUS::PACKAGE_PURCHASED;
        $package_user->save();

        $package_income_user = new PackageIncomeUser();
        $package_income_user->user_id = $user->id;
        $package_income_user->package_id = $package->id;
        $package_income_user->package_type = $package->type;
        $package_income_user->current_total_income = 0;
        $package_income_user->current_daily_income = $package->start_income * $package->price / 100;
        $package_income_user->max_income = $package->max_income;
        $package_income_user->rising_income = $package->rising_income * $package->price / 100;
        $package_income_user->weekly_fee = $package->weekly_fee;
        $package_income_user->status = STATUS::PACKAGE_PURCHASED;
        $package_income_user->save();
    }

    private function generate_package_transactions($package, $user, $sender, $amount, $mode)
    {
        $transaction = new PackageTransaction();
        $transaction->user_id = $user->id;
        $transaction->package_id = $package->id;
        $transaction->package_name = $package->name;
        $transaction->mode = $mode;
        $transaction->amount = $amount;
        $transaction->sender_id = $sender->id;
        $transaction->package_duration = $package->duration;
        $transaction->package_duration_unit = $package->repeat_unit;
        if ($mode == STATUS::PACKAGE_PURCHASED)      // purchase transaction
        {
            $transaction->remark = "Purchase Investment Plan";
            $transaction->details = "Purchased " . $package->name;
            $transaction->sender_id = $sender->id;
            $transaction->after_balance = $user->balance;
            $transaction->before_balance = $user->balance + $amount;
            $transaction->trx_type = '-';
        } else if ($mode == STATUS::PACKAGE_NETWORK_BONUS) {    // get Network Bonus
            $transaction->remark = "Get Network Invest bonus";
            $transaction->details = "Get Network bonus from " . $package->name;
            $transaction->after_balance = $user->balance;
            $transaction->before_balance = $user->balance - $amount;
            $transaction->trx_type = '+';
        } else if ($mode == STATUS::PACKAGE_INVITE_BONUS) {       //Get Invite Bonus
            $transaction->remark = "Get Invite bonus";
            $transaction->details = "Get Invite bonus from " . $package->name;
            $transaction->after_balance = $user->balance;
            $transaction->before_balance = $user->balance - $amount;
            $transaction->trx_type = '+';
        } else if ($mode == STATUS::PACKAGE_RELEASED) {
            $transaction->remark = "Get Profit from Invest";
            $transaction->details = "Get Profit from " . $package->name;
            $transaction->after_balance = $user->balance;
            $transaction->before_balance = $user->balance - $amount;
            $transaction->trx_type = '+';
        } else if ($mode == STATUS::PACKAGE_GET_DAILY_INCOME) {
            $transaction->remark = "Get Daily Income";
            $transaction->details = "Get Daily Income from " . $package->name;
            $transaction->after_balance = $user->balance;
            $transaction->before_balance = $user->balance - $amount;
        } else if($mode == STATUS::PACKAGE_CONTRIBUTE_NETWORK_BONUS){
            $transaction->after_balance = $user->balance;
            $transaction->before_balance = $user->balance + $amount;
            $transaction->remark = "Contribute Network Bonus";
            $transaction->details = "Contribute Network Bonus in " . $package->name;
        }
        $transaction->save();
    }

    public function generate_transactions($package, $user, $sender, $amount, $mode)
    {
        $trx = getTrx();
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->sender_id = $sender->id;
        $transaction->plan_id = $package->plan_id;
        $transaction->amount = $amount;
        $transaction->charge = $amount;
        $transaction->trx = $trx;
        $transaction->post_balance = $user->balance;
        $transaction->deducted_balance = $user->deducted_balance;
        if ($mode == STATUS::PACKAGE_PURCHASED) {
            $transaction->trx_type = '-';
            $transaction->remark = "Purchase Investment Plan";
            $transaction->details = "Purchased " . $package->name;
        } else if ($mode == STATUS::PACKAGE_NETWORK_BONUS) {
            $transaction->trx_type = '+';
            $transaction->remark = "Get Network Invest bonus";
            $transaction->details = "Get Network bonus from " . $package->name;
        } else if ($mode == STATUS::PACKAGE_INVITE_BONUS) {
            $transaction->trx_type = '+';
            $transaction->remark = "Get Invite bonus";
            $transaction->details = "Get Invite bonus from " . $package->name;
        } else if ($mode == STATUS::PACKAGE_RELEASED) {
            $transaction->trx_type = '+';
            $transaction->remark = "Get Profit from Invest";
            $transaction->details = "Get Profit from " . $package->name;
        } else if ($mode == STATUS::PACKAGE_GET_DAILY_INCOME) {
            $transaction->trx_type = '+';
            $transaction->remark = "Get Daily Income";
            $transaction->details = "Get Daily Income from " . $package->name;
        } else if($mode == STATUS::PACKAGE_CONTRIBUTE_NETWORK_BONUS){
            $transaction->trx_type = '-';
            $transaction->remark = "Contribute Network Bonus";
            $transaction->details = "Contribute Network Bonus in " . $package->name;
        }
        $transaction->save();
    }

    private function generate_bonus_distributions($package, $user)
    {
        $percentage = [20, 20, 20, 20, 20];

        $depth = 5;
        $ref_username = $user->ref_user;
        $ref_user = User::where('username', $ref_username)->first();
        $temp_user = $user;
        $admin = User::where('id', 1)->first();

        $index = 0;

        $admin_bonus = $package->price * ($package->bonus_price / 100);
        
        $user_decrease_balance = $admin_bonus;
        $user->balance -= $user_decrease_balance;
        $user->save();
        self::generate_package_transactions($package, $user, $user, $user_decrease_balance, STATUS::PACKAGE_CONTRIBUTE_NETWORK_BONUS);
        self::generate_transactions($package, $user, $user, $user_decrease_balance, STATUS::PACKAGE_CONTRIBUTE_NETWORK_BONUS);

        for ($i = 0; $i < $depth; $i++) {
            if ($ref_user->id == 1) {
                break;
            }
            $is_user = self::check_user_purchased_package($ref_user);
            if ($is_user) {
                $bonus = self::calc_ref_user_bonus($package, $percentage[$index++]);
                $ref_user->balance += $bonus;
                $ref_user->save();
                $admin_bonus -= $bonus;
                self::generate_package_transactions($package, $ref_user, $user, $bonus, STATUS::PACKAGE_NETWORK_BONUS);
                self::generate_transactions($package, $ref_user, $user, $bonus, STATUS::PACKAGE_NETWORK_BONUS);

                // $user_weekly_income = WeeklyIncome::where('user_id', $ref_user->id)->first();
                // $user_weekly_income->weekly_income += $bonus;
                // $user_weekly_income->save();

                $ref_user_packages = PackageUser::where('status', STATUS::PACKAGE_PURCHASED)->where('user_id', $ref_user->id)->orderBy('created_at', 'asc')->get();
                foreach($ref_user_packages as $ref_user_package){
                    $ref_user_income_package = PackageIncomeUser::where('status', STATUS::PACKAGE_PURCHASED)->where('user_id', $ref_user_package->user_id)->where('package_id', $ref_user_package->package_id)->first();
                    $package_uncom_amount = $ref_user_income_package->max_income - $ref_user_income_package->current_total_income;
                    if($bonus >= $package_uncom_amount){
                        $ref_user_package->status = STATUS::PACKAGE_RELEASED;
                        $ref_user_package->save();
                        $ref_user_income_package->current_total_income = 0;
                        $ref_user_income_package->current_daily_income = 0;
                        $ref_user_income_package->status = STATUS::PACKAGE_RELEASED;
                        $ref_user_income_package->save();
                        $bonus -= $package_uncom_amount;
                    } else{
                        $ref_user_income_package->current_total_income += $bonus;
                        $ref_user_income_package->save();
                        $bonus = 0;
                    }
                    if($bonus == 0) break;
                }
            }
            $temp_user = $ref_user;
            $ref_user = User::where('username', $temp_user->ref_user)->first();
        }
        if ($admin_bonus != 0) {
            $admin->balance += $admin_bonus;
            self::generate_package_transactions($package, $admin, $user, $admin_bonus, STATUS::PACKAGE_NETWORK_BONUS);
            self::generate_transactions($package, $admin, $user, $admin_bonus, STATUS::PACKAGE_NETWORK_BONUS);
        }
    }

    private function release_package($package, $user){
        $package_user = PackageUser::where('package_id', $package->id)->where('user_id', $user->id)->first();
        $package_user->status = STATUS::PACKAGE_RELEASED;
        $package_user->current_total_income = 0;
        $package_user->current_daily_income = 0;
        $package_user->save();
    }

    public function calc_ref_user_bonus($package, $percentage)
    {
        return $package->price * ($package->bonus_price / 100) * $percentage / 100;
    }

    private function check_user_purchased_package($user)
    {
        $temp_user = PackageUser::where('status', STATUS::PACKAGE_PURCHASED)->where('user_id', $user->id)->first();
        if ($temp_user) return true;
        return false;
    }

    public function schedule_func()
    {
        $current_day = date('w');                                   // Get current day
        $today = Carbon::today();

        $package_users = PackageUser::where('status', Status::PACKAGE_PURCHASED)->get();
        $admin = User::where('id', 1)->first();
        foreach ($package_users as $package_user) {
            $package = Package::where('id', $package_user->package_id)->first();
            $user = User::where('id', $package_user->user_id)->first();
            $user_weekly_income = WeeklyIncome::where('user_id', $user->id)->first();
            $package_income_user = PackageIncomeUser::where('status', Status::PACKAGE_PURCHASED)->where('package_id', $package->id)->where('user_id', $user->id)->first();
            $duration = $package->duration;
            $duration_unit = $package->repeat_unit;
            $deposits = Deposit::where('user_id', $user->id)->where('status', 1)->whereDate('created_at', $today)->get();
            $today_deposit_amount = 0;
            foreach ($deposits as $deposit) {
                $today_deposit_amount += $deposit->amount;
            }
            if ($duration_unit == "day") {
                $duration = $duration * 24;
            }
            if ($package_user->updated_at->lt(Carbon::now()->subHours($duration))) {
            // if ($package_user->updated_at->lt(Carbon::now()->subSeconds(1))) {     // For test
                if ($current_day == 6) {
                    
                    if ($user_weekly_income && $today_deposit_amount >= $user_weekly_income->weekly_fee) {
                        $user->weekly_paid = Status::USER_WEEKLY_PAID;
                        $release_amount = $package_income_user->current_daily_income;
                        if ($package_income_user->current_total_income + $release_amount < $package_income_user->max_income) {
                            $user->balance += $release_amount;
                            $user->save();
                            $user_weekly_income->weekly_income = 0;
                            $user_weekly_income->weekly_fee = 0;
                            $user_weekly_income->save();
                            self::generate_package_transactions($package, $user, $admin, $release_amount, STATUS::PACKAGE_GET_DAILY_INCOME);
                            self::generate_transactions($package, $user, $admin, $release_amount, STATUS::PACKAGE_GET_DAILY_INCOME);
                            self::generate_bonus_distributions($package, $user);
                            // $package_user->current_total_income = $package_user->current_total_income + $release_amount;
                            // $package_user->current_daily_income = $release_amount + $package_user->rising_income;
                            // $package_user->save();
                            $package_income_user->current_total_income = $package_income_user->current_total_income + $release_amount;
                            $package_income_user->current_daily_income = $release_amount + $package_income_user->rising_income;
                            $package_income_user->save();
                        } else {
                            $release_amount = $package_income_user->max_income - $package_income_user->current_total_income;
                            $user->balance += $release_amount;
                            $user->save();
                            $user_weekly_income->weekly_income = 0;
                            $user_weekly_income->weekly_fee = 0;
                            $user_weekly_income->save();
                            self::generate_package_transactions($package, $user, $admin, $release_amount, STATUS::PACKAGE_GET_DAILY_INCOME);
                            self::generate_transactions($package, $user, $admin, $release_amount, STATUS::PACKAGE_GET_DAILY_INCOME);
                            self::generate_bonus_distributions($package, $user);
                            $package_income_user->current_total_income = 0;
                            $package_income_user->current_daily_income = 0;
                            $package_income_user->status = STATUS::PACKAGE_RELEASED;
                            $package_income_user->save();
                            $package_user->status = STATUS::PACKAGE_RELEASED;
                            $package_user->save();
                        }
                    } else {
                        $user->weekly_paid = Status::USER_WEEKLY_NOT_PAID;
                    }
                    $user->save();
                }else{
                    if($user->weekly_paid != Status::USER_WEEKLY_NOT_PAID){
                        $release_amount = $package_income_user->current_daily_income;
                        if ($package_income_user->current_total_income + $release_amount < $package_income_user->max_income) {
                            $user->balance += $release_amount;
                            $user->save();
                            $user_weekly_income->weekly_income += $release_amount;
                            $user_weekly_income->weekly_fee += $release_amount * $package_income_user->weekly_fee / 100;
                            $user_weekly_income->save();
                            $package_income_user->current_total_income = $package_income_user->current_total_income + $release_amount;
                            $package_income_user->current_daily_income = $release_amount + $package_income_user->rising_income;
                            $package_income_user->save();
                            self::generate_package_transactions($package, $user, $admin, $release_amount, STATUS::PACKAGE_GET_DAILY_INCOME);
                            self::generate_transactions($package, $user, $admin, $release_amount, STATUS::PACKAGE_GET_DAILY_INCOME);
                            self::generate_bonus_distributions($package, $user);
                            
                        } else {
                            $release_amount = $package_income_user->max_income - $package_income_user->current_total_income;
                            $user->balance += $release_amount;
                            $user->save();
                            $user_weekly_income->weekly_income += $release_amount;
                            $user_weekly_income->weekly_fee += $release_amount * $package_income_user->weekly_fee / 100;
                            $user_weekly_income->save();
                            self::generate_package_transactions($package, $user, $admin, $release_amount, STATUS::PACKAGE_GET_DAILY_INCOME);
                            self::generate_transactions($package, $user, $admin, $release_amount, STATUS::PACKAGE_GET_DAILY_INCOME);
                            self::generate_bonus_distributions($package, $user);
                            $package_income_user->current_total_income = 0;
                            $package_income_user->current_daily_income = 0;
                            $package_income_user->status = STATUS::PACKAGE_RELEASED;
                            $package_income_user->save();
                            $package_user->status = STATUS::PACKAGE_RELEASED;
                            $package_user->save();
                        }
                    }
                }
                $package_user->save();
            }
        }

        $users = User::where('id', '!=', 1)->orderBy('created_at', 'desc')->get();
        foreach($users as $user){
            $deposits = Deposit::where('user_id', $user->id)->where('status', 1)->whereDate('created_at', $today)->get();
            $today_deposit_amount = 0;
            foreach ($deposits as $deposit) {
                $today_deposit_amount += $deposit->amount;
            }
            $user_weekly_income = WeeklyIncome::where('user_id', $user->id)->first();
            if ($current_day == 6) {
                if($today_deposit_amount >= $user_weekly_income->weekly_fee){
                    $user->weekly_paid = STATUS::USER_WEEKLY_PAID;
                    $user->save();
                    $user_weekly_income->weekly_fee = 0;
                    $user_weekly_income->save();
                }else{
                    $user->weekly_paid = STATUS::USER_WEEKLY_NOT_PAID;
                    $user->save();
                }
            }
        }
    }
}
