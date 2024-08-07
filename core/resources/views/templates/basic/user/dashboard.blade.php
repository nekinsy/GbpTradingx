@extends($activeTemplate . 'layouts.master')
@section('content')
    @php
        $kycInfo = getContent('kyc_info.content', true);
        $notice = getContent('notice.content', true);
    @endphp
    <div class="dashboard-inner">
        @if ($user->kv == 0)
            <div class="alert border--info border" role="alert">
                <div class="alert__icon d-flex align-items-center text--info">
                    <i class="fas fa-file-signature"></i>
                </div>
                <p class="alert__message">
                    <span class="fw-bold">@lang('KYC Verification Required')</span>
                    <br>
                    <small><i>{{ __($kycInfo->data_values->verification_content) }} <a class="link-color" href="{{ route('user.kyc.form') }}">@lang('Click Here to Verify')</a></i></small>
                </p>
            </div>

            <script>
                var alertList = document.querySelectorAll('.alert');
                alertList.forEach(function(alert) {
                    new bootstrap.Alert(alert)
                })
            </script>
        @elseif($user->kv == 2)
            <div class="alert border--warning border" role="alert">
                <div class="alert__icon d-flex align-items-center text--warning">
                    <i class="fas fa-user-check"></i>
                </div>
                <p class="alert__message">
                    <span class="fw-bold">@lang('KYC Verification Pending')</span>
                    <br>
                    <small><i>{{ __($kycInfo->data_values->pending_content) }} <a class="link-color" href="{{ route('user.kyc.data') }}">@lang('See KYC Data')</a></i></small>
                </p>
            </div>
        @endif

        @if (@$notice->data_values->notice_content != null && !$user->plan_id)
            <div class="card custom--card">
                <div class="card-header">
                    <h5>@lang('Notice')</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        {{ __($notice->data_values->notice_content) }}
                    </p>
                </div>
            </div>
        @endif
        
        @if(auth()->user()->weekly_paid == 1)
            <div class="alert border--danger border" role="alert">
                <div class="alert__icon d-flex align-items-center text--danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <p class="alert__message">
                    <span class="fw-bold">
                        @lang('You have not made a deposit for the weekly brokerage fee. Please deposit on Saturday!')
                    </span>
                    <br>
                    <span class="fw-bold block">
                        @lang('Balance Day! Withdrawals available from Sunday to Friday.')
                    </span>
                    <br>
                    <small><i> <a class="link-color" href="{{ route('user.deposit.index', ['brokerage_fee' => $weekly_info->weekly_fee]) }}">@lang('Click Here to Deposit')</a></i></small>
                </p>
            </div>
        @else
            <div>
                
            </div>
        @endif

        <div class="row g-3 mt-3 mb-4">

            <div class="col-lg-6">
                <div class="dashboard-widget">
                    <div class="d-flex justify-content-between">
                        <h5 class="text-secondary">@lang('Total Deposit')</h5>
                    </div>
                    <h3 class="text--secondary my-4">{{ showAmount($totalDeposit) }} {{ __($general->cur_text) }}</h3>
                    <div class="widget-lists">
                        <div class="row">
                            <div class="col-4">
                                <p class="fw-bold">@lang('Submitted')</p>
                                <span>{{ $general->cur_sym }}{{ showAmount($submittedDeposit) }}</span>
                            </div>
                            <div class="col-4">
                                <p class="fw-bold">@lang('Pending')</p>
                                <span>{{ $general->cur_sym }}{{ showAmount($pendingDeposit) }}</span>
                            </div>
                            <div class="col-4">
                                <p class="fw-bold">@lang('Rejected')</p>
                                <span>{{ $general->cur_sym }}{{ showAmount($rejectedDeposit) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="dashboard-widget">
                    <div class="d-flex justify-content-between">
                        <h5 class="text-secondary">@lang('Total Widthdraw')</h5>
                    </div>
                    <h3 class="text--secondary my-4">{{ showAmount($totalWithdraw) }} {{ __($general->cur_text) }}</h3>
                    <div class="widget-lists">
                        <div class="row">
                            <div class="col-4">
                                <p class="fw-bold">@lang('Submitted')</p>
                                <span>{{ $general->cur_sym }}{{ showAmount($submittedWithdraw) }}</span>
                            </div>
                            <div class="col-4">
                                <p class="fw-bold">@lang('Pending')</p>
                                <span>{{ $general->cur_sym }}{{ showAmount($pendingWithdraw) }}</span>
                            </div>
                            <div class="col-4">
                                <p class="fw-bold">@lang('Rejected')</p>
                                <span>{{ $general->cur_sym }}{{ showAmount($rejectWithdraw) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="dashboard-widget">
                    <div class="d-flex justify-content-between">
                        <h5 class="text-secondary">@lang('Total Referral Commission')</h5>
                    </div>
                    <h3 class="text--secondary my-4">{{ showAmount($user->total_ref_com) }} {{ __($general->cur_text) }}
                    </h3>
                    
                </div>
            </div>

            <div class="col-lg-3">
                <div class="dashboard-widget">
                    <div class="d-flex justify-content-between">
                        <h5 class="text-secondary">@lang('Total Invest')</h5>
                    </div>
                    <h3 class="text--secondary my-4">{{ showAmount($user->total_invest) }} {{ __($general->cur_text) }}
                    </h3>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="dashboard-widget">
                    <div class="d-flex justify-content-between">
                        <h5 class="text-secondary">@lang('Weekly Income')</h5>
                    </div>
                    <h3 class="text--secondary my-4">{{ $weekly_info ? showAmount($weekly_info->weekly_income) : 0 }} {{ __($general->cur_text) }}
                    </h3>
                </div>
            </div>

            <div class="col-lg-3">
                <a href="{{ route('user.deposit.index', ['brokerage_fee' => $weekly_info->weekly_fee]) }}"  
                   class="text-decoration-none text-dark w-100">  
                    <div class="dashboard-widget {{ auth()->user()->weekly_paid == 1 ? 'bg-danger' : '' }}">
                        <div class="d-flex justify-content-between">
                            <h5 class="{{ auth()->user()->weekly_paid == 1 ? 'text-white' : 'text-secondary' }}">@lang('Brokerage Fee (Weekly)')</h5>
                        </div>
                        <h3 class="{{ \Carbon\Carbon::now()->isTuesday() ? 'text-white' : 'text-secondary' }} my-4">{{ $weekly_info ? showAmount($weekly_info->weekly_fee) : 0 }} {{ __($general->cur_text) }}
                        </h3>
                    </div>
                </a>
            </div>

        </div>

        
    </div>
@endsection
