@auth
    <div class="modal fade" id="purchase_package_modal">
        <div class="modal-dialog" role="dialog" style="max-width : 700px">
            <div class="modal-content">
                <form action="{{ route('user.package.purchase') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title m-0">@lang('Please Start Your Invest!')</h4>
                        <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="accordion-body">
                            <input id="package_id_area" name="package_id" type="hidden"/>
                            <input name="balance" type="hidden" value="{{auth()->user()->balance}}" />
                            <ul class="caption-list">
                                <li>
                                    <span class="caption">@lang('Name')</span>
                                    <span class="value" id="package_name"></span>
                                </li>
                                <li>
                                    <span class="caption">@lang('Price')</span>
                                    <span class="value" id="package_price"></span>
                                </li>
                                <li>
                                    <span class="caption">@lang('Max Returning Payment')</span>
                                    <span class="value" id="package_max_income"></span>
                                </li>
                                <li>
                                    <span class="caption">@lang('Daily Income')</span>
                                    <span class="value" id="package_start_daily_income"></span>
                                </li>
                                <li>
                                    <span class="caption">@lang('Contributing Network Bonus Amount')</span>
                                    <span class="value" id="package_network_bonus_amount"></span>
                                </li>
                                <li>
                                    <span class="caption">@lang('Brokerage fee on profit(weekly)')</span>
                                    <span class="value" id="package_weekly_fee"></span>
                                </li>
                                <li>
                                    <span class="caption">@lang('Balance')</span>
                                    <span class="value" id="user_balance">{{auth()->user()->balance}}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--base w-100" id="invest_start_btn">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="modal fade" id="loginModal" role="dialog" aria-hidden="true" tabindex="-1">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title m-0">@lang('Confirmation Alert!')</h5>
                        <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <span class="text-center">@lang('Please login to subscribe plans.')</span>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-dark h-auto w-auto" data-bs-dismiss="modal"
                            type="button">@lang('Close')</button>
                        <a class="btn btn--base w-auto" href="{{ route('user.login') }}">@lang('Login')</a>
                    </div>
                </div>
            </div>
        </div>

    @endauth

    @push('script')
        <script>
            'use strict';
            var routeUrl;
            var task_id;

            $('.package_purchase_btn').on('click', function() {
                var modal = $('#purchase_package_modal');
                modal.find('#package_name').html($(this).data('name'));
                modal.find('input[name="package_id"]').val($(this).data('id'));
                modal.find('#package_price').html($(this).data('price') + ' {{ __($general->cur_text) }}');
                modal.find('#package_max_income').html($(this).data('max_income') + ' {{ __($general->cur_text) }}');
                modal.find('#package_start_daily_income').html($(this).data('start_income') + ' {{ __($general->cur_text) }}');
                modal.find('#package_rising_daily_income').html($(this).data('rising_income') + ' {{ __($general->cur_text) }}');
                modal.find('#package_weekly_fee').html($(this).data('weekly_fee') + ' (%)');
                modal.find('#package_network_bonus_amount').html($(this).data('bonus_price') + '{{ __($general->cur_text)}}')
                modal.modal('show');
            });
        </script>
    @endpush
