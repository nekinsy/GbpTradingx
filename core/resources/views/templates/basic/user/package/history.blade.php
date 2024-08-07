@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="dashboard-inner">
        <div class="mb-4">
            <div class="d-flex justify-content-between">
                <h3 class="mb-2">@lang('Invest Transaction History')</h3>
                <span>
                    <a href="{{ route('user.package.index') }}" class="btn btn--base btn--smd">@lang('Invest Now')</a>
                </span>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">

                <div class="accordion table--acordion" id="transactionAccordion">
                    @forelse($package_transactions as $package_trans)
                        <div class="accordion-item transaction-item">
                            <h2 class="accordion-header" id="h-{{ $loop->iteration }}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#c-{{ $loop->iteration }}" aria-expanded="false" aria-controls="c-1">
                                    <div class="col-lg-6 col-sm-6 col-6 order-1 icon-wrapper">
                                        <div class="left">
                                            @if ($package_trans->mode == Status::PACKAGE_PURCHASED)
                                                <div class="icon icon-danger">
                                                    <i class="las la-long-arrow-alt-right"></i>
                                                </div>
                                            @elseif ($package_trans->mode == Status::PACKAGE_RELEASED)
                                                <div class="icon icon-success">
                                                    <i class="las la-check"></i>
                                                </div>
                                            @elseif($package_trans->mode == Status::PACKAGE_NETWORK_BONUS)
                                                <div class="icon icon-primary">
                                                    <i class="las la-network-wired"></i>
                                                </div>
                                            @elseif($package_trans->mode == Status::PACKAGE_INVITE_BONUS)
                                                <div class="icon icon-success">
                                                    <i class="las la-users"></i>
                                                </div>
                                            @elseif($package_trans->mode == Status::PACKAGE_GET_DAILY_INCOME)
                                                <div class="icon icon-success">
                                                    <i class="las la-battery-three-quarters"></i>
                                                </div>
                                            @elseif($package_trans->mode == Status::PACKAGE_CONTRIBUTE_NETWORK_BONUS)
                                                <div class="icon icon-danger">
                                                    <i class="las la-network-wired"></i>
                                                </div>
                                            @endif
                                            <div class="content">
                                                <h6 class="trans-title">{{ __($package_trans->remark) }}</h6>
                                                <span class="text-muted font-size--14px mt-2">
                                                    {{ showDateTime($package_trans->created_at, 'M d Y @g:i:a') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-3 col-3 order-sm-2 order-2 content-wrapper mt-sm-0 mt-3">
                                        <p class="text-muted font-size--14px"><b>{{ $package_trans->package_name }}</b></p>
                                    </div>
                                    <div class="col-lg-3 col-sm-3 col-3 order-sm-3 order-3 text-end amount-wrapper">
                                        <p><b>{{ showAmount($package_trans->amount) }} {{ __($general->cur_text) }}</b></p>
                                    </div>
                                </button>
                            </h2>
                            <div id="c-{{ $loop->iteration }}" class="accordion-collapse collapse" aria-labelledby="h-1"
                                data-bs-parent="#transactionAccordion">
                                <div class="accordion-body">
                                    <ul class="caption-list">
                                        <li>
                                            <span class="caption">@lang('Amount')</span>
                                            <span class="value">{{ showAmount($package_trans->amount) }}
                                                {{ __($general->cur_text) }}</span>
                                        </li>
                                        <li>
                                            <span class="caption">@lang('After Transaction')</span>
                                            <span class="value">{{ showAmount($package_trans->after_balance) }}
                                                {{ __($general->cur_text) }}</span>
                                        </li>
                                        <li>
                                            <span class="caption">@lang('Before Transaction')</span>
                                            <span
                                                class="value">{{ showAmount($package_trans->before_balance) }} {{ __($general->cur_text) }}
                                                
                                            </span>
                                        </li>
                                        @if($package_trans->mode == Status::PACKAGE_PURCHASED)
                                            <li>
                                                <span class="caption">@lang('Duration')</span>
                                                <span class="value">{{ intval($package_trans->package_duration) }} {{ $package_trans->package_duration_unit }}</span>
                                            </li>
                                        @endif
                                        <li>
                                            <span class="caption">@lang('Status')</span>
                                            <span class="value">
                                                @php echo $package_trans->statusBadge @endphp <button type="button" class="btn p-0"><i
                                                        class="las la-info-circle detailBtn"
                                                        data-user_data="{{ json_encode($package_trans->details) }}"></i></button>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div><!-- transaction-item end -->
                    @empty
                        <div class="accordion-body bg-white text-center">
                            <h4 class="text--muted"><i class="far fa-frown"></i> {{ __($emptyMessage) }}</h4>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        @if ($package_transactions->hasPages())
            <div class="card-footer py-4">
                @php echo paginateLinks($package_transactions) @endphp
            </div>
        @endif
    </div>



    {{-- APPROVE MODAL --}}
    <div id="detailModal" class="modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Details')</h5>
                    <span type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <ul class="list-group userData">

                    </ul>
                    <div class="feedback"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark btn--sm" data-bs-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            var history = @json($package_transactions);
            console.log(history);

        })(jQuery);
    </script>
@endpush
