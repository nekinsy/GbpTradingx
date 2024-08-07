@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="dashboard-inner">
        <div class="mb-4">
            <h3 class="mb-2">{{ __($pageTitle) }}</h3>
        </div>
        @if (!count($packages))
            <div class="row">
                <h4>@lang('No Investment Plan Launched')</h4>
            </div>
        @else
            <div class="row justify-content-center">
                <div class="d-flex justify-content-between mb-3 flex-wrap gap-1 text-end">
                    <h3 class="dashboard-title">@lang('Start your Invest') <i class="fas fa-question-circle text-muted text--small"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('The invest will decrease your balance. Please wait the duration suggested, you will give profit every duration!')"></i></h3>
                    <a class="btn btn--base btn--smd" href="{{ route('user.package.history') }}">@lang('My Plans')</a>
                </div>
                <div class="col-md-12">
                    <div class="accordion table--acordion" id="transactionAccordion">
                        @forelse($packages as $package)
                            <div class="accordion-item transaction-item">
                                <h2 class="accordion-header" id="h-{{ $loop->iteration }}">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#c-{{ $loop->iteration }}">
                                        <div class="col-lg-4 col-sm-7 col-5 order-1 icon-wrapper">
                                            <div class="left">
                                                <div class="icon tr-icon icon-success">
                                                    <i class="fas fa-tasks"></i>
                                                </div>
                                                <div class="content">
                                                    <h6 class="trans-title">{{ $package->name }}</h6>
                                                    <span
                                                        class="text-muted font-size--14px mt-2">{{ showDateTime($package->updated_at, 'M d Y @g:i:a') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="col-lg-4 col-sm-5 col-7 order-sm-2 order-3 content-wrapper mt-sm-0 mt-3">
                                            <p class="text-muted font-size--14px">@lang('Total Income') :
                                                <b>{{ number_format($package->total_income, $package->total_income - floor($package->total_income) == 0 ? 0 : 1, '.', '') }}
                                                    {{ __($general->cur_text) }}</b></p>
                                            <p class="text-muted font-size--14px">@lang('Today Income') :
                                                <b>{{ number_format($package->today_income, $package->today_income - floor($package->today_income) == 0 ? 0 : 1, '.', '') }}
                                                    {{ __($general->cur_text) }}</b></p>
                                        </div>
                                        <div class="col-lg-4 col-sm-12 col-12 order-sm-3 order-2 text-end amount-wrapper">
                                            <p>
                                                <b>@lang('Required balance'): {{ number_format($package->price, $package->price - floor($package->price) == 0 ? 0 : 1, '.', '') }}
                                                    {{ __($general->cur_text) }}</b><br>
                                                <b class="fw-bold">@lang('Maxiumn Returning Value'): {{ number_format($package->max_income, $package->max_income - floor($package->max_income) == 0 ? 0 : 1, '.', '') }}
                                                    {{ __($general->cur_text) }}</b>
                                            </p>

                                        </div>
                                    </button>
                                </h2>
                                <div id="c-{{ $loop->iteration }}" class="accordion-collapse collapse"
                                    aria-labelledby="h-1" data-bs-parent="#transactionAccordion">
                                    <div class="accordion-body">
                                        <ul class="caption-list">
                                            <li>
                                                <span class="caption">@lang('Required Balance')</span>
                                                <span class="value">{{ showAmount($package->price) }}
                                                    {{ __($general->cur_text) }}</span>
                                            </li>
                                            <li>
                                                <span class="caption">@lang('Maxiumn Returning Value')</span>
                                                <span class="value">{{ showAmount($package->max_income) }}
                                                    {{ __($general->cur_text) }}</span>
                                            </li>
                                            <li>
                                                <span class="caption">@lang('Daily Income')</span>
                                                <span
                                                    class="value">{{ showAmount(($package->start_income * $package->price) / 100) }}
                                                    {{ __($general->cur_text) }}</span>
                                            </li>
                                            <li>
                                                <span class="caption">@lang('Contributing Network Bonus Amount')</span>
                                                <span
                                                    class="value">{{ showAmount(($package->bonus_price * $package->price) / 100) }}
                                                    {{ __($general->cur_text) }}</span>
                                            </li>
                                            <li>
                                                <span class="caption">@lang('Brokerage fee on profit(weekly)')</span>
                                                <span class="value">{{ $package->weekly_fee }} (%)</span>
                                            </li>
                                            <li>
                                                <span class="caption">@lang('Description')</span>
                                                <span class="value">{{ $package->description }}</span>
                                            </li>
                                            <li>
                                                @if (auth()->user()->weekly_paid == Status::USER_WEEKLY_NOT_PAID)
                                                    <button class="btn btn--danger w-100 disabled mt-2" type="button">
                                                        @lang('Weekly Deposit is not enough')
                                                    </button>
                                                @elseif ($package->active)
                                                    <button class="btn btn--success w-100 disabled mt-2" type="button">
                                                        @lang('Current Running')
                                                    </button>
                                                @else
                                                    <button class="btn btn--base w-100 mt-1 package_purchase_btn"
                                                        data-id="{{ $package->id }}" data-name="{{ $package->name }}"
                                                        data-price="{{ $package->price }}"
                                                        data-max_income="{{ showAmount($package->max_income) }}"
                                                        data-bonus_price="{{ ($package->bonus_price * $package->price) / 100 }}"
                                                        data-start_income="{{ ($package->start_income * $package->price) / 100 }}"
                                                        data-rising_income="{{ ($package->rising_income * $package->price) / 100 }}"
                                                        data-invite_bonus="{{ $package->invite_bonus }}"
                                                        data-weekly_fee="{{ $package->weekly_fee }}"
                                                        data-invite_count="{{ $package->invite_count }}" type="button">
                                                        @lang('Start')
                                                    </button>
                                                @endif
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div><!-- task-item end -->
                        @empty
                            <div class="accordion-body text-center">
                                <h4 class="text--muted"><i class="far fa-frown"></i> Empty</h4>
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>
        @endif
        @if ($packages->hasPages())
            <div class="card-footer py-4">
                @php echo paginateLinks($packages) @endphp
            </div>
        @endif
    </div>
    @include($activeTemplate . 'partials.package_modals')
@endsection

@push('script')
    <script>
        'use strict';
    </script>
@endpush
